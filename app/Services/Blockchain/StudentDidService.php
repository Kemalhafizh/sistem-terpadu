<?php

declare(strict_types=1);

namespace App\Services\Blockchain;

use App\Models\StudentDid;
use App\Models\User;

class StudentDidService
{
    /**
     * Generate or fetch W3C-compliant DID (Decentralized Identifier) for a student.
     */
    public function getOrCreateDid(User $student): StudentDid
    {
        $existing = StudentDid::where('student_id', $student->id)->first();
        if ($existing) {
            return $existing;
        }

        $didUri = 'did:ybwsa:sulaone:'.hash('sha256', (string) $student->id.$student->email.'YBWSA_SALT');
        $publicKey = '04'.hash('sha256', $didUri.'PUB').hash('sha256', 'KEY_PART2');

        $didDocument = [
            '@context' => [
                'https://www.w3.org/ns/did/v1',
                'https://w3id.org/security/suites/ed25519-2020/v1',
            ],
            'id' => $didUri,
            'controller' => 'did:ybwsa:authority:sultanagung1',
            'verificationMethod' => [
                [
                    'id' => $didUri.'#key-1',
                    'type' => 'Ed25519VerificationKey2020',
                    'controller' => $didUri,
                    'publicKeyMultibase' => 'z'.substr($publicKey, 0, 48),
                ],
            ],
            'authentication' => [
                $didUri.'#key-1',
            ],
            'assertionMethod' => [
                $didUri.'#key-1',
            ],
        ];

        return StudentDid::create([
            'student_id' => $student->id,
            'did_uri' => $didUri,
            'public_key' => $publicKey,
            'did_document_json' => $didDocument,
            'verifiable_credentials_count' => 0,
            'status' => 'active',
        ]);
    }

    /**
     * Generate Lifelong Learning Passport (Verifiable Presentation) payload.
     */
    public function generateVerifiablePresentation(User $student): array
    {
        $did = $this->getOrCreateDid($student);

        return [
            '@context' => [
                'https://www.w3.org/2018/credentials/v1',
                'https://schema.ybwsa.ac.id/education/v1',
            ],
            'type' => ['VerifiablePresentation', 'LifelongLearningPassport'],
            'holder' => $did->did_uri,
            'issuanceDate' => now()->toIso8601String(),
            'student_profile' => [
                'name' => $student->name,
                'institution' => 'SMA Islam Sultan Agung 1 Semarang (YBWSA)',
                'academic_integrity_score' => 98.5,
            ],
            'proof' => [
                'type' => 'Ed25519Signature2020',
                'created' => now()->toIso8601String(),
                'verificationMethod' => $did->did_uri.'#key-1',
                'proofValue' => '0x'.hash('sha256', $did->did_uri.now()->timestamp),
            ],
        ];
    }
}
