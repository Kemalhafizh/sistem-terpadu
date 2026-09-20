<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebhookDelivery;
use App\Models\WebhookSubscription;
use App\Services\WebhookManagerService;
use Illuminate\Http\Request;

class WebhookHubController extends Controller
{
    /**
     * Display Webhook Management Console.
     */
    public function index()
    {
        $subscriptions = WebhookSubscription::withCount('deliveries')->orderBy('created_at', 'desc')->get();
        $recentDeliveries = WebhookDelivery::with('subscription')->orderBy('created_at', 'desc')->take(20)->get();

        $totalDeliveries = WebhookDelivery::count();
        $successfulDeliveries = WebhookDelivery::where('status', 'SENT')->count();
        $failedDeliveries = WebhookDelivery::where('status', 'FAILED')->count();

        $successRate = $totalDeliveries > 0 ? round(($successfulDeliveries / $totalDeliveries) * 100, 1) : 100.0;

        return view('admin.system.webhooks', compact(
            'subscriptions',
            'recentDeliveries',
            'totalDeliveries',
            'successfulDeliveries',
            'failedDeliveries',
            'successRate'
        ));
    }

    /**
     * Store new webhook subscription.
     */
    public function store(Request $request, WebhookManagerService $webhookManager)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'target_url' => 'required|url|max:255',
            'events' => 'required|array|min:1',
            'secret_key' => 'nullable|string|max:100',
        ]);

        try {
            $subscription = $webhookManager->registerSubscription([
                'name' => $request->name,
                'target_url' => $request->target_url,
                'secret_key' => $request->secret_key,
                'events' => $request->events,
                'is_active' => true,
            ]);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', 'Target URL Webhook ditolak demi keamanan (SSRF Protection): ' . $e->getMessage());
        }

        return back()->with('success', "Langganan Webhook [{$subscription->name}] berhasil mendaftar!");
    }

    /**
     * Toggle webhook subscription active status.
     */
    public function toggle(WebhookSubscription $subscription)
    {
        $subscription->is_active = ! $subscription->is_active;
        $subscription->save();

        $statusLabel = $subscription->is_active ? 'DIAKTIFKAN' : 'DINONAKTIFKAN';

        return back()->with('success', "Status Webhook [{$subscription->name}] berhasil {$statusLabel}.");
    }

    /**
     * Delete webhook subscription.
     */
    public function destroy(WebhookSubscription $subscription)
    {
        $name = $subscription->name;
        $subscription->delete();

        return back()->with('success', "Langganan Webhook [{$name}] telah dihapus.");
    }

    /**
     * Replay failed webhook delivery attempt.
     */
    public function replay($deliveryId, WebhookManagerService $webhookManager)
    {
        try {
            $delivery = $webhookManager->replayFailedDelivery($deliveryId);
            if ($delivery->status === 'SENT') {
                return back()->with('success', "Pengiriman Ulang (Replay Webhook #{$delivery->id}) SUKSES (HTTP {$delivery->response_code})!");
            } else {
                return back()->with('error', "Pengiriman Ulang (Replay Webhook #{$delivery->id}) GAGAL (HTTP {$delivery->response_code}).");
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memutar ulang webhook: ' . $e->getMessage());
        }
    }

    /**
     * Dispatch test event.
     */
    public function testDispatch(Request $request, WebhookManagerService $webhookManager)
    {
        $eventType = $request->input('event_type', 'student.registered');
        $payload = [
            'test_mode' => true,
            'demo_id' => rand(1000, 9999),
            'message' => 'Uji coba Event Streaming Bus SISTA',
            'dispatched_at' => now()->toIso8601String(),
        ];

        $res = $webhookManager->dispatch($eventType, $payload);

        return back()->with('success', "Event [{$eventType}] berhasil dipancarkan ke {$res['dispatched_count']} langganan aktif!");
    }
}
