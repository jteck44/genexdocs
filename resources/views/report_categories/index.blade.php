<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Rédiger un rapport</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <p class="text-gray-500 mb-6">Choisissez d'abord le domaine de la mission.</p>

            @if ($categories->isEmpty())
                {{-- État vide : on explique quoi faire plutôt que de laisser
                     un écran blanc silencieux (bonne pratique d'ergonomie). --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
                    <p class="text-gray-600">Aucune catégorie n'a encore été créée.</p>
                    @if (Auth::user()->isDirector())
                        <a href="{{ route('templates.create') }}"
                           class="inline-block mt-4 bg-[#16213E] text-white font-semibold px-6 py-3 rounded-xl hover:bg-[#0f1730]">
                            Importer votre premier template
                        </a>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($categories as $category)
                        <a href="{{ route('report-types.index', $category->slug) }}"
                           class="block bg-white rounded-2xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-[#16213E]/30 transition">
                            <h3 class="text-lg font-bold text-[#16213E]">{{ $category->name }}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $category->types_count }} {{ Str::plural('modèle', $category->types_count) }} disponible{{ $category->types_count > 1 ? 's' : '' }}
                            </p>
                        </a>
                    @endforeach
                </div>
            @endif

            @if (Auth::user()->isDirector() && $categories->isNotEmpty())
                <div class="mt-8">
                    <a href="{{ route('templates.create') }}" class="text-sm text-[#16213E] font-semibold hover:underline">
                        + Importer un nouveau template
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>