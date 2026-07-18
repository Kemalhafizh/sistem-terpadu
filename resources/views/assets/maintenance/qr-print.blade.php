<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label QR Perbaikan Sarpras</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        
        /* Top Navigation/Bar (Hides on print) */
        .no-print-bar {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
        }
        .bar-title {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .bar-desc {
            margin: 4px 0 0 0;
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }
        .btn-print {
            background-color: #0f766e;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-print:hover {
            background-color: #0d9488;
            transform: translateY(-1px);
        }
        
        /* Printable container */
        .print-container {
            max-width: 210mm;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 15mm;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            min-height: 297mm;
        }
        
        /* Sticker Grid */
        .sticker-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8mm;
        }
        
        /* Sticker Box Styling */
        .sticker-box {
            border: 1.5px dashed #cbd5e1;
            border-radius: 12px;
            padding: 14px;
            text-align: center;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 80mm;
            page-break-inside: avoid;
            position: relative;
            overflow: hidden;
            transition: border-color 0.2s;
        }
        .sticker-box:hover {
            border-color: #0f766e;
        }
        
        /* Corner decoration */
        .sticker-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #0f766e, #0d9488);
        }

        .school-title {
            font-size: 9px;
            font-weight: 800;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        
        .school-location {
            font-size: 7px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 6px;
        }

        .divider {
            border-bottom: 1px solid #e2e8f0;
            margin: 0 auto 8px auto;
            width: 85%;
        }

        .asset-name {
            font-size: 11px;
            font-weight: 800;
            color: #1e293b;
            line-height: 1.3;
            height: 32px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            margin-bottom: 8px;
            padding: 0 4px;
        }

        .qr-wrapper {
            margin: 6px auto;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 96px;
            height: 96px;
            background-color: #ffffff;
            padding: 4px;
            border-radius: 8px;
            box-shadow: inset 0 0 0 1px #e2e8f0;
        }

        .asset-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            font-weight: 700;
            color: #2563eb;
            margin: 6px 0 2px 0;
            letter-spacing: -0.3px;
        }

        .scan-instruction {
            font-size: 7.5px;
            font-weight: 700;
            color: #64748b;
            line-height: 1.2;
            padding: 0 8px;
            margin-top: 4px;
        }
        
        /* Print Stylesheet Overrides */
        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .print-container {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                max-width: 100% !important;
                min-height: auto !important;
            }
            .sticker-grid {
                gap: 6mm !important;
            }
            .sticker-box {
                border: 1px dashed #94a3b8 !important;
                height: 80mm !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <!-- Top Bar (Hides on print) -->
    <div class="no-print-bar">
        <div>
            <h1 class="bar-title">Lembar Cetak Stiker QR Code Inventaris</h1>
            <p class="bar-desc">Didesain khusus untuk kertas stiker A4. Siapkan printer Anda dan gunakan setelan Margin: "None" atau "Default" saat mencetak.</p>
        </div>
        <button class="btn-print" onclick="window.print()">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            <span>Cetak Halaman (Print)</span>
        </button>
    </div>

    <!-- Print Canvas -->
    <div class="print-container">
        <div class="sticker-grid">
            @foreach($assets as $asset)
                <div class="sticker-box">
                    <div>
                        <div class="school-title">SMA Islam Sultan Agung 1</div>
                        <div class="school-location">Semarang</div>
                        <div class="divider"></div>
                        <div class="asset-name" title="{{ $asset->name }}">{{ $asset->name }}</div>
                    </div>
                    
                    <!-- Simulated Vector QR Code -->
                    <div class="qr-wrapper">
                        <svg width="86" height="86" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #0f766e;">
                            <!-- Finder Pattern Top Left -->
                            <rect x="5" y="5" width="25" height="25" rx="4" fill="currentColor"/>
                            <rect x="10" y="10" width="15" height="15" fill="white"/>
                            <rect x="13" y="13" width="9" height="9" rx="2" fill="currentColor"/>
                            
                            <!-- Finder Pattern Top Right -->
                            <rect x="70" y="5" width="25" height="25" rx="4" fill="currentColor"/>
                            <rect x="75" y="10" width="15" height="15" fill="white"/>
                            <rect x="78" y="13" width="9" height="9" rx="2" fill="currentColor"/>
                            
                            <!-- Finder Pattern Bottom Left -->
                            <rect x="5" y="70" width="25" height="25" rx="4" fill="currentColor"/>
                            <rect x="10" y="75" width="15" height="15" fill="white"/>
                            <rect x="13" y="78" width="9" height="9" rx="2" fill="currentColor"/>
                            
                            <!-- Alignment Pattern -->
                            <rect x="70" y="70" width="9" height="9" rx="1.5" fill="currentColor"/>
                            
                            <!-- QR Data Matrix Blocks (Simulated) -->
                            <rect x="35" y="5" width="5" height="10" fill="currentColor"/>
                            <rect x="45" y="5" width="10" height="5" fill="currentColor"/>
                            <rect x="60" y="10" width="5" height="15" fill="currentColor"/>
                            
                            <rect x="35" y="20" width="15" height="5" fill="currentColor"/>
                            <rect x="55" y="20" width="5" height="5" fill="currentColor"/>
                            
                            <rect x="5" y="35" width="10" height="5" fill="currentColor"/>
                            <rect x="20" y="35" width="5" height="10" fill="currentColor"/>
                            <rect x="30" y="30" width="15" height="5" fill="currentColor"/>
                            <rect x="50" y="30" width="5" height="15" fill="currentColor"/>
                            <rect x="60" y="35" width="10" height="5" fill="currentColor"/>
                            <rect x="75" y="35" width="5" height="15" fill="currentColor"/>
                            <rect x="85" y="30" width="10" height="5" fill="currentColor"/>
                            
                            <rect x="10" y="50" width="5" height="15" fill="currentColor"/>
                            <rect x="25" y="50" width="15" height="5" fill="currentColor"/>
                            <rect x="45" y="45" width="10" height="10" fill="currentColor"/>
                            <rect x="60" y="50" width="5" height="5" fill="currentColor"/>
                            <rect x="70" y="45" width="10" height="5" fill="currentColor"/>
                            <rect x="85" y="50" width="5" height="10" fill="currentColor"/>
                            
                            <rect x="35" y="60" width="5" height="15" fill="currentColor"/>
                            <rect x="45" y="65" width="15" height="5" fill="currentColor"/>
                            <rect x="65" y="60" width="10" height="5" fill="currentColor"/>
                            <rect x="80" y="65" width="15" height="5" fill="currentColor"/>
                            
                            <rect x="35" y="80" width="15" height="5" fill="currentColor"/>
                            <rect x="55" y="75" width="5" height="10" fill="currentColor"/>
                            <rect x="65" y="85" width="15" height="5" fill="currentColor"/>
                            <rect x="85" y="80" width="10" height="10" fill="currentColor"/>
                        </svg>
                    </div>

                    <div>
                        <div class="asset-code">{{ $asset->asset_code }}</div>
                        <div class="scan-instruction">Scan QR Code untuk melaporkan kerusakan sarpras</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Auto Print trigger -->
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
