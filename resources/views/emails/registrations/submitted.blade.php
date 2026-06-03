# Pendaftaran Berhasil Diterima!

Halo {{ $registration->full_name }},

Terima kasih telah mendaftar di Sekolah Kami. Pendaftaran Anda telah kami terima dengan detail sebagai berikut:

- **Nomor Pendaftaran:** {{ $registration->registration_number }}
- **NISN:** {{ $registration->nisn }}
- **Asal Sekolah:** {{ $registration->previous_school }}

Silakan simpan nomor pendaftaran di atas untuk mengecek status pendaftaran Anda secara berkala melalui website kami.

<x-mail::button :url="route('spmb.status', ['registration_number' => $registration->registration_number, 'nisn' => $registration->nisn])">
Cek Status Pendaftaran
</x-mail::button>

Terima kasih,<br>
Panitia SPMB
</x-mail::message>
