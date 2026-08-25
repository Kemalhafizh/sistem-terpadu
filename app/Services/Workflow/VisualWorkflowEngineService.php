<?php

declare(strict_types=1);

namespace App\Services\Workflow;

use App\Models\User;
use App\Models\WorkflowBlueprint;
use App\Models\WorkflowNode;

class VisualWorkflowEngineService
{
    /**
     * Create or update visual BPMN workflow blueprint
     */
    public function saveBlueprint(User $creator, array $data, ?int $blueprintId = null): WorkflowBlueprint
    {
        $payload = [
            'name' => $data['name'],
            'process_type' => $data['process_type'] ?? 'rkas_approval',
            'bpmn_schema' => $data['bpmn_schema'] ?? [
                'nodes' => [
                    ['id' => 'start', 'type' => 'start', 'label' => 'Mulai Pengajuan'],
                    ['id' => 'review_waka', 'type' => 'user_task', 'label' => 'Persetujuan Waka Kurikulum'],
                    ['id' => 'review_kepsek', 'type' => 'user_task', 'label' => 'Persetujuan Kepala Sekolah'],
                    ['id' => 'end', 'type' => 'end', 'label' => 'Selesai & Disetujui'],
                ],
            ],
            'is_active' => $data['is_active'] ?? true,
            'created_by' => $creator->id,
        ];

        if ($blueprintId) {
            $blueprint = WorkflowBlueprint::find($blueprintId);
            if ($blueprint) {
                $blueprint->update($payload);
            } else {
                $blueprint = WorkflowBlueprint::create($payload);
            }
        } else {
            $blueprint = WorkflowBlueprint::create($payload);
        }

        // Synchronize individual nodes
        if (isset($data['bpmn_schema']['nodes'])) {
            $blueprint->nodes()->delete();
            foreach ($data['bpmn_schema']['nodes'] as $n) {
                WorkflowNode::create([
                    'workflow_blueprint_id' => $blueprint->id,
                    'node_key' => $n['id'],
                    'node_name' => $n['label'] ?? $n['id'],
                    'node_type' => $n['type'] ?? 'user_task',
                    'configuration' => $n['config'] ?? [],
                ]);
            }
        }

        return $blueprint;
    }
}
