<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Tableau de bord</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if (Auth::user()->isDirector())
                {{-- --- Vue Directeur : ce qui attend une décision, en premier --- --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl border-2 border-[#B8912F]/40 p-5">
                        <div class="text-3xl font-extrabold text-[#B8912F]">{{ $stats['a_valider'] }}</div>
                        <div class="text-sm text-gray-500 mt-1">À valider</div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <div class="text-3xl font-extrabold text-green-700">{{ $stats['valides'] }}</div>
                        <div class="text-sm text-gray-500 mt-1">Validés</div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <div class="text-3xl font-extrabold text-red-600">{{ $stats['rejetes'] }}</div>
                        <div class="text-sm text-gray-500 mt-1">Rejetés</div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <div class="text-3xl font-extrabold text-gray-700">{{ $stats['total'] }}</div>
                        <div class="text-sm text-gray-500 mt-1">Rapports au total</div>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-3">En attente de votre décision</h3>
                    @forelse ($reportsAVerifier as $report)
                        <a href="{{ route('reports.show', $report->id) }}"
                           class="flex items-center justify-between bg-white rounded-xl border border-gray-200 px-5 py-4 mb-2 hover:border-[#B8912F] hover:shadow-sm transition">
                            <div>
                                <div class="font-semibold text-gray-800">{{ $report->title }}</div>
                                <div class="text-xs text-gray-400">{{ $report->type->name }} — par {{ $report->author->name }}</div>
                            </div>
                            <span class="text-[#B8912F] text-sm font-semibold">Examiner &rarr;</span>
                        </a>
                    @empty
                        <p class="text-gray-500 text-sm bg-white rounded-xl border border-gray-200 p-5">
                            Aucun rapport en attente pour le moment.
                        </p>
                    @endforelse
                </div>
            @else
                {{-- --- Vue Expert --- --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <div class="text-3xl font-extrabold text-gray-600">{{ $stats['brouillons'] }}</div>
                        <div class="text-sm text-gray-500 mt-1">Brouillons</div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <div class="text-3xl font-extrabold text-amber-600">{{ $stats['soumis'] }}</div>
                        <div class="text-sm text-gray-500 mt-1">En attente</div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <div class="text-3xl font-extrabold text-green-700">{{ $stats['valides'] }}</div>
                        <div class="text-sm text-gray-500 mt-1">Validés</div>
                    </div>
                    <div class="bg-white rounded-2xl border-2 border-red-300 p-5">
                        <div class="text-3xl font-extrabold text-red-600">{{ $stats['rejetes'] }}</div>
                        <div class="text-sm text-gray-500 mt-1">À corriger</div>
                    </div>
                </div>

                <a href="{{ route('report-categories.index') }}"
                   class="inline-block bg-[#16213E] text-white font-semibold px-6 py-3.5 rounded-xl hover:bg-[#0f1730] transition">
                    + Rédiger un nouveau rapport
                </a>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-3">Mes derniers rapports</h3>
                    @forelse ($mesRapports as $report)
                        <a href="{{ route('reports.show', $report->id) }}"
                           class="flex items-center justify-between bg-white rounded-xl border border-gray-200 px-5 py-4 mb-2 hover:border-gray-300 hover:shadow-sm transition">
                            <div>
                                <div class="font-semibold text-gray-800">{{ $report->title }}</div>
                                <div class="text-xs text-gray-400">{{ $report->type->name }}</div>
                            </div>
                            <span class="text-gray-400 text-sm">&rarr;</span>
                        </a>
                    @empty
                        <p class="text-gray-500 text-sm bg-white rounded-xl border border-gray-200 p-5">
                            Vous n'avez encore rédigé aucun rapport.
                        </p>
                    @endforelse
                </div>
            @endif

        </div>
    </div>
</x-app-layout>