# Selamat, Anda Telah Diterima!

Halo {{ $user->name }},

Kami dengan senang hati menginformasikan bahwa Anda telah diterima sebagai siswa di sekolah kami. Akun belajar Anda telah dibuat:

- **Email:** {{ $user->email }}
- **Password Sementara:** {{ $password }}

**PENTING:** Anda wajib mengubah password Anda saat pertama kali login demi keamanan akun.

<x-mail::button :url="route('login')">
Login ke Dashboard Siswa
</x-mail::button>

Jika ada kendala dalam proses login, silakan hubungi bagian IT atau Tata Usaha.

Terima kasih,<br>
Sekolah Terpadu
</x-mail::message>
