<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Équipe</h2>
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

            @if (session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <details class="bg-white rounded-2xl border border-gray-200 p-5">
                <summary class="cursor-pointer font-semibold text-gray-700">+ Créer un nouveau compte</summary>
                <form method="POST" action="{{ route('users.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="text" name="name" required placeholder="Nom complet"
                           class="w-full rounded-xl border-gray-300 text-sm py-2.5">
                    <input type="email" name="email" required placeholder="Adresse email"
                           class="w-full rounded-xl border-gray-300 text-sm py-2.5">
                    <select name="role" class="w-full rounded-xl border-gray-300 text-sm py-2.5">
                        <option value="expert">Expert</option>
                    </select>
                    <button type="submit" class="bg-[#16213E] text-white font-semibold px-5 py-2.5 rounded-xl hover:bg-[#0f1730]">
                        Créer le compte
                    </button>
                </form>
            </details>

            <div class="space-y-2">
                @foreach ($users as $user)
                    <div class="flex items-center justify-between bg-white rounded-xl border border-gray-200 px-5 py-4">
                        <div>
                            <div class="font-semibold text-gray-800">{{ $user->name }}</div>
                            <div class="text-xs text-gray-400">{{ $user->email }}</div>
                        </div>
                        {{-- Lecture seule : la nomination de l'adjoint se fait
                             désormais depuis Paramètres, pas ici. --}}
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-semibold px-3 py-1 rounded-full
                                {{ match($user->role) {
                                    'director' => 'bg-[#B8912F]/15 text-[#8a6c22]',
                                    'director_adjoint' => 'bg-blue-50 text-blue-700',
                                    default => 'bg-gray-100 text-gray-600',
                                } }}">
                                {{ match($user->role) {
                                    'director' => 'Directeur',
                                    'director_adjoint' => 'Directeur adjoint',
                                    default => 'Expert',
                                } }}
                            </span>

                            @if ($user->isExpert())
                                <details class="relative">
                                    <summary class="cursor-pointer list-none text-xs font-semibold text-gray-500 hover:text-[#16213E]">
                                        Avancé
                                    </summary>
                                    <div class="absolute right-0 z-10 mt-2 w-52 rounded-lg border border-gray-200 bg-white p-3 shadow-lg">
                                        <form method="POST" action="{{ route('users.reset-password', $user) }}"
                                              onsubmit="return confirm('Générer un nouveau mot de passe pour {{ addslashes($user->name) }} ?');">
                                            @csrf
                                            <button type="submit" class="text-xs font-semibold text-[#16213E] hover:text-[#0f1730]">
                                                Nouveau mot de passe
                                            </button>
                                        </form>
                                    </div>
                                </details>
                                <form method="POST" action="{{ route('users.destroy', $user) }}"
                                      onsubmit="return confirm('Masquer le compte de {{ addslashes($user->name) }} ?') && confirm('{{ $user->reports_authored_count > 0 ? 'Ses rapports seront conservés, mais ce compte ne sera plus visible dans l’équipe. Confirmer ?' : 'Confirmer définitivement cette suppression ?' }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800">
                                        Masquer
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>