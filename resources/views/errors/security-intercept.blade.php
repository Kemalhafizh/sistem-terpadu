<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SECURITY ALERT - ACCESS TERMINATED | SISTA Cyber Command</title>
    <x-vendor.script lib="tailwind-browser" />
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700;800&family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        .scanline {
            background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,0) 50%, rgba(0, 0, 0, 0.3) 50%, rgba(0, 0, 0, 0.3));
            background-size: 100% 4px;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 relative overflow-hidden select-none">
    
    <!-- Background Warning Glow & Grid -->
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-red-950/40 via-slate-950 to-slate-950 pointer-events-none"></div>
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#1f293715_1px,transparent_1px),linear-gradient(to_bottom,#1f293715_1px,transparent_1px)] bg-[size:4rem_4rem] pointer-events-none"></div>
    <div class="absolute inset-0 scanline opacity-30 pointer-events-none"></div>

    <div class="max-w-2xl w-full bg-slate-900/90 backdrop-blur-xl border border-red-500/30 rounded-3xl p-8 md:p-10 shadow-2xl shadow-red-950/50 relative z-10">
        
        <!-- Header Shield Icon & Status -->
        <div class="flex items-center gap-4 border-b border-red-500/20 pb-6 mb-8">
            <div class="w-14 h-14 rounded-2xl bg-red-500/10 border border-red-500/40 flex items-center justify-center text-red-500 shadow-lg shadow-red-500/20">
                <svg class="w-8 h-8 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/30 uppercase tracking-widest mono">
                    ACCESS TERMINATED
                </span>
                <h1 class="text-xl md:text-2xl font-black text-white tracking-tight mt-1">
                    CYBER THREAT INTERCEPTION NOTICE
                </h1>
            </div>
        </div>

        <!-- Threat Detail Table -->
        <div class="space-y-4 mb-8">
            <p class="text-slate-300 text-sm leading-relaxed">
                Your connection was automatically intercepted due to unauthorized signature scanning activity or decoy canary endpoint access attempt.
            </p>

            <div class="bg-slate-950/80 border border-slate-800 rounded-2xl p-5 font-mono text-xs space-y-3 shadow-inner">
                <div class="flex justify-between border-b border-slate-800/80 pb-2">
                    <span class="text-slate-500">INCIDENT ID</span>
                    <span class="text-red-400 font-bold tracking-wider">{{ $incidentId ?? 'SEC-SA1-GLOBAL-TRAP' }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-800/80 pb-2">
                    <span class="text-slate-500">CLIENT IP ADDRESS</span>
                    <span class="text-amber-400 font-bold">{{ $ip ?? request()->ip() }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-800/80 pb-2">
                    <span class="text-slate-500">THREAT REASON</span>
                    <span class="text-slate-300">{{ $reason ?? 'Honeypot Canary Trap Triggered' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">TIMESTAMP</span>
                    <span class="text-slate-400">{{ now()->toIso8601String() }}</span>
                </div>
            </div>
        </div>

        <!-- Warning Disclaimer -->
        <div class="bg-red-950/20 border border-red-500/20 rounded-2xl p-4 text-xs text-red-300/90 leading-relaxed mb-8 flex gap-3">
            <span class="text-lg"><svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg></span>
            <div>
                <strong class="text-red-400 font-bold block mb-1">LEGAL NOTICE & THREAT LOGGING</strong>
                This incident, device fingerprint, and network connection parameters have been cryptographically signed and stored in the SISTA Security Operations Center (SOC) audit vault. Unauthorized system access or scanning is prohibited under Law No. 11/2008 (ITE) Article 30.
            </div>
        </div>

        <!-- Portal Return Link -->
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('home') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition-all border border-slate-700 inline-flex items-center gap-2">
                &larr; Return to Public Portal
            </a>
            <span class="text-[10px] text-slate-500 mono">SISTA CYBER COMMAND v3.5</span>
        </div>

    </div>
</body>
</html>
