<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $mandant->name }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <a href="{{ route('mandants.index') }}" class="text-sm text-gray-500 hover:text-gray-800">
                &larr; Tous les mandants
            </a>

            <div class="bg-white rounded-2xl border border-gray-200 p-6 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-xs text-gray-400 uppercase font-semibold">Téléphone</div>
                    <div class="text-gray-800">{{ $mandant->phone ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase font-semibold">Email</div>
                    <div class="text-gray-800">{{ $mandant->email ?? '—' }}</div>
                </div>
            </div>

            @if (Auth::user()->isChiefDirector())
                <form method="POST" action="{{ route('mandants.destroy', $mandant) }}"
                      onsubmit="return confirm({{ Js::from('Ce mandant possède '.$reports->count().' dossier(s). Les dossiers seront conservés, mais ne seront plus rattachés à ce mandant.') }}) && confirm({{ Js::from('Voulez-vous vraiment supprimer définitivement « '.$mandant->name.' » ?') }});">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 text-sm font-semibold hover:underline">
                        Supprimer ce mandant
                    </button>
                </form>
            @endif

            <form method="GET" class="flex items-center gap-2">
                <label class="text-sm text-gray-500">Filtrer par domaine :</label>
                <select name="category" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm">
                    <option value="">Tous</option>
                    @foreach (\App\Models\ReportCategory::orderBy('name')->get() as $cat)
                        <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </form>

            <div>
                <h3 class="font-semibold text-gray-800 mb-3">
                    Dossiers ({{ $reports->count() }})
                </h3>
                @forelse ($reports as $report)
                    <a href="{{ route('reports.show', $report->id) }}"
                       class="flex items-center justify-between bg-white rounded-xl border border-gray-200 px-5 py-4 mb-2 hover:border-gray-300 hover:shadow-sm transition">
                        <div>
                            <div class="font-semibold text-gray-800">{{ $report->title }}</div>
                            <div class="text-xs text-gray-400">{{ $report->type->name }} — {{ $report->created_at->format('d/m/Y') }}</div>
                        </div>
                        <span class="text-gray-400 text-sm">&rarr;</span>
                    </a>
                @empty
                    <p class="text-gray-500 text-sm bg-white rounded-xl border border-gray-200 p-5">
                        Aucun dossier pour ce mandant pour l'instant.
                    </p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>