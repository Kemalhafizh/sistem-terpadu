<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notulensi Rapat - {{ $meetingNote->title }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; font-size: 11px; }
        .container { padding: 20px; }
        
        /* Kop Surat */
        .header { border-bottom: 3px double #064e3b; padding-bottom: 12px; margin-bottom: 20px; text-align: center; }
        .school-name { font-size: 16px; font-weight: bold; color: #064e3b; margin: 0; text-transform: uppercase; }
        .school-subtitle { font-size: 12px; font-weight: bold; color: #374151; margin: 2px 0 0 0; }
        .school-info { font-size: 8px; color: #666; margin: 2px 0 0 0; }
        
        .title { text-align: center; font-size: 13px; font-weight: bold; text-transform: uppercase; margin-bottom: 15px; color: #111827; }
        
        /* Info Grid */
        table.info-table { width: 100%; margin-bottom: 15px; border: none; }
        table.info-table td { padding: 3px 0; border: none; vertical-align: top; }
        .info-label { width: 100px; font-weight: bold; color: #4b5563; }
        .info-colon { width: 15px; text-align: center; color: #4b5563; }
        .info-value { font-weight: bold; color: #111827; }
        
        /* Section styling */
        .section-title { font-size: 11px; font-weight: bold; color: #064e3b; text-transform: uppercase; border-bottom: 1px solid #d1d5db; padding-bottom: 3px; margin-top: 15px; margin-bottom: 8px; }
        .section-content { margin-bottom: 15px; padding-left: 5px; text-align: justify; }
        
        /* Tables */
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 15px; }
        table.data-table th { background-color: #f3f4f6; border: 1px solid #d1d5db; padding: 6px; font-size: 9px; font-weight: bold; text-align: left; }
        table.data-table td { border: 1px solid #e5e7eb; padding: 6px; font-size: 9px; }
        
        /* Attendees string format */
        .attendees-box { background: #f9fafb; border: 1px solid #e5e7eb; padding: 8px 12px; border-radius: 4px; margin-bottom: 15px; }
        
        /* Footer / Signature */
        .footer-signatures { margin-top: 30px; }
        .signature-box { float: right; width: 180px; text-align: center; }
        .signature-box.left { float: left; }
        .signature-space { height: 50px; }
        .clear { clear: both; }
    </style>
</head>
<body>
    <div class="container">
        <!-- School Kop Surat -->
        <div class="header">
            <h1 class="school-name">{{ school_foundation_name(false) }}</h1>
            <h2 class="school-subtitle">{{ mb_strtoupper(school_name()) }}</h2>
            <p class="school-info">
                @if(school_accreditation())Terakreditasi {{ school_accreditation() }} &bull; @endif{{ school_address() }}<br>
                {{ school_contact_line(['phone', 'email', 'website']) }}
            </p>
        </div>

        <div class="title">NOTULENSI RAPAT: {{ $meetingNote->title }}</div>

        <!-- Meeting Meta info -->
        <table class="info-table">
            <tr>
                <td style="width: 50%;">
                    <table>
                        <tr>
                            <td class="info-label">Jenis Rapat</td>
                            <td class="info-colon">:</td>
                            <td class="info-value uppercase">{{ str_replace('_', ' ', $meetingNote->type) }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Hari / Tanggal</td>
                            <td class="info-colon">:</td>
                            <td class="info-value">{{ $meetingNote->meeting_date->translatedFormat('l, d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Waktu</td>
                            <td class="info-colon">:</td>
                            <td class="info-value">
                                {{ date('H:i', strtotime($meetingNote->start_time)) }} 
                                @if($meetingNote->end_time)
                                    - {{ date('H:i', strtotime($meetingNote->end_time)) }}
                                @endif
                                WIB
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%;">
                    <table>
                        <tr>
                            <td class="info-label">Tempat / Lokasi</td>
                            <td class="info-colon">:</td>
                            <td class="info-value">{{ $meetingNote->location ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Pimpinan Rapat</td>
                            <td class="info-colon">:</td>
                            <td class="info-value">{{ $meetingNote->creator->name ?? 'Staf Sekolah' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Status Dokumen</td>
                            <td class="info-colon">:</td>
                            <td class="info-value uppercase">{{ $meetingNote->status }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- 1. Agenda -->
        <div class="section-title">1. Agenda Rapat</div>
        <div class="section-content">
            {!! $agendaHtml ?: '<i>Tidak ada agenda tertulis.</i>' !!}
        </div>

        <!-- 2. Pembahasan -->
        <div class="section-title">2. Pembahasan & Diskusi</div>
        <div class="section-content">
            {!! $discussionHtml ?: '<i>Tidak ada pembahasan tertulis.</i>' !!}
        </div>

        <!-- 3. Keputusan -->
        <div class="section-title">3. Keputusan Rapat</div>
        <div class="section-content" style="background-color: #f9fafb; padding: 8px; border-radius: 4px; border: 1px solid #e5e7eb;">
            {!! $decisionsHtml ?: '<i>Tidak ada keputusan penting tertulis.</i>' !!}
        </div>

        <!-- 4. Kehadiran -->
        <div class="section-title">4. Peserta Rapat Terdaftar (Hadir)</div>
        <div class="attendees-box">
            @if($attendeeUsers->isNotEmpty())
                {{ implode(', ', $attendeeUsers->pluck('name')->toArray()) }}
            @else
                <i>Tidak ada daftar peserta terdaftar.</i>
            @endif
        </div>

        <!-- 5. Action Items -->
        @if(!empty($meetingNote->action_items))
            <div class="section-title">5. Tindak Lanjut Rapat (Action Items)</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">NO</th>
                        <th style="width: 50%;">TUGAS / TINDAK LANJUT</th>
                        <th style="width: 25%;">PENANGGUNG JAWAB (PIC)</th>
                        <th style="width: 20%;">BATAS WAKTU</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($meetingNote->action_items as $index => $item)
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td style="font-weight: bold;">
                                {{ $item['task'] }}
                                @if($item['is_completed'] ?? false)
                                    <span style="color: #10b981; font-weight: bold;">[SELESAI]</span>
                                @endif
                            </td>
                            <td>{{ $item['pic'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($item['deadline'])->translatedFormat('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Footer / Signatures -->
        <div class="footer-signatures">
            <div class="signature-box left">
                <p>Mengetahui,</p>
                <p>Pimpinan Rapat,</p>
                <div class="signature-space"></div>
                <p><strong>{{ $meetingNote->creator->name ?? 'Staf Sekolah' }}</strong></p>
                <p style="font-size: 8px; color: #666; margin-top: 2px;">NIP/NIY. {{ $meetingNote->creator->employee->nip ?? '-' }}</p>
            </div>
            
            <div class="signature-box">
                <p>Semarang, {{ $meetingNote->meeting_date->translatedFormat('d F Y') }}</p>
                <p>Notulis Rapat,</p>
                <div class="signature-space"></div>
                <p><strong>..................................................</strong></p>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</body>
</html>
