<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Akun Siswa &amp; Orang Tua Rombel"
            subtitle="Pencetakan lembar akun awal per rombel dan reset kata sandi sementara tanpa Google Workspace."
        />
    </x-slot>

    <div class="py-6">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">
            @if (session('reset_credentials'))
                @php
                    $cred = session('reset_credentials');
                @endphp
                <div class="rounded-xl border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-950/40 p-4 text-sm text-amber-900 dark:text-amber-200">
                    <p class="font-bold">Password Sementara Baru Diterbitkan</p>
                    <p class="mt-1">
                        Akun: <strong>{{ $cred['name'] }}</strong> ({{ $cred['role'] }}) &bull;
                        ID Login: <code class="font-mono font-bold">{{ $cred['login_identifier'] }}</code> &bull;
                        Password Sementara: <code class="font-mono font-bold">{{ $cred['temp_password'] }}</code>
                    </p>
                    <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">Salin segera karena password sementara ini tidak disimpan ulang dalam bentuk teks biasa.</p>
                </div>
            @endif

            @if (session('batch_credentials_token'))
                <div class="rounded-xl border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 p-4 text-sm text-emerald-900 dark:text-emerald-200 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="font-bold">Lembar Kredensial Batch Siap Diunduh</p>
                        <p class="text-xs text-emerald-700 dark:text-emerald-300">Unduh PDF kredensial untuk akun wali yang baru dibuat pada operasi ini.</p>
                    </div>
                    <a href="{{ route('users.import-credentials.download', session('batch_credentials_token')) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase rounded-lg transition-colors">
                        Unduh PDF Kredensial
                    </a>
                </div>
            @endif

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex flex-wrap items-end justify-between gap-4">
                <form method="GET" action="{{ route('student-accounts.index') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="classroom_id" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Pilih Rombel</label>
                        <select id="classroom_id" name="classroom_id" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                            @forelse ($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" @selected($selectedClassroom && $selectedClassroom->id === $classroom->id)>
                                    {{ $classroom->name }} ({{ $classroom->academic_year }})
                                </option>
                            @empty
                                <option value="">Tidak ada rombel perwalian</option>
                            @endforelse
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-slate-800 dark:bg-slate-700 hover:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-bold uppercase rounded-lg transition-colors">
                        Tampilkan
                    </button>
                </form>

                <div class="flex flex-wrap items-center gap-3">
                    @if ($selectedClassroom)
                        <a href="{{ route('classrooms.credentials-pdf', $selectedClassroom->id) }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold uppercase rounded-lg transition-colors">
                            Cetak Lembar Akun Rombel (PDF)
                        </a>
                    @endif

                    @if ($canBulkProvision)
                        <form method="POST" action="{{ route('users.bulk-provision-parents') }}" onsubmit="return confirm('Buat akun orang tua/wali untuk seluruh siswa di rombel ini yang belum memiliki akun tertaut?')">
                            @csrf
                            @if ($selectedClassroom)
                                <input type="hidden" name="classroom_id" value="{{ $selectedClassroom->id }}">
                            @endif
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase rounded-lg transition-colors">
                                Buat Akun Ortu Massal
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-x-auto">
                <table class="w-full text-start border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase text-slate-500 dark:text-slate-400">
                            <th class="py-3 px-4 text-start">Siswa</th>
                            <th class="py-3 px-4 text-start">NIS / NISN</th>
                            <th class="py-3 px-4 text-start">Status Akun Siswa</th>
                            <th class="py-3 px-4 text-start">Orang Tua / Wali</th>
                            <th class="py-3 px-4 text-end">Aksi Reset</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($students as $student)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">{{ $student->user?->name ?? '-' }}</td>
                                <td class="py-3 px-4 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $student->nis ?: '-' }} / {{ $student->nisn ?: '-' }}</td>
                                <td class="py-3 px-4 text-xs">
                                    @if ($student->user)
                                        @if ($student->user->must_change_password)
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">
                                                Menunggu aktivasi awal
                                            </span>
                                        @else
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                                                Sudah aktif
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">
                                            Belum punya akun
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    @if ($student->parent)
                                        <div class="font-semibold text-slate-900 dark:text-white">{{ $student->parent->name }}</div>
                                        <div class="font-mono text-slate-500 dark:text-slate-400">{{ $student->parent->phone_number ?: $student->parent->email }}</div>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500">Belum tertaut</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                        @if ($student->user)
                                            <form method="POST" action="{{ route('users.reset-password', $student->user->id) }}" onsubmit="return confirm('Reset kata sandi akun siswa ini dan terbitkan kata sandi sementara baru?')">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg transition-colors">
                                                    Reset Sandi Siswa
                                                </button>
                                            </form>
                                        @endif
                                        @if ($canBulkProvision && $student->parent)
                                            <form method="POST" action="{{ route('users.reset-password', $student->parent->id) }}" onsubmit="return confirm('Reset kata sandi akun orang tua/wali ini dan terbitkan kata sandi sementara baru?')">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold rounded-lg transition-colors">
                                                    Reset Sandi Ortu
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-500 dark:text-slate-400">Tidak ada data siswa pada rombel terpilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
