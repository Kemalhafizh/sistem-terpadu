<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Jadwal Master Sekolah</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #333333;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px double #0d5c3a;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .school-name {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0d5c3a;
            margin: 0;
        }
        .school-subtitle {
            font-size: 7px;
            font-weight: bold;
            color: #475569;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            margin: 3px 0 8px 0;
            color: #0f172a;
        }
        .classroom-section {
            page-break-after: always;
            margin-bottom: 20px;
        }
        .classroom-section:last-child {
            page-break-after: avoid;
        }
        .classroom-header {
            font-size: 10px;
            font-weight: bold;
            color: #0d5c3a;
            border-bottom: 1px solid #0d5c3a;
            padding-bottom: 4px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
        }
        th {
            background-color: #475569;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7px;
            padding: 4px;
            border: 1px solid #475569;
            text-align: center;
        }
        td {
            padding: 4px;
            border: 1px solid #e2e8f0;
            text-align: center;
            vertical-align: top;
            height: 32px;
        }
        .time-col {
            background-color: #f8fafc;
            font-weight: bold;
            width: 70px;
            text-align: left;
            padding-left: 4px;
            height: auto;
        }
        .schedule-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            padding: 3px;
            margin: 1px;
            text-align: left;
        }
        .subject-code {
            font-size: 7px;
            font-weight: bold;
            color: #475569;
        }
        .subject-name {
            font-size: 7.5px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .teacher-name {
            font-size: 6.5px;
            color: #64748b;
            margin-top: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .room-name {
            font-size: 6.5px;
            font-weight: bold;
            color: #b45309;
            margin-top: 1px;
        }
        .empty-slot {
            color: #e2e8f0;
            font-size: 10px;
            padding-top: 8px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: right;
            font-size: 6px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 2px;
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
    <div class="doc-title">Master Jadwal Pelajaran Seluruh Kelas</div>

    @foreach($classrooms as $cls)
        @php
            $classSchedules = $schedules->where('classroom_id', $cls->id);
        @endphp
        <div class="classroom-section">
            <div class="classroom-header">
                Kelas: {{ $cls->name }} (Grade {{ $cls->grade_level }}) &mdash; Wali Kelas: {{ $cls->teacher->name ?? '-' }}
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 12%;">Jam Sesi</th>
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
                                <strong>Jam ke-{{ $i }}</strong>
                                <span style="font-size: 6px; color: #64748b; block">
                                    {{ $time ? date('H:i', strtotime($time->start_time)) . '-' . date('H:i', strtotime($time->end_time)) : '--:--' }}
                                </span>
                            </td>
                            @foreach($days as $day)
                                <td>
                                    @php 
                                        $existing = $classSchedules->filter(function($s) use ($day, $i) {
                                            return $s->day == $day && $i >= $s->session_start && $i <= $s->session_end;
                                        })->first();
                                    @endphp

                                    @if($existing)
                                        @if($existing->session_start == $i)
                                            <div class="schedule-box">
                                                <div class="subject-code">{{ $existing->subject->code }}</div>
                                                <div class="subject-name">{{ $existing->subject->name }}</div>
                                                <div class="teacher-name">{{ $existing->teacher->name }}</div>
                                                @if($existing->room)
                                                    <div class="room-name"> {{ $existing->room->name }}</div>
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
        </div>
    @endforeach

    <!-- Footer PDF -->
    <div class="footer">
        SMA Islam Sultan Agung 1 Semarang — Dokumen Jadwal Resmi SISTA
    </div>

</body>
</html>
