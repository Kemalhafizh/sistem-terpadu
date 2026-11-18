<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Katalog Jabatan & Penetapan Pejabat') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('success'))
            <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    {{ __('Daftar Jabatan Sekolah') }}
                </h3>
                <div class="space-y-4">
                    @foreach ($positions as $position)
                        <div class="border dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $position->name }}</span>
                                    <span class="text-xs text-gray-500 ml-2">({{ $position->code }})</span>
                                    @if ($position->is_system)
                                        <span class="ml-2 px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-800">Sistem</span>
                                    @endif
                                    @if (! $position->is_active)
                                        <span class="ml-2 px-2 py-0.5 text-xs rounded bg-gray-200 text-gray-700">Nonaktif</span>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-600 dark:text-gray-400">
                                    {{ $position->assignments->count() }} pejabat aktif
                                </span>
                            </div>
                            @if ($position->description)
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $position->description }}</p>
                            @endif
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach ($position->permissions as $perm)
                                    <span class="px-2 py-0.5 text-xs rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        {{ $perm->permission }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    {{ __('Penugasan Pejabat Aktif & Riwayat SK') }}
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left">Pejabat</th>
                                <th class="px-3 py-2 text-left">Jabatan</th>
                                <th class="px-3 py-2 text-left">Periode</th>
                                <th class="px-3 py-2 text-left">No. SK</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($assignments as $assignment)
                                <tr>
                                    <td class="px-3 py-2">{{ $assignment->user?->name ?? '-' }}</td>
                                    <td class="px-3 py-2">
                                        {{ $assignment->position?->name ?? '-' }}
                                        @if ($assignment->is_acting)
                                            <span class="ml-1 px-1.5 py-0.5 text-xs rounded bg-amber-100 text-amber-800">Plt</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ $assignment->starts_on?->format('Y-m-d') }}
                                        —
                                        {{ $assignment->ends_on?->format('Y-m-d') ?? 'Aktif' }}
                                    </td>
                                    <td class="px-3 py-2">{{ $assignment->decree_number ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-gray-500">
                                        Belum ada penugasan pejabat tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
