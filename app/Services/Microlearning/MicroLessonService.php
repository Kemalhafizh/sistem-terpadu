<?php
namespace App\Services\Microlearning;

use App\Models\MicroLesson;

class MicroLessonService
{
    public function generateFromSyllabus($syllabusId, $title) {
        return MicroLesson::create(['syllabus_id' => $syllabusId, 'title' => $title]);
    }
    public function publishMicroLesson(MicroLesson $lesson) {
        $lesson->update(['is_published' => true]);
        return $lesson;
    }
    public function getStudentProgress($studentId) {
        return [];
    }
}
