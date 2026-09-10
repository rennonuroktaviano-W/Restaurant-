@if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
        {{ session('success') }}
    </div>
@endif

@if (session('status'))
    <div class="mb-4 rounded-lg border border-brand-500/40 bg-brand-500/10 px-4 py-3 text-sm text-brand-200">
        {{ session('status') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
        <ul class="list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif