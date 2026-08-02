<?php

namespace App\Services\Community;

use App\Models\Partnership;

class PartnershipService
{
    public function registerPartner(string $orgName, string $partnerType, int $creatorId, ?string $contactPerson = null, ?string $contactEmail = null): Partnership
    {
        return Partnership::create([
            'organization_name' => $orgName,
            'partner_type' => $partnerType,
            'contact_person' => $contactPerson,
            'contact_email' => $contactEmail,
            'status' => 'active',
            'valid_until' => now()->addYear(),
            'created_by' => $creatorId,
        ]);
    }
}
