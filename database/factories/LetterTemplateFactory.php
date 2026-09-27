<?php

namespace Database\Factories;

use App\Models\LetterTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class LetterTemplateFactory extends Factory
{
    protected $model = LetterTemplate::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3).' Template',
            'code' => strtoupper($this->faker->lexify('???')),
            'body_template' => 'Yang bertanda tangan di bawah ini menerangkan bahwa siswa bernama {{nama_siswa}} dengan NIS {{nis}} / NISN {{nisn}} kelas {{kelas}} lahir pada {{tanggal_lahir}} adalah benar aktif belajar. Surat ini dibuat untuk keperluan {{keperluan}}.',
            'required_fields' => ['keperluan'],
            'signer_name' => $this->faker->name(),
            'signer_title' => 'Kepala Sekolah',
            'is_active' => true,
        ];
    }
}
