<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Confirmer les champs détectés</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="flex items-center gap-2 mb-8 text-sm">
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-green-600 text-white font-bold text-xs">✓</span>
                <span class="text-gray-400">Informations du template</span>
                <span class="text-gray-300">—</span>
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-[#16213E] text-white font-bold text-xs">2</span>
                <span class="font-semibold text-[#16213E]">Confirmer les champs</span>
            </div>

            @if ($hasLogo)
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl">
                    ✓ Ce modèle affichera le logo automatiquement à la validation.
                </div>
            @else
                <div class="mb-4 p-4 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-xl">
                    Aucun repère logo trouvé — il sera ajouté automatiquement dans l'en-tête du document.
                </div>
            @endif

            @if ($noVariablesFound)
                <div class="mb-4 p-4 bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl">
                    Aucune zone à remplir détectée dans ce fichier — ce sera un modèle fixe.
                </div>
            @endif

            <form method="POST" action="{{ route('templates.store') }}"
                  x-data="{
                      fields: {{ Illuminate\Support\Js::from($suggestedFields) }},
                      removeField(i) { this.fields.splice(i, 1); }
                  }"
                  class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 space-y-6">
                @csrf

                <input type="hidden" name="temp_path" value="{{ $tempPath }}">
                <input type="hidden" name="category_id" value="{{ $categoryId }}">
                <input type="hidden" name="new_category_name" value="{{ $newCategoryName }}">
                <input type="hidden" name="type_name" value="{{ $typeName }}">

                <h3 class="font-bold text-lg text-gray-800">{{ $typeName }}</h3>
                <p class="text-sm text-gray-500 -mt-4">
                    Vérifiez le libellé de chaque champ et son type — corrigez si besoin, ou retirez
                    (✕) les champs qui appartiennent en réalité à un tableau que vous allez définir
                    ci-dessous.
                </p>

                <div class="space-y-3">
                    {{-- template x-for : chaque ligne devient supprimable côté client,
                         sans recharger la page — le champ disparaît juste du tableau
                         "fields" en JavaScript, donc n'est plus soumis au serveur. --}}
                    <template x-for="(field, i) in fields" :key="field.key">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-3 sm:items-center border border-gray-100 rounded-xl p-4">
                            <div class="sm:col-span-3">
                                <code class="text-xs text-gray-400 break-all" x-text="'\u0024{' + field.key + '}'"></code>
                                <input type="hidden" name="field_keys[]" :value="field.key">
                            </div>

                            <div class="sm:col-span-5">
                                <label class="sm:hidden text-xs font-semibold text-gray-500">Libellé affiché</label>
                                <input type="text" name="field_labels[]" x-model="field.label"
                                       class="w-full rounded-lg border-gray-300 text-sm">
                            </div>

                            <div class="sm:col-span-3">
                                <label class="sm:hidden text-xs font-semibold text-gray-500">Type</label>
                                <select name="field_types[]" x-model="field.type" class="w-full rounded-lg border-gray-300 text-sm">
                                    <option value="text">Texte court</option>
                                    <option value="textarea">Texte long</option>
                                    <option value="date">Date</option>
                                    <option value="number">Nombre</option>
                                </select>
                            </div>

                            <div class="sm:col-span-1 text-right">
                                <button type="button" @click="removeField(i)"
                                        class="text-red-400 hover:text-red-600 font-bold" title="Retirer ce champ">
                                    ✕
                                </button>
                            </div>
                        </div>
                    </template>

                    <p x-show="fields.length === 0" class="text-sm text-gray-400 text-center py-4">
                        Tous les champs simples ont été retirés — vérifiez que c'est bien voulu.
                    </p>
                </div>

                {{-- --- Constructeur de tableau optionnel --- --}}
                <div x-data="{
                        hasTable: false,
                        columns: [{ key: '', label: '', type: 'text' }]
                     }" class="border-t border-gray-100 pt-6">

                    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                        <input type="checkbox" x-model="hasTable" name="has_table" value="1" class="rounded">
                        Ce rapport contient un tableau à lignes répétées (devis, dommages...)
                    </label>

                    <div x-show="hasTable" class="mt-4 space-y-4 bg-gray-50 rounded-xl p-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Nom technique du tableau</label>
                            <input type="text" name="table_key" placeholder="ex : lignes_devis"
                                   class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Titre affiché</label>
                            <input type="text" name="table_label" placeholder="ex : Détail des travaux"
                                   class="w-full rounded-lg border-gray-300 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-2">Colonnes du tableau</label>
                            <template x-for="(col, i) in columns" :key="i">
                                <div class="grid grid-cols-12 gap-2 mb-2">
                                    <input type="text" x-model="col.key" :name="'table_column_keys[]'"
                                           placeholder="cle_technique" class="col-span-4 rounded-lg border-gray-300 text-sm">
                                    <input type="text" x-model="col.label" :name="'table_column_labels[]'"
                                           placeholder="Libellé affiché" class="col-span-5 rounded-lg border-gray-300 text-sm">
                                    <select x-model="col.type" :name="'table_column_types[]'" class="col-span-2 rounded-lg border-gray-300 text-sm">
                                        <option value="text">Texte</option>
                                        <option value="number">Nombre</option>
                                    </select>
                                    <button type="button" @click="columns.splice(i, 1)" class="col-span-1 text-red-500 text-sm">✕</button>
                                </div>
                            </template>
                            <button type="button" @click="columns.push({ key: '', label: '', type: 'text' })"
                                    class="text-sm text-[#16213E] font-semibold hover:underline">
                                + Ajouter une colonne
                            </button>
                        </div>

                        <hr class="border-gray-200">

                        <div>
                            <p class="text-xs font-semibold text-gray-500 mb-2">Calcul automatique (facultatif)</p>
                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                <span>Colonne</span>
                                <input type="text" name="computed_target" placeholder="ex : pt"
                                       class="w-24 rounded-lg border-gray-300 text-sm">
                                <span>=</span>
                                <input type="text" name="computed_factor_1" placeholder="ex : qte"
                                       class="w-24 rounded-lg border-gray-300 text-sm">
                                <span>×</span>
                                <input type="text" name="computed_factor_2" placeholder="ex : pu"
                                       class="w-24 rounded-lg border-gray-300 text-sm">
                            </div>
                            <p class="text-xs text-gray-400 mt-2">
                                Utilisez les mêmes noms techniques que dans les colonnes ci-dessus.
                            </p>
                        </div>

                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="show_total" value="1" class="rounded">
                            Afficher un total général (nécessite un repère
                            <code class="text-xs">${nom_du_tableau_total}</code> dans le Word, hors du tableau)
                        </label>
                    </div>
                </div>

                <button type="submit"
                        class="w-full sm:w-auto bg-[#16213E] text-white font-semibold px-8 py-3.5 rounded-xl hover:bg-[#0f1730] transition">
                    Enregistrer ce type de rapport
                </button>
            </form>

        </div>
    </div>
</x-app-layout>