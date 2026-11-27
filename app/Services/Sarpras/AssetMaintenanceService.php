<?php

namespace App\Services\Sarpras;

use App\Events\NewNotificationEvent;
use App\Http\Middleware\RoleMiddleware;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\AssetMaintenanceTicket;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AssetMaintenanceService
{
    public const PRIVATE_DISK = 'local';

    public const PRIVATE_DIRECTORY = 'sarpras/damage-reports';

    public function canViewSarpras(User $user): bool
    {
        return $user->hasPermission('sarpras.kelola')
            || $user->hasPermission('pimpinan.dasbor')
            || RoleMiddleware::allowsUser($user, ['waka_sarpras', 'kepala_sekolah', 'kepsek', 'staf_tu', 'admin', 'superadmin']);
    }

    public function canManageSarpras(User $user): bool
    {
        return $user->hasPermission('sarpras.kelola')
            || RoleMiddleware::allowsUser($user, ['waka_sarpras', 'admin', 'superadmin']);
    }

    public function canViewAttachment(User $user, AssetMaintenanceTicket $ticket): bool
    {
        if ((int) $ticket->reported_by === (int) $user->id) {
            return true;
        }

        return $this->canViewSarpras($user);
    }

    /**
     * Any authenticated school member scans an asset QR code and reports damage (`ALUR_KERJA.md` I3 step 1).
     * Stores optional photo evidence strictly on private `local` disk.
     *
     * @param  array<string, mixed>  $data
     */
    public function submitDamageReport(
        Asset $asset,
        User $reporter,
        array $data,
        ?UploadedFile $photo = null,
    ): AssetMaintenanceTicket {
        $priority = (string) ($data['priority'] ?? 'medium');
        if (! in_array($priority, AssetMaintenanceTicket::PRIORITIES, true)) {
            throw ValidationException::withMessages([
                'priority' => __('Prioritas laporan harus berupa low, medium, high, atau critical.'),
            ]);
        }

        return DB::transaction(function () use ($asset, $reporter, $data, $priority, $photo) {
            $photoPath = null;
            if ($photo !== null) {
                $photoPath = $photo->store(self::PRIVATE_DIRECTORY, self::PRIVATE_DISK);
            }

            $ticket = AssetMaintenanceTicket::create([
                'asset_id' => $asset->id,
                'reported_by' => $reporter->id,
                'issue_description' => trim((string) $data['issue_description']),
                'priority' => $priority,
                'status' => AssetMaintenanceTicket::STATUS_PENDING,
                'photo_evidence' => $photoPath,
                'photo_disk' => self::PRIVATE_DISK,
            ]);

            if ($priority === 'critical' && $asset->condition === 'good') {
                $asset->update(['condition' => 'damaged']);
            }

            $this->notifySarprasStaff($ticket->loadMissing(['asset.room', 'reporter']));

            return $ticket;
        });
    }

    /**
     * Sarpras transitions a damage ticket through `pending -> investigating -> repairing -> resolved | scrapped`
     * (`ALUR_KERJA.md` I3 steps 2-3), syncing parent `Asset` status/condition and recording corrective maintenance cost/vendor.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function transitionTicketStatus(
        AssetMaintenanceTicket $ticket,
        User $actor,
        string $newStatus,
        array $attributes = [],
    ): AssetMaintenanceTicket {
        if (! $this->canManageSarpras($actor)) {
            abort(403, __('Hanya Waka Sarpras atau Admin Sarpras yang berwenang memproses tiket kerusakan aset.'));
        }

        if (! in_array($newStatus, AssetMaintenanceTicket::STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => __('Status tiket tidak valid.'),
            ]);
        }

        return DB::transaction(function () use ($ticket, $actor, $newStatus, $attributes) {
            $locked = AssetMaintenanceTicket::query()->lockForUpdate()->findOrFail($ticket->id);

            if ($locked->status === AssetMaintenanceTicket::STATUS_SCRAPPED && $newStatus !== AssetMaintenanceTicket::STATUS_SCRAPPED) {
                throw ValidationException::withMessages([
                    'status' => __('Aset yang sudah dihapusbukukan (scrapped) tidak dapat dikembalikan ke antrean perbaikan.'),
                ]);
            }

            $asset = Asset::query()->lockForUpdate()->findOrFail($locked->asset_id);

            $updates = [
                'status' => $newStatus,
                'handled_by' => $locked->handled_by ?? $actor->id,
            ];

            if (array_key_exists('resolution_notes', $attributes)) {
                $updates['resolution_notes'] = $attributes['resolution_notes'];
            }
            if (array_key_exists('cost', $attributes) && $attributes['cost'] !== null && $attributes['cost'] !== '') {
                $updates['cost'] = max(0, (float) $attributes['cost']);
            }
            if (array_key_exists('vendor', $attributes)) {
                $updates['vendor'] = $attributes['vendor'];
            }

            if ($newStatus === AssetMaintenanceTicket::STATUS_INVESTIGATING) {
                $updates['investigated_at'] = $locked->investigated_at ?? now();
                $asset->update([
                    'status' => 'repairing',
                    'condition' => $asset->condition === 'good' ? 'damaged' : $asset->condition,
                ]);
            } elseif ($newStatus === AssetMaintenanceTicket::STATUS_REPAIRING) {
                $updates['investigated_at'] = $locked->investigated_at ?? now();
                $updates['repair_started_at'] = $locked->repair_started_at ?? now();
                $asset->update([
                    'status' => 'repairing',
                    'condition' => $asset->condition === 'good' ? 'damaged' : $asset->condition,
                ]);
            } elseif ($newStatus === AssetMaintenanceTicket::STATUS_RESOLVED) {
                $updates['resolved_by'] = $actor->id;
                $updates['resolved_at'] = now();
                $asset->update([
                    'condition' => 'good',
                    'status' => 'available',
                ]);

                $resolvedCost = isset($updates['cost'])
                    ? (float) $updates['cost']
                    : (float) ($locked->cost ?? 0);
                $resolvedVendor = $updates['vendor'] ?? $locked->vendor;
                $resolvedDesc = trim((string) ($updates['resolution_notes'] ?? $locked->resolution_notes ?? $locked->issue_description));

                $maintenancePayload = [
                    'asset_id' => $asset->id,
                    'maintenance_date' => now()->toDateString(),
                    'type' => 'korektif',
                    'description' => 'Perbaikan Tiket #'.$locked->id.': '.$resolvedDesc,
                    'cost' => $resolvedCost,
                    'vendor' => $resolvedVendor,
                    'status' => 'completed',
                    'handled_by' => $actor->id,
                ];

                if ($locked->asset_maintenance_id) {
                    AssetMaintenance::whereKey($locked->asset_maintenance_id)->update($maintenancePayload);
                } else {
                    $maintenance = AssetMaintenance::create($maintenancePayload);
                    $updates['asset_maintenance_id'] = $maintenance->id;
                }
            } elseif ($newStatus === AssetMaintenanceTicket::STATUS_SCRAPPED) {
                $updates['resolved_by'] = $actor->id;
                $updates['resolved_at'] = now();
                $asset->update([
                    'condition' => 'broken',
                    'status' => 'lost',
                ]);
            }

            $locked->update($updates);

            return $locked->refresh()->load(['asset.room', 'reporter', 'handler', 'resolver']);
        });
    }

    /**
     * Schedule or record preventive/corrective/upgrade maintenance directly by Sarpras.
     *
     * @param  array<string, mixed>  $validated
     */
    public function createScheduledMaintenance(User $actor, array $validated): AssetMaintenance
    {
        if (! $this->canManageSarpras($actor)) {
            abort(403, __('Hanya Waka Sarpras atau Admin Sarpras yang berwenang menjadwalkan perawatan aset.'));
        }

        return AssetMaintenance::create(array_merge($validated, [
            'handled_by' => $actor->id,
        ]));
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updateScheduledMaintenance(AssetMaintenance $maintenance, User $actor, array $validated): AssetMaintenance
    {
        if (! $this->canManageSarpras($actor)) {
            abort(403, __('Hanya Waka Sarpras atau Admin Sarpras yang berwenang memperbarui perawatan aset.'));
        }

        $maintenance->update($validated);

        return $maintenance->refresh();
    }

    /**
     * Resolve ticket by UUID or numeric ID for backward-compatible route binding.
     */
    public function findTicketByKey(string|int $key): AssetMaintenanceTicket
    {
        if (is_numeric($key)) {
            return AssetMaintenanceTicket::findOrFail((int) $key);
        }

        return AssetMaintenanceTicket::where('uuid', (string) $key)->firstOrFail();
    }

    /**
     * Stream private damage photo attachment after Anti-IDOR verification.
     */
    public function streamTicketAttachment(AssetMaintenanceTicket $ticket, User $viewer)
    {
        if (! $this->canViewAttachment($viewer, $ticket)) {
            abort(403, __('Anda tidak berwenang melihat foto bukti laporan kerusakan ini.'));
        }

        $path = $ticket->photo_evidence;
        if (! $path) {
            abort(404);
        }

        $preferredDisk = $ticket->photo_disk ?: self::PRIVATE_DISK;
        foreach (array_unique([$preferredDisk, self::PRIVATE_DISK, 'public']) as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->response($path);
            }
        }

        abort(404);
    }

    private function notifySarprasStaff(AssetMaintenanceTicket $ticket): void
    {
        $recipients = User::query()
            ->where('role', 'waka_sarpras')
            ->orWhereHas('positionAssignments', function ($q) {
                $q->activeOn()->whereHas('position', fn ($pq) => $pq->where('code', 'waka_sarpras'));
            })
            ->get();

        $assetName = $ticket->asset?->name ?? 'Aset Sekolah';
        $roomName = $ticket->asset?->room?->name ? ' ('.$ticket->asset->room->name.')' : '';

        foreach ($recipients as $recipient) {
            event(new NewNotificationEvent($recipient, [
                'id' => $ticket->id,
                'type' => 'asset_damage_reported',
                'title' => "Laporan Kerusakan Aset [{$ticket->priority}]: {$assetName}",
                'description' => ($ticket->reporter?->name ?? 'Warga Sekolah')." melaporkan kerusakan {$assetName}{$roomName}: {$ticket->issue_description}",
                'created_at' => now()->toDateTimeString(),
            ]));
        }
    }
}
