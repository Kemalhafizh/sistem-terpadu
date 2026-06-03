<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Stiker Label QR Code Inventaris Aset</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .page-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1a202c;
        }
        table.label-grid {
            width: 100%;
            border-collapse: collapse;
        }
        td.label-cell {
            width: 33.3%;
            padding: 10px;
            vertical-align: top;
        }
        .label-sticker {
            border: 1px dashed #cbd5e0;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            background-color: #fafafb;
        }
        .school-header {
            font-size: 8px;
            font-weight: 800;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .asset-name {
            font-size: 10px;
            font-weight: bold;
            color: #1f2937;
            margin: 4px 0;
            height: 24px;
            overflow: hidden;
        }
        .qr-code-img {
            margin: 6px 0;
            width: 80px;
            height: 80px;
        }
        .asset-code {
            font-family: monospace;
            font-size: 9px;
            font-weight: bold;
            color: #2563eb;
            letter-spacing: -0.3px;
        }
        .footer-tag {
            font-size: 6px;
            color: #9ca3af;
            margin-top: 5px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <div class="page-title">
        Lembar Stiker Label Inventaris Aset Sekolah
    </div>

    <table class="label-grid">
        @php $cellCount = 0; @endphp
        @foreach($assets as $asset)
            @if($cellCount % 3 === 0)
                <tr>
            @endif

            <td class="label-cell">
                <div class="label-sticker">
                    <div class="school-header">SMA ISLAM SULTAN AGUNG 1</div>
                    <div style="border-bottom: 1px solid #e5e7eb; margin-bottom: 6px;"></div>
                    <div class="asset-name">{{ Str::limit($asset->name, 38) }}</div>
                    
                    <!-- Free QR Code Generator API -->
                    <img class="qr-code-img" src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode(route('assets.show', $asset->uuid)) }}" alt="QR Label">
                    
                    <div class="asset-code">{{ $asset->asset_code }}</div>
                    <div class="footer-tag">SISTA Sarpras Security Tag</div>
                </div>
            </td>

            @php $cellCount++; @endphp
            @if($cellCount % 3 === 0)
                </tr>
            @endif
        @endforeach

        <!-- Pad empty cells if the final row is incomplete -->
        @if($cellCount % 3 !== 0)
            @while($cellCount % 3 !== 0)
                <td class="label-cell" style="border: none;"></td>
                @php $cellCount++; @endphp
            @endwhile
            </tr>
        @endif
    </table>

</body>
</html>
