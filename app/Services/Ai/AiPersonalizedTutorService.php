<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Models\AiTutorMessage;
use App\Models\AiTutorSession;
use App\Models\User;

class AiPersonalizedTutorService
{
    /**
     * Start a new Socratic AI Tutor Session for a student.
     */
    public function startSession(User $student, string $subjectName, string $topic, string $difficulty = 'intermediate', ?string $learningObjective = null): AiTutorSession
    {
        $session = AiTutorSession::create([
            'student_id' => $student->id,
            'subject_name' => $subjectName,
            'topic' => $topic,
            'difficulty_level' => $difficulty,
            'learning_objective' => $learningObjective ?? "Memahami konsep dasar dan penerapan {$topic} secara mendalam.",
            'status' => 'active',
        ]);

        // Initial Socratic welcoming question
        AiTutorMessage::create([
            'session_id' => $session->id,
            'sender' => 'ai',
            'message' => "Assalamu'alaikum {$student->name}! Saya Asisten Tutor AI Sulaone. Hari ini kita akan menjelajahi materi '{$topic}'. Menurut pemahamanmu saat ini, bagaimana kamu mendefinisikan konsep ini?",
            'socratic_scaffold_type' => 'prompt_question',
            'comprehension_score' => 0.50,
        ]);

        return $session;
    }

    /**
     * Process student message and return AI Socratic scaffolding response.
     */
    public function replyMessage(AiTutorSession $session, string $studentMessage): array
    {
        // 1. Record student message
        $userMsg = AiTutorMessage::create([
            'session_id' => $session->id,
            'sender' => 'student',
            'message' => $studentMessage,
        ]);

        // 2. Socratic Scaffolding Engine logic
        $length = str_word_count($studentMessage);
        $comprehensionScore = min(1.0, 0.4 + ($length / 50.0));

        $socraticType = 'prompt_question';
        $aiResponse = '';

        if (stripos($studentMessage, 'bingung') !== false || stripos($studentMessage, 'tidak tahu') !== false || $length < 3) {
            $socraticType = 'hint';
            $aiResponse = "Tidak apa-apa! Mari kita urai perlahan. Coba bayangkan analogi sederhana terkait {$session->topic}. Jika dikaitkan dengan kehidupan sehari-hari, apa yang pertama kali terlintas?";
        } elseif ($comprehensionScore >= 0.75) {
            $socraticType = 'conceptual_bridge';
            $aiResponse = 'Maa syaa Allah, pemikiran yang sangat baik! Kamu sudah menangkap esensi utamanya. Sekarang, bisakah kamu mencoba menghubungkan konsep ini dengan contoh kasus atau rumus yang berlaku?';
        } else {
            $socraticType = 'prompt_question';
            $aiResponse = "Poin yang menarik! Mengapa menurutmu hal itu terjadi? Apa faktor utama yang mempengaruhinya pada materi {$session->topic}?";
        }

        // 3. Record AI message
        $aiMsg = AiTutorMessage::create([
            'session_id' => $session->id,
            'sender' => 'ai',
            'message' => $aiResponse,
            'socratic_scaffold_type' => $socraticType,
            'comprehension_score' => $comprehensionScore,
        ]);

        return [
            'session_id' => $session->id,
            'student_message' => $userMsg,
            'ai_response' => $aiMsg,
            'current_comprehension' => $comprehensionScore,
        ];
    }
}
