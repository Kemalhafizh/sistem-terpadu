<?php

namespace App\Services;

use App\Events\NewNotificationEvent;
use App\Models\IncomingLetter;
use App\Models\LetterDisposition;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IncomingLetterService
{
    public function generateAgendaNumber(?CarbonInterface $receivedDate = null): string
    {
        $year = ($receivedDate ?? Carbon::today())->format('Y');

        $count = IncomingLetter::withTrashed()
            ->whereYear('received_date', $year)
            ->count();

        $sequence = $count + 1;
        do {
            $seq = str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
            $candidate = "{$seq}/SM/{$year}";
            $sequence++;
        } while (IncomingLetter::withTrashed()->where('agenda_number', $candidate)->exists());

        return $candidate;
    }

    public function create(array $data, User $creator, ?UploadedFile $attachment = null): IncomingLetter
    {
        $attachmentPath = null;
        if ($attachment !== null) {
            $attachmentPath = $attachment->store('incoming-letters', 'local');
        }

        $receivedDate = Carbon::parse($data['received_date']);

        return DB::transaction(function () use ($data, $creator, $attachmentPath, $receivedDate) {
            $agendaNumber = $this->generateAgendaNumber($receivedDate);

            return IncomingLetter::create([
                'agenda_number' => $agendaNumber,
                'reference_number' => $data['reference_number'],
                'sender' => $data['sender'],
                'subject' => $data['subject'],
                'letter_date' => $data['letter_date'],
                'received_date' => $receivedDate->toDateString(),
                'category' => $data['category'] ?? null,
                'summary' => $data['summary'] ?? null,
                'attachment_path' => $attachmentPath,
                'status' => IncomingLetter::STATUS_RECEIVED,
                'created_by' => $creator->id,
            ]);
        });
    }

    public function createDisposition(IncomingLetter $letter, User $dispatcher, array $data): LetterDisposition
    {
        return DB::transaction(function () use ($letter, $dispatcher, $data) {
            $disposition = LetterDisposition::create([
                'incoming_letter_id' => $letter->id,
                'from_user_id' => $dispatcher->id,
                'to_user_id' => $data['to_user_id'],
                'instruction' => $data['instruction'],
                'priority' => $data['priority'] ?? 'normal',
                'due_at' => $data['due_at'] ?? null,
                'status' => LetterDisposition::STATUS_ASSIGNED,
            ]);

            if ($letter->status !== IncomingLetter::STATUS_COMPLETED) {
                $letter->update(['status' => IncomingLetter::STATUS_DISPOSED]);
            }

            $disposition->load('toUser');
            if ($disposition->toUser) {
                event(new NewNotificationEvent($disposition->toUser, [
                    'id' => $disposition->id,
                    'type' => 'letter_disposition',
                    'title' => 'Disposisi Surat Masuk: '.$letter->agenda_number,
                    'description' => $dispatcher->name.' mendisposisikan surat "'.$letter->subject.'" kepada Anda.',
                    'created_at' => now()->toDateTimeString(),
                ]));
            }

            return $disposition;
        });
    }

    public function updateDispositionStatus(LetterDisposition $disposition, string $status, ?string $notes = null): LetterDisposition
    {
        return DB::transaction(function () use ($disposition, $status, $notes) {
            $disposition->update([
                'status' => $status,
                'follow_up_notes' => $notes,
                'completed_at' => $status === LetterDisposition::STATUS_DONE ? now() : null,
            ]);

            $letter = $disposition->incomingLetter;
            if ($letter && $letter->status !== IncomingLetter::STATUS_COMPLETED) {
                $letter->update(['status' => IncomingLetter::STATUS_FOLLOWED_UP]);
            }

            return $disposition->fresh();
        });
    }

    public function archive(IncomingLetter $letter, User $archiver): IncomingLetter
    {
        $letter->update([
            'status' => IncomingLetter::STATUS_COMPLETED,
            'archived_by' => $archiver->id,
            'archived_at' => now(),
        ]);

        return $letter->fresh();
    }
}
