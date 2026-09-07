<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Importer un nouveau template</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            {{-- Indicateur d'étapes : simple et rassurant pour quelqu'un qui
                 n'est pas familier des formulaires longs — on sait où on en est. --}}
            <div class="flex items-center gap-2 mb-8 text-sm">
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-[#16213E] text-white font-bold text-xs">1</span>
                <span class="font-semibold text-[#16213E]">Informations du template</span>
                <span class="text-gray-300">—</span>
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-gray-200 text-gray-500 font-bold text-xs">2</span>
                <span class="text-gray-400">Confirmer les champs</span>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('templates.analyze') }}" enctype="multipart/form-data"
                  class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Catégorie</label>
                    <select name="category_id" id="category_id"
                            class="w-full rounded-xl border-gray-300 text-sm py-2.5">
                        <option value="">— Créer une nouvelle catégorie —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Nom de la nouvelle catégorie
                    </label>
                    <p class="text-xs text-gray-400 mb-2">Uniquement si vous n'avez pas choisi de catégorie ci-dessus.</p>
                    <input type="text" name="new_category_name"
                           class="w-full rounded-xl border-gray-300 text-sm py-2.5" placeholder="ex : Infrastructures">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nom du type de rapport</label>
                    <input type="text" name="type_name" required
                           class="w-full rounded-xl border-gray-300 text-sm py-2.5" placeholder="ex : Rapport après travaux">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Fichier Word (.docx)</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-[#16213E]/40 transition">
                        <input type="file" name="template_file" accept=".docx" required class="w-full text-sm">
                        <p class="text-xs text-gray-400 mt-2">10 Mo maximum, format .docx uniquement.</p>
                    </div>
                </div>

                <button type="submit"
                        class="w-full sm:w-auto bg-[#16213E] text-white font-semibold px-8 py-3.5 rounded-xl hover:bg-[#0f1730] transition">
                    Analyser le fichier &rarr;
                </button>
            </form>

        </div>
    </div>
</x-app-layout>