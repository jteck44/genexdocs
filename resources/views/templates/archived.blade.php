<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Templates archivés</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <p class="text-gray-500 text-sm">
                Ces templates ont été retirés de la liste active, mais rien n'a été supprimé :
                les rapports déjà créés avec eux continuent de fonctionner, et vous pouvez les
                remettre en service à tout moment.
            </p>

            <div class="space-y-2">
                @forelse ($archivedTypes as $type)
                    <div class="flex items-center justify-between bg-white rounded-xl border border-gray-200 px-5 py-4">
                        <div>
                            <div class="font-semibold text-gray-800">{{ $type->name }}</div>
                            <div class="text-xs text-gray-400">
                                {{ $type->category->name }}
                                — utilisé par {{ $type->reports_count }} rapport(s)
                                — archivé le {{ $type->deleted_at->format('d/m/Y') }}
                            </div>
                        </div>
                        <form method="POST" action="{{ route('templates.restore', $type->id) }}">
                            @csrf
                            <button type="submit" class="bg-[#16213E] text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-[#0f1730]">
                                Remettre en service
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm bg-white rounded-xl border border-gray-200 p-5 text-center">
                        Aucun template archivé pour l'instant.
                    </p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>