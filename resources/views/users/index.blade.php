<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            title="Kelola Pengguna" 
            subtitle="Daftar seluruh akun pengguna sistem SISTA dengan manajemen peran, lembar akun rombel, dan reset kata sandi."
        >
            <x-slot name="actions">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('student-accounts.index') }}" class="inline-flex items-center px-3.5 py-2.5 bg-gray-800 hover:bg-gray-700 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-wider transition-all shadow-xs">
                        Akun Siswa per Rombel
                    </a>
                    <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2.5 bg-primary-600 hover:bg-primary-700 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-wider transition-all shadow-xs">
                        + Tambah Pengguna
                    </a>
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6">
        <div class="w-full sm:px-6 lg:px-8 space-y-4">
            @if (session('reset_credentials'))
                @php
                    $cred = session('reset_credentials');
                @endphp
                <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                    <p class="font-bold">Kata Sandi Sementara Baru Diterbitkan</p>
                    <p class="mt-1">
                        Pengguna: <strong>{{ $cred['name'] }}</strong> &bull;
                        ID Login: <code class="font-mono font-bold">{{ $cred['login_identifier'] }}</code> &bull;
                        Password Sementara: <code class="font-mono font-bold">{{ $cred['temp_password'] }}</code>
                    </p>
                    <p class="text-xs text-amber-700 mt-1">Salin dan berikan langsung kepada pengguna; sistem mewajibkan penggantian sandi saat login pertama.</p>
                </div>
            @endif

            @if (session('batch_credentials_token'))
                <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm text-emerald-900 flex items-center justify-between gap-4">
                    <div>
                        <p class="font-bold">Akun Wali Baru Berhasil Dibuat</p>
                        <p class="text-xs text-emerald-700">Unduh lembar kredensial PDF untuk dibagikan kepada orang tua/wali terkait.</p>
                    </div>
                    <a href="{{ route('users.import-credentials.download', session('batch_credentials_token')) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase rounded-lg">
                        Unduh PDF Kredensial
                    </a>
                </div>
            @endif

            @php
                $canOperateK5 = auth()->user()->role === 'superadmin' || array_intersect(auth()->user()->effectiveRoles(), ['admin', 'staf_tu']) !== [];
                $provisioningService = app(\App\Services\Auth\AccountProvisioningService::class);
            @endphp

            @if ($canOperateK5)
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-4 flex flex-wrap items-center justify-between gap-4">
                    <form method="POST" action="{{ route('users.bulk-provision-parents') }}" class="flex items-center gap-3">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase rounded-lg">
                            Buat Akun Ortu Massal (SPMB)
                        </button>
                        <span class="text-xs text-gray-500 dark:text-slate-400">Menautkan &amp; membuat 1 akun orang tua per nomor HP yang belum tertaut.</span>
                    </form>

                    @if (isset($classrooms) && $classrooms->isNotEmpty())
                        <div class="flex items-center gap-2" x-data="{ clsId: '{{ $classrooms->first()->id }}' }">
                            <select x-model="clsId" class="py-2 px-3 border border-gray-300 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 rounded-lg text-xs text-gray-700 dark:text-slate-300">
                                @foreach ($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}">{{ $classroom->name }} ({{ $classroom->academic_year }})</option>
                                @endforeach
                            </select>
                            <a :href="'{{ url('classrooms') }}/' + clsId + '/credentials-pdf'" class="inline-flex items-center px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold uppercase rounded-lg">
                                Cetak Lembar Akun Rombel
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <x-data-table 
                :headers="[
                    ['label' => 'Nama Lengkap'],
                    ['label' => 'Email / No. HP'],
                    ['label' => 'Peran (Role)'],
                    ['label' => 'Aksi', 'align' => 'right']
                ]"
                :items="$users"
                emptyIcon="search"
                emptyMessage="Tidak ada pengguna ditemukan"
                emptySubMessage="Coba sesuaikan kata kunci pencarian atau filter peran Anda."
                emptyActionUrl="{{ route('users.create') }}"
                emptyActionText="Tambah Pengguna"
            >
                <x-slot name="search">
                    <form action="{{ route('users.index') }}" method="GET" class="flex items-center gap-3">
                        @if(request('role'))
                            <input type="hidden" name="role" value="{{ request('role') }}">
                        @endif
                        <div class="relative w-72 md:w-96">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" name="search" id="global-search" value="{{ request('search') }}" 
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-slate-700 rounded-lg leading-5 bg-gray-50 dark:bg-slate-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 sm:text-sm transition duration-150 ease-in-out" 
                                placeholder="Cari Nama, Email, atau No. HP...">
                        </div>
                    </form>
                </x-slot>

                <x-slot name="actions">
                    <form action="{{ route('users.index') }}" method="GET" class="flex items-center gap-2">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        <select name="role" class="block w-40 md:w-48 py-2 px-3 border border-gray-300 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 rounded-lg focus:outline-none focus:ring-primary-500 sm:text-sm text-gray-700 dark:text-slate-300">
                            <option value="">Semua Peran</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                            <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Siswa</option>
                            <option value="parent" {{ request('role') == 'parent' ? 'selected' : '' }}>Wali Murid</option>
                            <option value="staf_tu" {{ request('role') == 'staf_tu' ? 'selected' : '' }}>Staf TU</option>
                        </select>

                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-slate-700 hover:bg-gray-700 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-wider transition-colors">
                            Filter
                        </button>
                        
                        @if(request()->hasAny(['search', 'role']))
                            <a href="{{ route('users.index') }}" class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 font-bold uppercase underline ml-2">Reset</a>
                        @endif
                    </form>
                </x-slot>

                @foreach ($users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="py-4 px-6">
                            <div class="flex items-center">
                                <div class="h-10 w-10 shrink-0 bg-primary-100 dark:bg-primary-950/60 rounded-full flex items-center justify-center border border-primary-200/50 dark:border-primary-900/40">
                                    <span class="text-primary-700 dark:text-primary-300 font-bold text-sm">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $user->name }}</div>
                                    @if ($user->must_change_password)
                                        <span class="text-[11px] font-medium text-amber-600">Wajib ganti sandi awal</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-sm text-gray-600 dark:text-slate-300">
                            <div>{{ $user->email ?: '-' }}</div>
                            @if ($user->phone_number)
                                <div class="text-xs font-mono text-gray-500">{{ $user->phone_number }}</div>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-sm">
                            <x-status-badge :status="$user->role" />
                        </td>
                        <td class="py-4 px-6 text-sm font-medium text-right flex items-center justify-end space-x-3">
                            @if(auth()->user()->isAdmin() && $user->id !== auth()->id() && !$user->isAdmin())
                                <a href="{{ route('admin.impersonate.take', $user->id) }}" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-900 bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1.5 rounded-lg transition-all flex items-center text-xs font-bold" title="Login Sebagai User Ini">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                    Masuk
                                </a>
                            @endif

                            @if($provisioningService->canResetPasswordFor(auth()->user(), $user))
                                <form method="POST" action="{{ route('users.reset-password', $user->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-amber-600 dark:text-amber-400 hover:underline">Reset Sandi</button>
                                </form>
                            @endif

                            <a href="{{ route('users.edit', $user->id) }}" class="text-primary-600 dark:text-primary-400 hover:underline">Edit</a>
                            
                            @if($user->id !== auth()->id())
                                <button type="button" x-data x-on:click="$dispatch('open-modal', 'confirm-delete-{{ $user->id }}')" class="text-red-600 dark:text-red-400 hover:underline">Hapus</button>
                                <x-confirm-modal 
                                    name="confirm-delete-{{ $user->id }}" 
                                    title="Hapus Akun Pengguna?" 
                                    type="danger" 
                                    :action="route('users.destroy', $user->id)" 
                                    method="DELETE"
                                    submitText="Ya, Hapus"
                                >
                                    Apakah Anda yakin ingin menghapus akun <strong>{{ $user->name }}</strong>? Tindakan ini akan menghapus semua data terkait secara permanen.
                                </x-confirm-modal>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
    </div>
</x-app-layout>
