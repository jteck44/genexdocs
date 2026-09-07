<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Tous les rapports</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher par titre..."
                       class="flex-1 rounded-xl border-gray-300 text-sm py-2.5">

                @if (Auth::user()->isDirector())
                    <label class="flex items-center gap-2 text-sm text-gray-600 whitespace-nowrap">
                        <input type="checkbox" name="sans_mandant" value="1" @checked($onlyOrphans) class="rounded">
                        Sans mandant rattaché
                    </label>
                @endif

                <button type="submit" class="bg-[#16213E] text-white font-semibold px-5 py-2.5 rounded-xl hover:bg-[#0f1730]">
                    Filtrer
                </button>
            </form>

            <div class="space-y-2">
                @forelse ($reports as $report)
                    <div class="flex items-center justify-between bg-white rounded-xl border border-gray-200 px-5 py-4">
                        <a href="{{ route('reports.show', $report->id) }}" class="flex-1">
                            <div class="font-semibold text-gray-800">{{ $report->title }}</div>
                            <div class="text-xs text-gray-400">
                                {{ $report->type->name }}
                                — {{ $report->mandant->name ?? 'Sans mandant' }}
                                — {{ $report->created_at->format('d/m/Y') }}
                            </div>
                        </a>

                        @if (Auth::user()->isDirector())
                            {{-- Le directeur peut supprimer n'importe quel rapport — le message
                                d'avertissement s'adapte selon son statut, pour qu'il sache
                                précisément ce qu'il s'apprête à effacer. --}}
                            <form method="POST" action="{{ route('reports.director-destroy', $report->id) }}"
                                onsubmit="return confirm('Statut actuel : {{ $report->status }}{{ $report->mandant ? ' — Mandant : '.$report->mandant->name : ' — Sans mandant' }}.') && confirm('Voulez-vous vraiment supprimer définitivement « {{ $report->title }} » ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 text-sm hover:underline ml-4">
                                    Supprimer
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 text-sm bg-white rounded-xl border border-gray-200 p-5 text-center">
                        Aucun rapport ne correspond à votre recherche.
                    </p>
                @endforelse
            </div>

            <div>{{ $reports->links() }}</div>

        </div>
    </div>
</x-app-layout>