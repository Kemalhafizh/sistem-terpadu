@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            Pemetaan Tier Diferensiasi
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ school_name() }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 text-gray-600 dark:text-gray-300">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Tipe Diferensiasi: {{ ucfirst($plan->differentiation_type ?? '') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
                <h3 class="font-bold text-emerald-800">Tier 1 (Kelas)</h3>
                <ul class="list-disc pl-5 mt-2 text-sm text-emerald-700">
                    @foreach($plan->tier_1_strategy ?? [] as $strategy)
                        <li>{{ $strategy }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                <h3 class="font-bold text-amber-800">Tier 2 (Kelompok Kecil)</h3>
                <ul class="list-disc pl-5 mt-2 text-sm text-amber-700">
                    @foreach($plan->tier_2_strategy ?? [] as $strategy)
                        <li>{{ $strategy }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-lg">
                <h3 class="font-bold text-rose-800">Tier 3 (Individu)</h3>
                <ul class="list-disc pl-5 mt-2 text-sm text-rose-700">
                    @foreach($plan->tier_3_strategy ?? [] as $strategy)
                        <li>{{ $strategy }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
