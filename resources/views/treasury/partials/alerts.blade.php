@if(session('success'))
    <div class="mb-4 rounded-md bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-4 rounded-md bg-rose-50 p-4 text-sm text-rose-800">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif
