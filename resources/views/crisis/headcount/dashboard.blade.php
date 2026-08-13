@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">Safety Headcount</h1>
        <div class="text-gray-600 dark:text-gray-300">
            @foreach($headcounts as $headcount)
            <div class="mb-4">
                <p>Assembly Point: {{ $headcount->assembly_point }}</p>
                <p>Expected: {{ $headcount->expected_count }} | Accounted: {{ $headcount->accounted_count }} | Missing: {{ $headcount->missing_count }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection