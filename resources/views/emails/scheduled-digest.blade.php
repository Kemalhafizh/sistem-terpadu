<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $digestName }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-w-2xl: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px border #e2e8f0;
        }
        .header {
            background-color: #312e81; /* Indigo-900 */
            padding: 30px 40px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 11px;
            color: #c7d2fe;
            font-weight: bold;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .content {
            padding: 40px;
            color: #334155;
            line-height: 1.6;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 20px;
            text-align: center;
            font-size: 11px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 5px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div style="background-color: #f8fafc; padding: 20px 0; width: 100%;">
        <div class="container" style="max-width: 600px; margin: 0 auto;">
            <!-- Header -->
            <div class="header">
                <h1>{{ $digestName }}</h1>
                <p>Sistem Informasi Terpadu {{ school_short_name() }}</p>
            </div>
            
            <!-- Content -->
            <div class="content">
                {!! $htmlContent !!}
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p>© {{ date('Y') }} {{ school_name() }}. All Rights Reserved.</p>
                <p style="font-weight: normal; color: #94a3b8; margin-top: 10px;">Email ini dikirimkan secara otomatis oleh sistem reporting terjadwal SISTA. Harap tidak membalas email ini secara langsung.</p>
            </div>
        </div>
    </div>
</body>
</html>
