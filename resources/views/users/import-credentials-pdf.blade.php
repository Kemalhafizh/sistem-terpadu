<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $batch_label }} - {{ $school_name }}</title>
    <style>
        @page { margin: 20px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #1f2937; line-height: 1.4; }
        .header { border-bottom: 2px solid #065f46; padding-bottom: 8px; margin-bottom: 12px; }
        .title { font-size: 14px; font-weight: bold; color: #064e3b; margin: 0 0 2px 0; }
        .subtitle { font-size: 9.5px; color: #4b5563; margin: 0; }
        .notice { background: #fef3c7; border: 1px solid #f59e0b; padding: 7px 10px; margin-bottom: 10px; font-size: 9.5px; color: #78350f; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #d1d5db; padding: 5px 7px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; font-size: 9px; text-transform: uppercase; }
        .mono { font-family: monospace; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">{{ $school_name }} &mdash; {{ $batch_label }}</p>
        <p class="subtitle">Lembar Kredensial Akun Baru &bull; Dicetak: {{ $generated_at }}</p>
    </div>

    <div class="notice">
        <strong>Catatan Keamanan:</strong> Password sementara hanya tampil satu kali saat impor/pembuatan akun.
        Setiap pengguna wajib langsung mengganti kata sandi pada login pertama.
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th>Jenis Akun</th>
                <th>Nama</th>
                <th>ID Login (Email / HP / NISN / NIY)</th>
                <th>Password Sementara</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($credentials as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $item['role_label'] ?? '-' }}</td>
                    <td><strong>{{ $item['name'] ?? '-' }}</strong></td>
                    <td class="mono">{{ $item['identifier'] ?? '-' }}</td>
                    <td class="mono">{{ $item['temp_password'] ?? '-' }}</td>
                    <td>{{ $item['notes'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada akun baru pada batch ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
