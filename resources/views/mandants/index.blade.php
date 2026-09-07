<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Mandants</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
                    {{ session('status') }}
                </div>
            @endif

            {{-- --- Barre de recherche --- --}}
            <form method="GET" action="{{ route('mandants.index') }}" class="flex gap-2">
                <input type="text" name="q" value="{{ $search }}"
                       placeholder="Rechercher un mandant par nom..."
                       class="flex-1 rounded-xl border-gray-300 text-sm py-2.5">
                <button type="submit" class="bg-[#16213E] text-white font-semibold px-5 py-2.5 rounded-xl hover:bg-[#0f1730]">
                    Chercher
                </button>
            </form>

            {{-- --- Création rapide --- --}}
            <details class="bg-white rounded-2xl border border-gray-200 p-5">
                <summary class="cursor-pointer font-semibold text-gray-700">+ Ajouter un nouveau mandant</summary>
                <form method="POST" action="{{ route('mandants.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="text" name="name" required placeholder="Nom du mandant"
                           class="w-full rounded-xl border-gray-300 text-sm py-2.5">
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" name="phone" placeholder="Téléphone (optionnel)"
                               class="w-full rounded-xl border-gray-300 text-sm py-2.5">
                        <input type="email" name="email" placeholder="Email (optionnel)"
                               class="w-full rounded-xl border-gray-300 text-sm py-2.5">
                    </div>
                    <button type="submit" class="bg-[#16213E] text-white font-semibold px-5 py-2.5 rounded-xl hover:bg-[#0f1730]">
                        Créer le mandant
                    </button>
                </form>
            </details>

            {{-- --- Liste --- --}}
            <div class="space-y-2">
                @forelse ($mandants as $mandant)
                    <a href="{{ route('mandants.show', $mandant->id) }}"
                       class="flex items-center justify-between bg-white rounded-xl border border-gray-200 px-5 py-4 hover:border-[#16213E]/30 hover:shadow-sm transition">
                        <div>
                            <div class="font-semibold text-gray-800">{{ $mandant->name }}</div>
                            <div class="text-xs text-gray-400">
                                {{ $mandant->reports_count }} {{ Str::plural('dossier', $mandant->reports_count) }}
                            </div>
                        </div>
                        <span class="text-gray-400">&rarr;</span>
                    </a>
                @empty
                    <p class="text-gray-500 text-sm bg-white rounded-xl border border-gray-200 p-5 text-center">
                        @if ($search)
                            Aucun mandant ne correspond à « {{ $search }} ».
                        @else
                            Aucun mandant enregistré pour l'instant.
                        @endif
                    </p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>