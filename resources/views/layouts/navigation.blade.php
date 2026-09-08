<nav x-data="{ open: false }" class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">

                <a href="{{ route('dashboard') }}" class="relative z-10 flex items-center gap-0.5 shrink-0">
                    <img src="{{ asset('storage/images/genex/genexlogo.png') }}" alt="LogoGenex" class="h-10 w-auto">
                    <span class="text-xl font-extrabold text-[#16213E] tracking-tight">Genex</span>
                    <span class="text-xl font-extrabold text-[#B8912F] tracking-tight">Docs</span>
                </a>

                <div class="hidden sm:flex w-full sm:items-center rounded-lg border border-gray-300 shadow-md sm:ml-10 sm:space-x-5">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Tableau de bord
                    </x-nav-link>

                    <x-nav-link :href="route('report-categories.index')" :active="request()->routeIs('report-categories.*') || request()->routeIs('report-types.*')">
                        Rédiger un rapport
                    </x-nav-link>
                    <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                        Tous les rapports
                    </x-nav-link>

                    <x-nav-link :href="route('mandants.index')" :active="request()->routeIs('mandants.*')">
                        Les Mandants
                    </x-nav-link>
                    @if (Auth::user()->isDirector())
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                            Équipe
                        </x-nav-link>
                        <x-nav-link :href="route('templates.create')" :active="request()->routeIs('templates.*')">
                            Importer un template
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->isDirector())
                                 <x-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')">
                            Paramètres
                         </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:space-x-4">
                {{--  badge de rôle  --}}
                <span class="h-6 w-17 translate-x-4 overflow-hidden p-2 text-xs font-semibold px-2.5 py-1 rounded-full
                    {{ Auth::user()->isDirector() ? 'bg-[#B8912F]/15 text-[#8a6c22]' : 'bg-gray-100 text-gray-600' }}">
                    {{ Auth::user()->isDirector() ? 'Directeur' : 'Expert' }}
                </span>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center text-sm font-medium text-gray-600 hover:text-gray-900">
                            {{ Auth::user()->name }}
                            <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Mon profil</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Se déconnecter
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="p-2 text-gray-500 hover:text-gray-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-100">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Tableau de bord
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('report-categories.index')" :active="request()->routeIs('report-categories.*') || request()->routeIs('report-types.*')">
                Rédiger un rapport
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                Tous les rapports
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('mandants.index')" :active="request()->routeIs('mandants.*')">
                Les Mandants
            </x-responsive-nav-link>
            @if (Auth::user()->isDirector())
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    Équipe
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('templates.create')" :active="request()->routeIs('templates.*')">
                    Importer un template
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')">
                    Paramètres
                </x-responsive-nav-link>
            @endif
        </div>
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Mon profil</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        Se déconnecter
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>