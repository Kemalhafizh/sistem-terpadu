<?php

namespace Database\Seeders;

use App\Models\KnowledgeArticle;
use App\Models\LessonStudy;
use App\Models\PlcGroup;
use App\Models\SharedResource;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KnowledgeManagementSeeder extends Seeder
{
    public function run()
    {
        $user = User::first() ?? User::factory()->create();
        for ($i = 1; $i <= 10; $i++) {
            KnowledgeArticle::create([
                'uuid' => Str::uuid(), 'author_id' => $user->id,
                'title' => 'Artikel Pengetahuan '.$i, 'content' => 'Konten artikel...',
                'category' => 'strategi_mengajar', 'status' => 'published',
            ]);
        }
        LessonStudy::create(['uuid' => Str::uuid(), 'title' => 'Lesson Study 1', 'model_teacher_id' => $user->id, 'session_date' => now(), 'status' => 'completed', 'phase' => 'completed']);
        LessonStudy::create(['uuid' => Str::uuid(), 'title' => 'Lesson Study 2', 'model_teacher_id' => $user->id, 'session_date' => now(), 'status' => 'in_progress', 'phase' => 'do']);
        for ($i = 1; $i <= 3; $i++) {
            PlcGroup::create(['name' => 'Kelompok PLC '.$i, 'type' => 'rumpun_mapel', 'coordinator_id' => $user->id]);
        }
        for ($i = 1; $i <= 15; $i++) {
            SharedResource::create([
                'uuid' => Str::uuid(), 'uploader_id' => $user->id, 'title' => 'Sumber Daya '.$i,
                'resource_type' => 'bahan_ajar', 'file_path' => 'path/to/file', 'file_type' => 'pdf', 'file_size' => 1024,
            ]);
        }
    }
}
