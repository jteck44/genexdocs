<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $category->name }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <a href="{{ route('report-categories.index') }}" class="text-sm text-gray-500 hover:text-gray-800">
                &larr; Retour aux catégories
            </a>

            <p class="text-gray-500 mt-3 mb-6">Choisissez le type de rapport à rédiger.</p>

            @if ($types->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
                    <p class="text-gray-600">Aucun modèle dans cette catégorie pour l'instant.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($types as $type)
                        <div class="flex items-center justify-between bg-white rounded-2xl border border-gray-200 px-6 py-5 hover:border-[#16213E]/30 hover:shadow-sm transition">
                            <a href="{{ route('reports.create', $type->slug) }}" class="flex-1">
                                <div class="font-semibold text-gray-800 text-lg">{{ $type->name }}</div>
                                <div class="text-sm text-gray-400 mt-0.5">
                                    {{ count($type->fields) }} information(s) à renseigner
                                </div>
                            </a>

                            <div class="flex items-center gap-4">
                                <a href="{{ route('reports.create', $type->slug) }}"
                                   class="bg-[#16213E] text-white text-sm font-semibold px-4 py-2 rounded-lg">
                                    Commencer
                                </a>

                                @if (Auth::user()->isDirector())
                                    {{-- Double confirmation : on interroge d'abord le serveur
                                         pour savoir si ce template est utilisé, avant d'afficher
                                         le bon message. Deux confirm() successifs = deux fenêtres. --}}
                                    <form method="POST" action="{{ route('templates.destroy', $type->id) }}"
                                          onsubmit="event.preventDefault();
                                              fetch('{{ route('templates.check-deletable', $type->id) }}')
                                                  .then(r => r.json())
                                                  .then(data => {
                                                      const info = data.reports_count > 0
                                                          ? `Ce template est utilisé par ${data.reports_count} rapport(s). Il sera retiré de la liste, mais ces rapports continueront de fonctionner normalement.`
                                                          : `Ce template n'est utilisé par aucun rapport. Il sera supprimé définitivement, fichier compris.`;
                                                      if (confirm(info) && confirm('Voulez-vous vraiment continuer ?')) {
                                                          this.submit();
                                                      }
                                                  });
                                              return false;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 text-sm font-semibold hover:underline">
                                            Supprimer
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (Auth::user()->isDirector())
                <div class="mt-8 flex gap-4">
                    <a href="{{ route('templates.create') }}" class="text-sm text-[#16213E] font-semibold hover:underline">
                        + Importer un nouveau template
                    </a>
                    <a href="{{ route('templates.archived') }}" class="text-sm text-gray-500 hover:underline">
                        Voir les templates archivés
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>