<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Jadwal Guru - {{ $teacher->name }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px double #0d5c3a;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .school-name {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0d5c3a;
            margin: 0;
        }
        .school-subtitle {
            font-size: 8px;
            font-weight: bold;
            color: #475569;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            margin: 5px 0 10px 0;
            color: #0f172a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            table-layout: fixed;
        }
        th {
            background-color: #0d5c3a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
            padding: 6px;
            border: 1px solid #0d5c3a;
            text-align: center;
        }
        td {
            padding: 6px 4px;
            border: 1px solid #cbd5e1;
            text-align: center;
            vertical-align: top;
            height: 45px;
        }
        .time-col {
            background-color: #f8fafc;
            font-weight: bold;
            width: 80px;
            text-align: left;
            padding-left: 6px;
            height: auto;
        }
        .schedule-box {
            background-color: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 4px;
            padding: 4px;
            margin: 1px;
            text-align: left;
        }
        .subject-code {
            font-size: 7px;
            font-weight: bold;
            color: #3730a3;
            text-transform: uppercase;
        }
        .subject-name {
            font-size: 8px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 1px;
        }
        .classroom-name {
            font-size: 7px;
            font-weight: bold;
            color: #475569;
            margin-top: 2px;
        }
        .room-name {
            font-size: 7px;
            font-weight: bold;
            color: #b45309;
            margin-top: 2px;
        }
        .empty-slot {
            color: #cbd5e1;
            font-size: 12px;
            padding-top: 12px;
        }
        .sign-area {
            margin-top: 20px;
            width: 100%;
        }
        .sign-area td {
            border: none;
            width: 50%;
            height: auto;
            text-align: center;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: right;
            font-size: 7px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
        }
    </style>
</head>
<body>

    <!-- School Header -->
    <div class="header">
        <h1 class="school-name">SMA Islam Sultan Agung 1 Semarang</h1>
        <p class="school-subtitle">Sistem Informasi Sekolah Terpadu SISTA</p>
    </div>

    <!-- Doc Title -->
    <div class="doc-title">Jadwal Mengajar Guru: {{ $teacher->name }}</div>

    <!-- Grid Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Jam Sesi</th>
                @foreach($days as $day)
                    <th>{{ $day }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @for($i = 1; $i <= 11; $i++)
                @php 
                    $time = $activePreset ? $activePreset->times->where('session_number', $i)->first() : null; 
                @endphp
                <tr>
                    <td class="time-col">
                        <strong>Jam ke-{{ $i }}</strong><br>
                        <span style="font-size: 7px; color: #64748b;">
                            {{ $time ? date('H:i', strtotime($time->start_time)) . ' - ' . date('H:i', strtotime($time->end_time)) : '--:--' }}
                        </span>
                    </td>
                    @foreach($days as $day)
                        <td>
                            @php 
                                $existing = $schedules->filter(function($s) use ($day, $i) {
                                    return $s->day == $day && $i >= $s->session_start && $i <= $s->session_end;
                                })->first();
                            @endphp

                            @if($existing)
                                @if($existing->session_start == $i)
                                    <div class="schedule-box">
                                        <div class="subject-code">{{ $existing->subject->code }}</div>
                                        <div class="subject-name">{{ $existing->subject->name }}</div>
                                        <div class="classroom-name">Kelas: {{ $existing->classroom->name }}</div>
                                        @if($existing->room)
                                            <div class="room-name">📍 {{ $existing->room->name }}</div>
                                        @endif
                                    </div>
                                @endif
                            @else
                                <div class="empty-slot">-</div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Sign-off Area -->
    <table class="sign-area">
        <tr>
            <td></td>
            <td style="text-align: center;">
                Semarang, {{ now()->format('d M Y') }}<br>
                Waka Kurikulum SMA ISA 1,<br><br><br><br>
                <span style="font-weight: bold; text-decoration: underline;">Staf Kurikulum Sekolah</span>
            </td>
        </tr>
    </table>

    <!-- Footer PDF -->
    <div class="footer">
        SMA Islam Sultan Agung 1 Semarang — Dokumen Jadwal Resmi SISTA
    </div>

</body>
</html>
