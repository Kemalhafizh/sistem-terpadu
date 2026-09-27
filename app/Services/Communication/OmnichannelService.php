<?php

namespace App\Services\Communication;

use App\Models\OmnichannelLog;
use App\Models\OmnichannelTemplate;

class OmnichannelService
{
    public function dispatchMessage($userId, $channel, $recipientAddress, $templateId, $data = [])
    {
        return OmnichannelLog::create([
            'user_id' => $userId,
            'channel' => $channel,
            'recipient_address' => $recipientAddress,
            'template_id' => $templateId,
            'delivery_status' => 'queued',
        ]);
    }

    public function resolveTemplateVariables(OmnichannelTemplate $template, array $data)
    {
        $content = $template->body_content;
        foreach ($data as $key => $value) {
            $content = str_replace('{{'.$key.'}}', $value, $content);
        }

        return $content;
    }

    public function getDeliveryRate()
    {
        $total = OmnichannelLog::count();
        if ($total === 0) {
            return 0;
        }
        $delivered = OmnichannelLog::where('delivery_status', 'delivered')->count();

        return ($delivered / $total) * 100;
    }
}
