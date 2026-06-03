<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-black text-3xl text-gray-900 tracking-tight">Portal Kepegawaian</h2>
                <p class="text-sm text-gray-500 font-medium mt-1">Presensi berbasis GPS, pengajuan izin cuti, dan histori slip gaji bulanan Anda.</p>
            </div>
            <div class="mt-4 md:mt-0 bg-primary-50 border border-primary-100 rounded-2xl px-6 py-3 flex items-center">
                <div class="w-2.5 h-2.5 bg-primary-500 rounded-full mr-2.5 animate-pulse"></div>
                <span class="text-xs font-black text-primary-800 uppercase tracking-widest">{{ $employee->employee_number }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-6 py-4 rounded-xl flex items-center shadow-sm">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl flex items-center shadow-sm">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Kiri: Presensi Hari Ini (GPS + Selfie) -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 text-center sticky top-8 space-y-6">
                        <div>
                            <div class="w-20 h-20 mx-auto bg-gray-900 text-white rounded-3xl flex items-center justify-center font-black text-3xl mb-4 shadow-md">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <h3 class="font-black text-xl text-gray-900 mb-0.5">{{ auth()->user()->name }}</h3>
                            <p class="text-xs font-bold text-primary-600 uppercase tracking-widest">{{ $employee->position }}</p>
                        </div>

                        <div class="border-t border-gray-100 pt-6 space-y-4">
                            <p class="text-sm font-bold text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</p>
                            <h4 class="text-4xl font-black text-gray-900 font-mono tracking-tight" id="clock">00:00:00</h4>

                            <!-- GPS Status Card -->
                            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 text-left space-y-1 shadow-sm" id="gps-status-card">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2.5 h-2.5 bg-amber-500 rounded-full animate-ping" id="gps-indicator"></div>
                                    <p class="text-xs font-black text-gray-700" id="gps-status-text">Mendeteksi lokasi GPS...</p>
                                </div>
                                <p class="text-[10px] text-gray-500 font-medium" id="gps-accuracy-text">Mencari sinyal GPS terbaik...</p>
                            </div>

                            @if(!$todayAttendance || !$todayAttendance->check_in_time)
                                <form action="{{ route('my-hrd.attendance') }}" method="POST" enctype="multipart/form-data" id="attendance-form" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="type" value="in">
                                    <input type="hidden" name="lat" id="lat_in">
                                    <input type="hidden" name="long" id="long_in">

                                    <!-- Camera Selfie Section -->
                                    <div class="mb-4 text-left bg-gray-50 border border-gray-200 rounded-2xl p-4">
                                        <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-3">Ambil Foto Selfie</label>
                                        <div class="flex items-center space-x-4">
                                            <label for="photo-capture" class="cursor-pointer px-4 py-3 bg-gray-900 text-white rounded-xl text-xs font-bold hover:bg-black transition-colors flex items-center shadow-md">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                KAMERA
                                            </label>
                                            <input type="file" name="photo" id="photo-capture" accept="image/*" capture="user" required class="hidden" onchange="previewSelfie(this)">
                                            
                                            <div id="selfie-preview-container" class="hidden">
                                                <img id="selfie-preview" class="w-14 h-14 rounded-xl object-cover border-2 border-emerald-500 shadow-inner">
                                            </div>
                                            <span class="text-xs text-gray-400 font-medium" id="photo-status">Belum ada foto</span>
                                        </div>
                                    </div>

                                    <button type="submit" id="submit-btn" disabled class="w-full py-4 bg-emerald-500 text-white rounded-2xl font-black text-lg shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 disabled:bg-gray-300 disabled:shadow-none disabled:cursor-not-allowed transition-all flex items-center justify-center">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                        CHECK IN SEKARANG
                                    </button>
                                </form>
                                <p class="text-[11px] text-gray-400 mt-2">Pastikan Anda berada di area sekolah dan telah mengambil foto selfie.</p>
                            @elseif($todayAttendance && !$todayAttendance->check_out_time)
                                <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 space-y-1">
                                    <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Check-In Berhasil</p>
                                    <p class="text-xl font-black text-emerald-950 font-mono">{{ $todayAttendance->check_in_time }}</p>
                                    @if($todayAttendance->photo_path)
                                        <div class="mt-2 flex justify-center">
                                            <img src="{{ asset('storage/' . $todayAttendance->photo_path) }}" class="w-16 h-16 rounded-xl object-cover border-2 border-emerald-300">
                                        </div>
                                    @endif
                                </div>
                                <form action="{{ route('my-hrd.attendance') }}" method="POST" id="attendance-form" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="type" value="out">
                                    <input type="hidden" name="lat" id="lat_out">
                                    <input type="hidden" name="long" id="long_out">
                                    <button type="submit" id="submit-btn" disabled class="w-full py-4 bg-amber-500 text-white rounded-2xl font-black text-lg shadow-lg shadow-amber-500/20 hover:bg-amber-600 disabled:bg-gray-300 disabled:shadow-none disabled:cursor-not-allowed transition-all flex items-center justify-center">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                        CHECK OUT PULANG
                                    </button>
                                </form>
                            @else
                                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 text-center space-y-2">
                                    <div class="flex justify-center">
                                        <div class="bg-gray-100 text-gray-500 w-12 h-12 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                    </div>
                                    <p class="text-sm font-bold text-gray-700">Presensi Selesai</p>
                                    <p class="text-xs text-gray-400 font-medium">Masuk: {{ $todayAttendance->check_in_time }} | Pulang: {{ $todayAttendance->check_out_time }}</p>
                                    @if($todayAttendance->photo_path)
                                        <div class="mt-2 flex justify-center">
                                            <img src="{{ asset('storage/' . $todayAttendance->photo_path) }}" class="w-16 h-16 rounded-xl object-cover border border-gray-200">
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Kanan: Form Pengajuan Cuti, Histori & Slip Gaji -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Form Pengajuan Cuti -->
                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-black text-lg text-gray-900">Pengajuan Izin / Cuti</h3>
                            <p class="text-xs text-gray-500 mt-1">Kirimkan permohonan ketidakhadiran secara terencana di sini.</p>
                        </div>
                        <div class="p-6">
                            <form action="{{ route('my-hrd.leaves') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Jenis Ketidakhadiran</label>
                                        <select name="type" required class="w-full rounded-xl border-gray-200 bg-gray-50 p-3 focus:ring-primary-500 font-medium text-sm">
                                            <option value="sakit">Sakit</option>
                                            <option value="izin">Izin Keperluan Pribadi</option>
                                            <option value="cuti_tahunan">Cuti Tahunan</option>
                                            <option value="cuti_melahirkan">Cuti Melahirkan</option>
                                            <option value="umroh_haji">Umroh / Haji</option>
                                            <option value="dinas_luar">Dinas Luar</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Lampiran Bukti (Opsional)</label>
                                        <input type="file" name="attachment" accept=".pdf,.jpg,.png" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 border border-gray-200 rounded-xl">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Tanggal Mulai</label>
                                        <input type="date" name="start_date" required min="{{ date('Y-m-d') }}" class="w-full rounded-xl border-gray-200 bg-gray-50 p-3 focus:ring-primary-500 font-medium text-sm text-gray-600">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Tanggal Berakhir</label>
                                        <input type="date" name="end_date" required min="{{ date('Y-m-d') }}" class="w-full rounded-xl border-gray-200 bg-gray-50 p-3 focus:ring-primary-500 font-medium text-sm text-gray-600">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Alasan Detail</label>
                                        <textarea name="reason" rows="2" required placeholder="Jelaskan secara singkat alasan ketidakhadiran Anda..." class="w-full rounded-xl border-gray-200 bg-gray-50 p-3 focus:ring-primary-500 font-medium text-sm"></textarea>
                                    </div>
                                </div>
                                <div class="pt-2 flex justify-end">
                                    <button type="submit" class="px-6 py-3 bg-gray-900 text-white rounded-xl font-bold text-sm shadow-md hover:bg-black transition-colors">AJUKAN SEKARANG</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Riwayat Singkat (Absensi & Cuti) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- List Absensi -->
                        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-5 border-b border-gray-100 bg-gray-50/20">
                                <h3 class="font-black text-gray-900 text-base">Histori Absen (5 Hari Terakhir)</h3>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @forelse($recentAttendances ?? [] as $att)
                                    <div class="p-4 flex justify-between items-center hover:bg-gray-50 transition-colors">
                                        <div>
                                            <p class="font-bold text-gray-900 text-sm">{{ $att->date?->translatedFormat('d M Y') }}</p>
                                            <p class="text-xs text-gray-400 font-medium">
                                                Masuk: {{ $att->check_in_time ?? '--:--' }} 
                                                @if($att->distance_meters)
                                                    (±{{ Math.round($att->distance_meters) }}m)
                                                @endif
                                            </p>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider
                                            @if($att->status == 'hadir') bg-emerald-50 text-emerald-700 border border-emerald-100
                                            @elseif($att->status == 'terlambat') bg-amber-50 text-amber-700 border border-amber-100
                                            @elseif($att->status == 'alfa') bg-red-50 text-red-700 border border-red-100
                                            @else bg-blue-50 text-blue-700 border border-blue-100 @endif">
                                            {{ $att->status }}
                                        </span>
                                    </div>
                                @empty
                                    <p class="p-4 text-center text-sm text-gray-400 font-medium">Belum ada data kehadiran.</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- List Cuti -->
                        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-5 border-b border-gray-100 bg-gray-50/20">
                                <h3 class="font-black text-gray-900 text-base">Pengajuan Cuti Saya</h3>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @forelse($recentLeaves ?? [] as $leave)
                                    <div class="p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex justify-between items-center mb-1">
                                            <p class="font-bold text-gray-900 text-sm">{{ strtoupper(str_replace('_', ' ', $leave->type)) }}</p>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider
                                                @if($leave->status == 'approved') bg-emerald-100 text-emerald-700
                                                @elseif($leave->status == 'pending') bg-amber-100 text-amber-700
                                                @else bg-red-100 text-red-700 @endif">
                                                {{ $leave->status }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-gray-400 font-semibold">{{ $leave->start_date?->format('d M') }} - {{ $leave->end_date?->format('d M Y') }}</p>
                                    </div>
                                @empty
                                    <p class="p-4 text-center text-sm text-gray-400 font-medium">Belum ada pengajuan izin/cuti.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Slip Gaji Bulanan Karyawan -->
                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-black text-lg text-gray-900">Histori Slip Gaji Saya</h3>
                            <p class="text-xs text-gray-500 mt-1">Unduh slip gaji resmi bulanan yang telah diterbitkan oleh bendahara sekolah.</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">
                                        <th class="p-4 pl-6">Periode</th>
                                        <th class="p-4">Gaji Pokok</th>
                                        <th class="p-4">Tunjangan</th>
                                        <th class="p-4">Potongan</th>
                                        <th class="p-4">Gaji Bersih</th>
                                        <th class="p-4">Status</th>
                                        <th class="p-4 pr-6 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($payslips ?? [] as $pay)
                                        <tr class="hover:bg-gray-50/50 transition-colors text-sm font-medium text-gray-700">
                                            <td class="p-4 pl-6 font-bold text-gray-900">
                                                {{ $pay->month_name }} {{ $pay->year }}
                                            </td>
                                            <td class="p-4 font-mono text-xs">{{ $pay->base_salary_formatted }}</td>
                                            <td class="p-4 font-mono text-xs text-emerald-600">+{{ $pay->allowances_formatted }}</td>
                                            <td class="p-4 font-mono text-xs text-red-500">-{{ $pay->deductions_formatted }}</td>
                                            <td class="p-4 font-bold text-gray-900 font-mono text-xs">{{ $pay->net_salary_formatted }}</td>
                                            <td class="p-4">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider
                                                    @if($pay->status == 'paid') bg-emerald-50 text-emerald-700 border border-emerald-100
                                                    @else bg-blue-50 text-blue-700 border border-blue-100 @endif">
                                                    {{ $pay->status }}
                                                </span>
                                            </td>
                                            <td class="p-4 pr-6 text-right">
                                                <a href="{{ route('my-hrd.payslips.pdf', $pay->id) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-bold hover:bg-black transition-colors">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                    PDF
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="p-6 text-center text-sm text-gray-400 font-medium">Belum ada slip gaji bulanan yang diterbitkan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Clock and Geofencing Validation Scripts -->
    <script>
        // Update live clock
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour12: false });
            document.getElementById('clock').textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Image Preview Handler
        function previewSelfie(input) {
            const previewContainer = document.getElementById('selfie-preview-container');
            const previewImg = document.getElementById('selfie-preview');
            const photoStatus = document.getElementById('photo-status');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                    photoStatus.textContent = "Selfie siap!";
                    photoStatus.className = "text-xs text-emerald-600 font-bold";
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Live Geofencing calculations
        let isLocationReady = false;

        function initGeolocation() {
            const statusText = document.getElementById('gps-status-text');
            const accuracyText = document.getElementById('gps-accuracy-text');
            const indicator = document.getElementById('gps-indicator');
            const submitBtn = document.getElementById('submit-btn');

            if (!navigator.geolocation) {
                statusText.textContent = "Browser tidak mendukung GPS";
                statusText.className = "text-xs font-black text-red-600";
                indicator.className = "w-2.5 h-2.5 bg-red-600 rounded-full";
                return;
            }

            navigator.geolocation.watchPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const accuracy = position.coords.accuracy;

                    // Set coordinates to check-in fields
                    if (document.getElementById('lat_in')) {
                        document.getElementById('lat_in').value = lat;
                        document.getElementById('long_in').value = lng;
                    }

                    // Set coordinates to check-out fields
                    if (document.getElementById('lat_out')) {
                        document.getElementById('lat_out').value = lat;
                        document.getElementById('long_out').value = lng;
                    }

                    // School coordinates & configuration
                    const schoolLat = parseFloat("{{ $schoolLat }}");
                    const schoolLong = parseFloat("{{ $schoolLong }}");
                    const schoolRadius = parseFloat("{{ $schoolRadius }}");

                    // Distance using JS
                    const distance = calculateDistanceJS(lat, lng, schoolLat, schoolLong);

                    accuracyText.textContent = `Akurasi Sinyal GPS: ±${Math.round(accuracy)} meter`;

                    if (distance <= schoolRadius) {
                        statusText.textContent = `Jangkauan Cocok (±${Math.round(distance)}m dari sekolah)`;
                        statusText.className = "text-xs font-black text-emerald-600";
                        indicator.className = "w-2.5 h-2.5 bg-emerald-500 rounded-full";
                        
                        // Enable check-in / check-out buttons
                        isLocationReady = true;
                        submitBtn.disabled = false;
                    } else {
                        statusText.textContent = `Di Luar Area (±${Math.round(distance)}m dari sekolah)`;
                        statusText.className = "text-xs font-black text-red-600";
                        indicator.className = "w-2.5 h-2.5 bg-red-500 rounded-full";
                        
                        // Disable buttons
                        isLocationReady = false;
                        submitBtn.disabled = true;
                    }
                },
                (error) => {
                    statusText.textContent = "GPS tidak aktif / diblokir: " + error.message;
                    statusText.className = "text-xs font-black text-red-600";
                    indicator.className = "w-2.5 h-2.5 bg-red-500 rounded-full";
                    submitBtn.disabled = true;
                },
                { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 }
            );
        }

        // Haversine formula inside JS
        function calculateDistanceJS(lat1, lon1, lat2, lon2) {
            const R = 6371000; // in meters
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        window.addEventListener('load', initGeolocation);
    </script>
</x-app-layout>
