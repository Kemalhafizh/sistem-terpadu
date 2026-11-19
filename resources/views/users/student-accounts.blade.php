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
                @php($cred = session('reset_credentials'))
                <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                    <p class="font-bold">Password Sementara Baru Diterbitkan</p>
                    <p class="mt-1">
                        Akun: <strong>{{ $cred['name'] }}</strong> ({{ $cred['role'] }}) &bull;
                        ID Login: <code class="font-mono font-bold">{{ $cred['login_identifier'] }}</code> &bull;
                        Password Sementara: <code class="font-mono font-bold">{{ $cred['temp_password'] }}</code>
                    </p>
                    <p class="text-xs text-amber-700 mt-1">Salin segera karena password sementara ini tidak disimpan ulang dalam bentuk teks biasa.</p>
                </div>
            @endif

            @if (session('batch_credentials_token'))
                <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm text-emerald-900 flex items-center justify-between gap-4">
                    <div>
                        <p class="font-bold">Lembar Kredensial Batch Siap Diunduh</p>
                        <p class="text-xs text-emerald-700">Unduh PDF kredensial untuk akun wali yang baru dibuat pada operasi ini.</p>
                    </div>
                    <a href="{{ route('users.import-credentials.download', session('batch_credentials_token')) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase rounded-lg">
                        Unduh PDF Kredensial
                    </a>
                </div>
            @endif

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5 flex flex-wrap items-end justify-between gap-4">
                <form method="GET" action="{{ route('student-accounts.index') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="classroom_id" class="block text-xs font-semibold text-gray-600 dark:text-slate-300 mb-1">Pilih Rombel</label>
                        <select id="classroom_id" name="classroom_id" class="rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 text-sm">
                            @forelse ($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" @selected($selectedClassroom && $selectedClassroom->id === $classroom->id)>
                                    {{ $classroom->name }} ({{ $classroom->academic_year }})
                                </option>
                            @empty
                                <option value="">Tidak ada rombel perwalian</option>
                            @endforelse
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold uppercase rounded-lg">
                        Tampilkan
                    </button>
                </form>

                <div class="flex flex-wrap items-center gap-3">
                    @if ($selectedClassroom)
                        <a href="{{ route('classrooms.credentials-pdf', $selectedClassroom->id) }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold uppercase rounded-lg">
                            Cetak Lembar Akun Rombel (PDF)
                        </a>
                    @endif

                    @if ($canBulkProvision)
                        <form method="POST" action="{{ route('users.bulk-provision-parents') }}">
                            @csrf
                            @if ($selectedClassroom)
                                <input type="hidden" name="classroom_id" value="{{ $selectedClassroom->id }}">
                            @endif
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase rounded-lg">
                                Buat Akun Ortu Massal
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-slate-700 text-xs uppercase text-gray-500">
                            <th class="py-3 px-4">Siswa</th>
                            <th class="py-3 px-4">NIS / NISN</th>
                            <th class="py-3 px-4">Status Akun Siswa</th>
                            <th class="py-3 px-4">Orang Tua / Wali</th>
                            <th class="py-3 px-4 text-right">Aksi Reset</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @forelse ($students as $student)
                            <tr>
                                <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">{{ $student->user?->name ?? '-' }}</td>
                                <td class="py-3 px-4 font-mono text-xs">{{ $student->nis ?: '-' }} / {{ $student->nisn ?: '-' }}</td>
                                <td class="py-3 px-4 text-xs">
                                    @if ($student->user)
                                        {{ $student->user->must_change_password ? 'Menunggu aktivasi awal' : 'Sudah aktif' }}
                                    @else
                                        <span class="text-red-600">Belum punya akun</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    @if ($student->parent)
                                        <div class="font-semibold">{{ $student->parent->name }}</div>
                                        <div class="font-mono text-gray-500">{{ $student->parent->phone_number ?: $student->parent->email }}</div>
                                    @else
                                        <span class="text-gray-400">Belum tertaut</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        @if ($student->user)
                                            <form method="POST" action="{{ route('users.reset-password', $student->user->id) }}">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg">
                                                    Reset Sandi Siswa
                                                </button>
                                            </form>
                                        @endif
                                        @if ($canBulkProvision && $student->parent)
                                            <form method="POST" action="{{ route('users.reset-password', $student->parent->id) }}">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-gray-700 hover:bg-gray-800 text-white text-xs font-bold rounded-lg">
                                                    Reset Sandi Ortu
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-500">Tidak ada data siswa pada rombel terpilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
