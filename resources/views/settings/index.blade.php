<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Paramètres — Direction</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">
                <div>
                    <div class="text-xs font-semibold text-gray-400 uppercase">Directeur</div>
                    <div class="text-gray-800 font-medium">{{ Auth::user()->name }} (vous)</div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-gray-400 uppercase mb-1">Directeur adjoint</div>
                    @if ($adjoint)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-800 font-medium">{{ $adjoint->name }}</span>
                            <form method="POST" action="{{ route('settings.remove-adjoint') }}"
                                  onsubmit="return confirm('Retirer {{ $adjoint->name }} du poste d\'adjoint ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 text-sm font-semibold hover:underline">
                                    Retirer
                                </button>
                            </form>
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">
                            Aucun adjoint désigné — vous êtes seul à administrer GenexDocs.
                        </p>
                    @endif
                </div>
            </div>

            @if ($experts->isNotEmpty())
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-800 mb-3">
                        {{ $adjoint ? 'Remplacer' : 'Désigner' }} un directeur adjoint
                    </h3>
                    <form method="POST" action="{{ route('settings.set-adjoint') }}" class="flex gap-3">
                        @csrf
                        <select name="user_id" required class="flex-1 rounded-xl border-gray-300 text-sm">
                            <option value="">— Choisir un expert —</option>
                            @foreach ($experts as $expert)
                                <option value="{{ $expert->id }}">{{ $expert->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-[#16213E] text-white font-semibold px-5 py-2.5 rounded-xl hover:bg-[#0f1730]">
                            Désigner
                        </button>
                    </form>
                </div>
            @else
                <p class="text-gray-500 text-sm">
                    Aucun compte expert disponible — créez-en un depuis la page Équipe.
                </p>
            @endif

        </div>
    </div>
</x-app-layout>