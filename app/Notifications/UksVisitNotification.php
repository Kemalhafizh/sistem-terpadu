<?php

namespace App\Notifications;

use App\Models\UksVisit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UksVisitNotification extends Notification
{
    use Queueable;

    public UksVisit $visit;

    public function __construct(UksVisit $visit)
    {
        $this->visit = $visit;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Kunjungan UKS',
            'message' => 'Ananda '.($this->visit->student?->name ?? 'Siswa').' ditangani di UKS pada '
                .$this->visit->visit_time->format('d-m-Y H:i').' dengan keluhan: '.$this->visit->complaint.'.',
            'uks_visit_id' => $this->visit->id,
            'complaint' => $this->visit->complaint,
            'diagnosis' => $this->visit->diagnosis,
            'action_url' => '/uks',
        ];
    }
}
