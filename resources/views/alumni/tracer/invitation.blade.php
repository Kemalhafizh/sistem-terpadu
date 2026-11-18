<x-app-layout>
    <x-slot name="title">Undangan Pengisian Tracer Study</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Undangan Pengisian Tracer Study
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Kirim undangan pengisian kuesioner ke alumni yang belum mengisi data.
                </p>
            </div>
            <a href="{{ route('alumni.tracer-study.analytics') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅ Kembali
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Filter & Progress Card -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Filter -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex flex-col justify-center">
                <form method="GET" action="{{ route('alumni.tracer-study.invitation') }}" class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pilih Angkatan Alumni</label>
                        <select name="year" onchange="this.form.submit()" class="w-full bg-gray-50/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                            @foreach($graduationYears as $yr)
                                <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>Angkatan {{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <!-- Progress Rate Card -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl lg:col-span-2 space-y-4 flex flex-col justify-center">
                <div class="flex justify-between items-center text-xs">
                    <div>
                        <h4 class="font-extrabold text-gray-800 dark:text-white text-sm"> Angka Partisipasi Angkatan {{ $selectedYear }}</h4>
                        <p class="text-gray-400 mt-0.5">Rasio pengisian kuesioner tracer study.</p>
                    </div>
                    <span class="font-black text-blue-600 dark:text-blue-400 text-lg">{{ $responseRate['percentage'] }}%</span>
                </div>
                <div class="space-y-1">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 h-3 rounded-full overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-full rounded-full" style="width: {{ $responseRate['percentage'] }}%"></div>
                    </div>
                    <div class="flex justify-between text-[10px] text-gray-400 font-bold">
                        <span>{{ $responseRate['total_responses'] }} Mengisi</span>
                        <span>Total {{ $responseRate['total_alumni'] }} Alumni</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Alumni Belum Mengisi -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700/50">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm"> Daftar Alumni Belum Mengisi Kuesioner (Angkatan {{ $selectedYear }})</h3>
                <p class="text-xs text-gray-400 mt-1">Salin draf teks undangan broadcast WhatsApp/Email cepat di bawah ini.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700/50">
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Alumni</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Kontak Terdaftar</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Draf Teks Undangan</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                        @forelse($unresponsiveAlumni as $al)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/10 transition-all">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $al->student->name }}</div>
                                    <p class="text-[10px] text-gray-500 mt-0.5">NISN: {{ $al->student->nisn ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500">
                                    <p class="font-medium">{{ $al->email ?? $al->student->user->email }}</p>
                                    <p>{{ $al->phone_number ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-4 max-w-md">
                                    <p class="text-[11px] text-gray-400 italic line-clamp-1">
                                        Assalamu'alaikum. Halo {{ $al->student->name }}, mohon bantuan Anda mengisi kuesioner Tracer Study SISTA...
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button onclick="copyInviteMessage('{{ addslashes($al->student->name) }}')" class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-1.5 px-3.5 text-xs font-bold transition-all shadow-md">
                                         Salin Pesan
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-8 text-gray-400 italic">
                                    Hebat! Seluruh alumni angkatan {{ $selectedYear }} sudah mengisi kuesioner Tracer Study.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function copyInviteMessage(name) {
            const inviteLink = `${window.location.origin}/alumni/tracer-study`;
            const text = `Assalamu'alaikum wr. wb. Halo ${name}, mohon bantuan Anda untuk meluangkan waktu sejenak mengisi kuesioner Tracer Study alumni ${@js(school_name())} melalui tautan berikut:\n\n ${inviteLink}\n\nMasukan Anda sangat berharga bagi akreditasi dan peningkatan kualitas kurikulum sekolah kita. Terima kasih banyak atas partisipasinya!`;
            
            navigator.clipboard.writeText(text).then(function() {
                alert('Draf pesan WhatsApp untuk ' + name + ' berhasil disalin ke clipboard!');
            }, function() {
                // Fallback
                const tempTextArea = document.createElement('textarea');
                tempTextArea.value = text;
                document.body.appendChild(tempTextArea);
                tempTextArea.select();
                document.execCommand('copy');
                document.body.removeChild(tempTextArea);
                alert('Draf pesan WhatsApp untuk ' + name + ' berhasil disalin ke clipboard!');
            });
        }
    </script>
</x-app-layout>
