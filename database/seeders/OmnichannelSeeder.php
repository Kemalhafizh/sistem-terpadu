<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OmnichannelTemplate;
use App\Models\DripCampaign;
use App\Models\NpsCampaign;
use App\Models\MediaContact;
use App\Models\User;
use App\Models\OmnichannelLog;
use App\Models\CampaignStep;
use App\Models\NpsResponse;

class OmnichannelSeeder extends Seeder
{
    public function run()
    {
        $admin = User::first() ?? User::factory()->create();

        $template = OmnichannelTemplate::create([
            'template_code' => 'WELCOME_MSG',
            'subject' => 'Selamat Datang di SISTA',
            'body_content' => 'Halo {{name}}, selamat bergabung.',
            'allowed_channels' => ['email', 'whatsapp'],
            'variables' => ['name'],
        ]);
        
        OmnichannelLog::create([
            'user_id' => $admin->id,
            'channel' => 'email',
            'recipient_address' => 'admin@example.com',
            'template_id' => $template->id,
            'delivery_status' => 'delivered',
        ]);

        $drip = DripCampaign::create([
            'campaign_name' => 'Orientasi Siswa Baru',
            'trigger_event' => 'onboarding',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $drip->steps()->create([
            'step_order' => 1,
            'delay_minutes' => 0,
            'omnichannel_template_id' => $template->id,
            'conditions' => [],
        ]);

        $nps = NpsCampaign::create([
            'campaign_title' => 'Survei Kepuasan Tengah Semester',
            'start_date' => now(),
            'end_date' => now()->addDays(14),
            'target_audience' => 'all_parents',
            'created_by' => $admin->id,
        ]);

        $nps->responses()->create([
            'parent_id' => $admin->id,
            'score' => 9,
            'verbatim_feedback' => 'Sangat memuaskan.',
            'category' => 'promoter',
        ]);

        MediaContact::create([
            'journalist_name' => 'Budi Santoso',
            'media_outlet' => 'Suara Merdeka',
            'email' => 'budi@suaramerdeka.com',
            'phone_number' => '081234567890',
            'relationship_status' => 'warm',
        ]);
    }
}
