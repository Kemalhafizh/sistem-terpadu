<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Manajer Lokalisasi & Bahasa (Translation Manager)') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola string terjemahan dinamis (Bahasa Indonesia, English, العربية RTL) secara real-time.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        <!-- Translation DataGrid -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-400 font-semibold">
                            <th class="p-3">Grup</th>
                            <th class="p-3">Kunci (Key)</th>
                            <th class="p-3">Bahasa Indonesia (ID)</th>
                            <th class="p-3">English (EN)</th>
                            <th class="p-3 text-right">العربية (AR - RTL)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($strings as $str)
                            <tr>
                                <td class="p-3 font-mono font-bold text-slate-400">{{ $str->group_name }}</td>
                                <td class="p-3 font-mono text-emerald-600 dark:text-emerald-400 font-bold">{{ $str->string_key }}</td>
                                <td class="p-3 text-slate-800 dark:text-white">{{ $str->text_id }}</td>
                                <td class="p-3 text-slate-600 dark:text-slate-300">{{ $str->text_en ?? '-' }}</td>
                                <td class="p-3 text-right text-slate-800 dark:text-white font-arabic text-sm" dir="rtl">{{ $str->text_ar ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
