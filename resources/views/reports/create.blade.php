<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $type->name }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
                    <p class="font-semibold mb-1">Vérifiez les informations ci-dessous :</p>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('reports.store', $type->slug) }}"
                  class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Titre du dossier
                    </label>
                    <p class="text-xs text-gray-400 mb-2">Un nom simple pour reconnaître ce rapport plus tard.</p>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full rounded-xl border-gray-300 text-sm py-2.5"
                           placeholder="ex : Sinistre SCDP NSAM - Canon à mousse">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Mandant / Assuré</label>
                    <select name="mandant_id" class="w-full rounded-xl border-gray-300 text-sm py-2.5">
                        <option value="">— Nouveau mandant —</option>
                        @foreach (\App\Models\Mandant::orderBy('name')->get() as $mandant)
                            <option value="{{ $mandant->id }}">{{ $mandant->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="new_mandant_name" placeholder="Nom du nouveau mandant (si non listé ci-dessus)"
                        class="w-full rounded-xl border-gray-300 text-sm py-2.5 mt-2">
                </div>
                <hr class="border-gray-100">

                    @foreach ($type->fields as $field)
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                {{ $field['label'] }}
                            </label>
                            @if ($field['type'] === 'table')
                                {{-- Le tableau façon "mini-Excel". Toute la logique (ajout de
                                    ligne, calcul automatique) tourne dans le navigateur avec
                                    Alpine.js (déjà installé par Breeze) — rien n'est envoyé au
                                    serveur tant qu'on n'a pas cliqué sur "Enregistrer". --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $field['label'] }}</label>

                                    <div x-data="{
                                            columns: {{ Illuminate\Support\Js::from($field['columns']) }},
                                            computed: {{ Illuminate\Support\Js::from($field['computed'] ?? null) }},
                                            rows: {{ Illuminate\Support\Js::from(old('data.'.$field['key']) ?? [[]]) }},
                                            addRow() { this.rows.push({}); },
                                            removeRow(i) { if (this.rows.length > 1) this.rows.splice(i, 1); },
                                            recompute(i) {
                                                if (!this.computed) return;
                                                const r = this.rows[i];
                                                const a = parseFloat(r[this.computed.factors[0]]) || 0;
                                                const b = parseFloat(r[this.computed.factors[1]]) || 0;
                                                r[this.computed.target] = String(a * b);
                                            },
                                            get total() {
                                                if (!this.computed) return 0;
                                                return this.rows.reduce((sum, r) => sum + (parseFloat(r[this.computed.target]) || 0), 0);
                                            }
                                        }" class="border border-gray-200 rounded-xl overflow-hidden">

                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm">
                                                <thead class="bg-gray-100">
                                                    <tr>
                                                        <template x-for="col in columns" :key="col.key">
                                                            <th class="text-left font-semibold text-gray-600 px-3 py-2" x-text="col.label"></th>
                                                        </template>
                                                        <th class="w-10"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="(row, i) in rows" :key="i">
                                                        <tr class="border-t border-gray-100">
                                                            <template x-for="col in columns" :key="col.key">
                                                                <td class="px-2 py-1.5">
                                                                    {{-- Le nom du champ est construit dynamiquement en
                                                                        JavaScript pour que chaque ligne/colonne envoie
                                                                        sa valeur au bon endroit : data[cle_tableau][0][pu],
                                                                        data[cle_tableau][1][pu], etc. --}}
                                                                    <input :name="'data[{{ $field['key'] }}][' + i + '][' + col.key + ']'"
                                                                        x-model="row[col.key]"
                                                                        @input="recompute(i)"
                                                                        type="text"
                                                                        :inputmode="col.type === 'number' ? 'decimal' : 'text'"
                                                                        :readonly="computed && col.key === computed.target"
                                                                        :class="computed && col.key === computed.target ? 'bg-gray-100' : 'bg-white'"
                                                                        class="w-full min-w-[110px] rounded-lg border-gray-300 text-sm py-1.5">
                                                                </td>
                                                            </template>
                                                            <td class="text-center">
                                                                <button type="button" @click="removeRow(i)"
                                                                        class="text-red-400 hover:text-red-600" title="Retirer cette ligne">✕</button>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="p-3 bg-gray-50 flex items-center justify-between">
                                            <button type="button" @click="addRow()" class="text-sm text-[#16213E] font-semibold hover:underline">
                                                + Ajouter une ligne
                                            </button>
                                            <template x-if="computed">
                                                <span class="text-sm font-bold text-gray-700">
                                                    Total : <span x-text="total.toLocaleString('fr-FR')"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            @elseif ($field['type'] === 'textarea')
                             <textarea name="data[{{ $field['key'] }}]" rows="4"
                                      class="w-full rounded-xl border-gray-300 text-sm">{{ old('data.'.$field['key']) }}</textarea>
                            @else
                               <input type="{{ $field['type'] }}" name="data[{{ $field['key'] }}]"
                                   value="{{ old('data.'.$field['key']) }}"
                                   class="w-full rounded-xl border-gray-300 text-sm py-2.5">
                            @endif
                        </div>
                    @endforeach
                           
                       
                <button type="submit"
                        class="w-full sm:w-auto bg-[#16213E] text-white font-semibold px-8 py-3.5 rounded-xl hover:bg-[#0f1730] transition">
                    Enregistrer en brouillon
                </button>
                <p class="text-xs text-gray-400">
                    Rien n'est définitif — vous pourrez continuer à le modifier ensuite.
                </p>
            </form>

        </div>
    </div>
</x-app-layout>