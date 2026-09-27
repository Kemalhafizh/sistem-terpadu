<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kuitansi Pembayaran - {{ $payment->receipt_number }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.6; font-size: 12px; }
        .receipt-container { border: 2px solid #eee; padding: 30px; border-radius: 10px; }
        .header { border-bottom: 2px solid #4f46e5; padding-bottom: 20px; margin-bottom: 20px; }
        .school-name { font-size: 24px; font-weight: bold; color: #4f46e5; margin: 0; }
        .school-info { font-size: 10px; color: #666; margin: 0; }
        .receipt-title { text-align: center; font-size: 18px; font-weight: bold; text-transform: uppercase; margin: 20px 0; border: 1px solid #333; padding: 5px; width: 200px; margin-left: auto; margin-right: auto; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px 0; vertical-align: top; }
        .info-label { width: 150px; color: #666; }
        .info-value { font-weight: bold; }
        
        .payment-details { background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .amount-word { font-style: italic; color: #4b5563; font-size: 11px; margin-top: 10px; border-top: 1px dashed #ccc; padding-top: 10px; }
        
        .footer { margin-top: 50px; }
        .signature-box { float: right; width: 200px; text-align: center; }
        .signature-space { height: 60px; }
        .clear { clear: both; }
        
        .total-box { background: #4f46e5; color: white; padding: 10px 20px; font-size: 16px; font-weight: bold; display: inline-block; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <table style="border:none; margin:0;">
                <tr>
                    <td style="border:none;">
                        <h1 class="school-name">{{ mb_strtoupper(school_short_name()) }}</h1>
                        <p class="school-info">{{ school_address() }}<br>{{ school_contact_line(['phone', 'email']) }}</p>
                    </td>
                    <td style="border:none; text-align:right; vertical-align:top;">
                        <div style="font-weight:bold; font-size:14px;">KUITANSI PEMBAYARAN</div>
                        <div style="color:#666;">No: {{ $payment->receipt_number }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <table class="info-table">
            <tr>
                <td class="info-label">Telah terima dari</td>
                <td class="info-value">{{ $payment->billing?->student?->name }} ({{ $payment?->billing?->student->nis }})</td>
            </tr>
            <tr>
                <td class="info-label">Kelas</td>
                <td class="info-value">{{ $payment?->billing?->student?->classroom?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Untuk Pembayaran</td>
                <td class="info-value">{{ $payment->billing?->category?->name }} {{ $payment->billing->month ? '- Bulan ' . $payment->billing->month : '' }}</td>
            </tr>
            <tr>
                <td class="info-label">Metode Pembayaran</td>
                <td class="info-value" style="text-transform: capitalize;">{{ $payment->payment_method }}</td>
            </tr>
            <tr>
                <td class="info-label">Tanggal</td>
                <td class="info-value">{{ \Carbon\Carbon::parse($payment->payment_date)->translatedFormat('d F Y') }}</td>
            </tr>
        </table>

        <div class="payment-details">
            <div style="margin-bottom: 5px;">Jumlah Terbayar:</div>
            <div class="total-box">Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</div>
            
            <div class="amount-word">
                Terbilang: <strong>{{ \Illuminate\Support\Str::title(\App\Helpers\NumberHelper::terbilang($payment->amount_paid)) }} Rupiah</strong>
            </div>
            
            @if($payment->note)
                <div style="margin-top: 10px; font-size: 10px; color: #666;">
                    Catatan: {{ $payment->note }}
                </div>
            @endif
        </div>

        <div class="footer">
            <div style="float: left; font-size: 10px; color: #999; margin-top: 80px;">
                *Kuitansi ini sah sebagai bukti pembayaran yang valid.<br>
                Dicetak pada: {{ date('d/m/Y H:i:s') }}
            </div>
            
            <div class="signature-box">
                <p>Semarang, {{ \Carbon\Carbon::parse($payment->payment_date)->translatedFormat('d F Y') }}</p>
                <p>Petugas Keuangan,</p>
                <div class="signature-space"></div>
                <p><strong>{{ $payment?->creator?->name ?? 'Dibayar online (dikonfirmasi payment gateway)' }}</strong></p>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</body>
</html>
