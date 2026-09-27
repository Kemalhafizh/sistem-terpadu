<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use App\Services\SmartNotificationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class SmartNotificationController extends Controller
{
    use ApiResponseTrait;

    protected SmartNotificationService $notificationService;

    public function __construct(SmartNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * GET /api/v1/notifications/preferences
     */
    public function getPreferences(Request $request)
    {
        $userId = $request->user()->id;
        $pref = NotificationPreference::firstOrCreate(
            ['user_id' => $userId],
            [
                'event_category' => 'all',
                'digest_mode' => false,
                'digest_interval' => 'realtime',
                'channel_preferences' => [
                    'exam' => true,
                    'assignment' => true,
                    'school_info' => true,
                    'finance' => true,
                    'emergency' => true,
                ],
                'quiet_hours' => [
                    'enabled' => true,
                    'start' => '22:00',
                    'end' => '04:30',
                ],
                'smart_grouping' => true,
            ]
        );

        $exam = $pref->channel_preferences['exam'] ?? true;
        $assignment = $pref->channel_preferences['assignment'] ?? true;

        return $this->successResponse([
            'examEnabled' => $exam,
            'examNotifsEnabled' => $exam,
            'assignmentEnabled' => $assignment,
            'assignmentNotifsEnabled' => $assignment,
            'schoolInfoEnabled' => $pref->channel_preferences['school_info'] ?? true,
            'digestMode' => $pref->digest_interval ?: 'Realtime',
            'quietHoursEnabled' => $pref->quiet_hours['enabled'] ?? true,
            'quietHoursStart' => $pref->quiet_hours['start'] ?? '22:00',
            'quietHoursEnd' => $pref->quiet_hours['end'] ?? '04:30',
        ], 'Notification preferences retrieved successfully');
    }

    /**
     * PUT /api/v1/notifications/preferences
     */
    public function updatePreferences(Request $request)
    {
        $userId = $request->user()->id;
        $data = $request->all();

        $pref = NotificationPreference::firstOrNew(['user_id' => $userId]);
        if (! $pref->exists) {
            $pref->event_category = 'all';
        }

        $channelPref = $pref->channel_preferences ?: [];
        if (isset($data['examEnabled'])) {
            $channelPref['exam'] = (bool) $data['examEnabled'];
        }
        if (isset($data['examNotifsEnabled'])) {
            $channelPref['exam'] = (bool) $data['examNotifsEnabled'];
        }
        if (isset($data['assignmentEnabled'])) {
            $channelPref['assignment'] = (bool) $data['assignmentEnabled'];
        }
        if (isset($data['assignmentNotifsEnabled'])) {
            $channelPref['assignment'] = (bool) $data['assignmentNotifsEnabled'];
        }
        if (isset($data['schoolInfoEnabled'])) {
            $channelPref['school_info'] = (bool) $data['schoolInfoEnabled'];
        }

        $pref->channel_preferences = $channelPref;
        if (isset($data['digestMode'])) {
            $pref->digest_interval = $data['digestMode'];
            $pref->digest_mode = strtolower($data['digestMode']) !== 'realtime';
        }

        if (isset($data['quietHoursEnabled'])) {
            $qh = $pref->quiet_hours ?: [];
            $qh['enabled'] = (bool) $data['quietHoursEnabled'];
            if (isset($data['quietHoursStart'])) {
                $qh['start'] = $data['quietHoursStart'];
            }
            if (isset($data['quietHoursEnd'])) {
                $qh['end'] = $data['quietHoursEnd'];
            }
            $pref->quiet_hours = $qh;
        }

        $pref->save();

        return $this->successResponse($pref, 'Notification preferences updated successfully');
    }

    /**
     * GET /api/v1/notifications/digest
     */
    public function getDigest(Request $request)
    {
        $userId = $request->user()->id;
        $digest = $this->notificationService->buildDigest($userId);

        return $this->successResponse($digest, 'Notification digest retrieved successfully');
    }
}
