<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $report->title }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex items-center gap-3">
                @php
                    $statusStyles = [
                        'draft' => 'bg-gray-100 text-gray-600',
                        'submitted' => 'bg-amber-100 text-amber-800',
                        'validated' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800',
                    ];
                    $statusLabels = [
                        'draft' => 'Brouillon',
                        'submitted' => 'En attente du directeur',
                        'validated' => 'Validé',
                        'rejected' => 'À corriger',
                    ];
                @endphp
                @if ($report->isValidated())
                    <span class="inline-flex items-center gap-1.5 pl-2 pr-3 py-1 rounded-full border-2 border-green-600 text-green-700 text-xs font-bold uppercase tracking-wide">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Validé
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide {{ $statusStyles[$report->status] }}">
                        {{ $statusLabels[$report->status] }}
                    </span>
                @endif
                <span class="text-sm text-gray-400">{{ $report->type->name }}</span>
            </div>

            @if ($report->isRejected() && $report->rejection_reason)
                <div class="p-5 bg-red-50 border border-red-200 rounded-2xl">
                    <p class="text-sm font-semibold text-red-800 mb-1">Ce que le directeur demande de corriger :</p>
                    <p class="text-sm text-red-700">{{ $report->rejection_reason }}</p>
                </div>
            @endif

            {{-- ================= CAS 1 : modifiable par l'expert ================= --}}
            @if ($report->isEditableByAuthor() && Auth::id() === $report->author_id)
                <form method="POST" action="{{ route('reports.update', $report->id) }}"
                      class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Titre du dossier</label>
                        <input type="text" name="title" value="{{ old('title', $report->title) }}" required
                               class="w-full rounded-xl border-gray-300 text-sm py-2.5">
                    </div>

                    <hr class="border-gray-100">

                    @foreach ($report->type->fields as $field)
                        @if ($field['type'] === 'table')
                            {{-- ============ CHAMP TABLEAU (le bloc qui manquait) ============ --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $field['label'] }}</label>

                                <div x-data="{
                                        columns: {{ Illuminate\Support\Js::from($field['columns']) }},
                                        computed: {{ Illuminate\Support\Js::from($field['computed'] ?? null) }},
                                        rows: {{ Illuminate\Support\Js::from($report->data[$field['key']] ?? [[]]) }},
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
                                                        <th class="text-left font-semibold text-gray-600 px-3 py-2 whitespace-nowrap" x-text="col.label"></th>
                                                    </template>
                                                    <th class="w-10"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-for="(row, i) in rows" :key="i">
                                                    <tr class="border-t border-gray-100">
                                                        <template x-for="col in columns" :key="col.key">
                                                            <td class="px-2 py-1.5">
                                                                {{-- Texte simple pour TOUTES les colonnes, y compris les
                                                                     nombres : un champ type="number" HTML efface la
                                                                     valeur en silence si la saisie lui semble invalide,
                                                                     sans le moindre message d'erreur. On reste en texte,
                                                                     la conversion en nombre se fait déjà côté serveur. --}}
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
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ $field['label'] }}</label>
                                <textarea name="data[{{ $field['key'] }}]" rows="4"
                                          class="w-full rounded-xl border-gray-300 text-sm">{{ old('data.'.$field['key'], $report->data[$field['key']] ?? '') }}</textarea>
                            </div>
                        @else
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ $field['label'] }}</label>
                                <input type="{{ $field['type'] }}" name="data[{{ $field['key'] }}]"
                                       value="{{ old('data.'.$field['key'], $report->data[$field['key']] ?? '') }}"
                                       class="w-full rounded-xl border-gray-300 text-sm py-2.5">
                            </div>
                        @endif
                    @endforeach

                    <button type="submit"
                            class="bg-gray-100 text-gray-700 font-semibold px-6 py-3 rounded-xl hover:bg-gray-200 transition">
                        Enregistrer les modifications
                    </button>
                </form>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('reports.export-draft', $report->id) }}"
                       class="text-center bg-white border-2 border-gray-200 text-gray-700 font-semibold px-6 py-3.5 rounded-xl hover:border-gray-300 transition">
                        Télécharger le brouillon (.docx)
                    </a>

                    <form method="POST" action="{{ route('reports.submit', $report->id) }}">
                        @csrf
                        <button type="submit"
                                class="w-full sm:w-auto bg-[#16213E] text-white font-semibold px-6 py-3.5 rounded-xl hover:bg-[#0f1730] transition">
                            Envoyer au directeur pour validation
                        </button>
                    </form>

                    @if ($report->isDraft())
                        <form method="POST" action="{{ route('reports.destroy', $report->id) }}"
                              onsubmit="return confirm('Supprimer définitivement ce brouillon ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-sm font-semibold hover:underline self-center">
                                Supprimer ce brouillon
                            </button>
                        </form>
                    @endif
                </div>

            {{-- ================= CAS 2 : lecture seule ================= --}}
            @else
                <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 space-y-5">
                    @foreach ($report->type->fields as $field)
                        @if ($field['type'] === 'table')
                            <div>
                                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">{{ $field['label'] }}</div>
                                <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            @foreach ($field['columns'] as $col)
                                                <th class="text-left font-semibold text-gray-600 px-3 py-2">{{ $col['label'] }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse (($report->data[$field['key']] ?? []) as $row)
                                            <tr class="border-t border-gray-100">
                                                @foreach ($field['columns'] as $col)
                                                    <td class="px-3 py-1.5">{{ $row[$col['key']] ?? '' }}</td>
                                                @endforeach
                                            </tr>
                                        @empty
                                            <tr><td class="px-3 py-2 text-gray-400" colspan="{{ count($field['columns']) }}">Aucune ligne</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div>
                                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ $field['label'] }}</div>
                                <div class="text-gray-800 mt-0.5 whitespace-pre-line">
                                    {{ $report->data[$field['key']] ?? '—' }}
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- --- Décision du directeur --- --}}
            @if (Auth::user()->isDirector() && $report->isSubmitted())
                <div class="bg-white rounded-2xl border-2 border-[#B8912F]/40 p-6 sm:p-8 space-y-5">
                    <h3 class="font-semibold text-gray-800">Votre décision</h3>

                    <form method="POST" action="{{ route('reports.validate', $report->id) }}">
                        @csrf
                        <button type="submit"
                                class="w-full sm:w-auto bg-green-700 text-white font-semibold px-6 py-3.5 rounded-xl hover:bg-green-800 transition">
                            ✓ Valider et générer le document officiel
                        </button>
                    </form>

                    <form method="POST" action="{{ route('reports.reject', $report->id) }}" class="space-y-2">
                        @csrf
                        <label class="block text-sm font-semibold text-gray-700">
                            Ou renvoyer à l'expert avec des corrections à faire
                        </label>
                        <textarea name="rejection_reason" rows="3" required
                                  class="w-full rounded-xl border-gray-300 text-sm"
                                  placeholder="Expliquez clairement ce qui doit être changé..."></textarea>
                        <button type="submit"
                                class="bg-white border-2 border-red-300 text-red-700 font-semibold px-6 py-3 rounded-xl hover:bg-red-50 transition">
                            Renvoyer pour correction
                        </button>
                    </form>
                </div>
            @endif

            @if ($report->isValidated())
                <a href="{{ route('reports.download-validated', $report->id) }}"
                   class="inline-flex items-center gap-2 bg-green-700 text-white font-semibold px-6 py-3.5 rounded-xl hover:bg-green-800 transition">
                    Télécharger le document officiel (.docx)
                </a>
            @endif

            @if ($report->statusLogs->isNotEmpty())
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4 text-sm">Historique</h3>
                    <div class="space-y-3">
                        @foreach ($report->statusLogs as $log)
                            <div class="text-sm border-l-2 border-gray-200 pl-3">
                                <span class="font-medium text-gray-700">{{ $log->user->name }}</span>
                                <span class="text-gray-500">— {{ $log->created_at->format('d/m/Y à H:i') }}</span>
                                @if ($log->to_status === 'rejected')
                                    <p class="text-red-600 mt-0.5">A rejeté : « {{ $log->reason }} »</p>
                                @elseif ($log->to_status === 'validated')
                                    <p class="text-green-700 mt-0.5">A validé le rapport</p>
                                @elseif ($log->to_status === 'submitted')
                                    <p class="text-gray-600 mt-0.5">A soumis pour validation</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>