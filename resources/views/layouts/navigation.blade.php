<nav x-data="{ open: false }" class="bg-white border-b border-stone-200/80 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14">

            <!-- Left: Logo + Links -->
            <div class="flex items-center gap-6">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 shrink-0">
                    <div class="w-7 h-7 rounded-lg bg-green-600 flex items-center justify-center shadow-sm shadow-green-900/20">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-stone-900 tracking-tight hidden sm:block">Pinboard</span>
                </a>

                <!-- Divider -->
                <div class="hidden sm:block w-px h-5 bg-stone-200"></div>

                <!-- Navigation Links -->
                <div class="hidden sm:flex items-center gap-1">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </x-nav-link>
                    @auth
                        <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                            </svg>
                            Projects
                        </x-nav-link>
                    @endauth
                </div>
            </div>

            <!-- Right: User menu -->
            <div class="hidden sm:flex sm:items-center sm:gap-3">
                <x-dropdown align="right" width="52">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg
                                       hover:bg-stone-50 transition duration-150 focus:outline-none
                                       focus:ring-2 focus:ring-stone-200">
                            <!-- Avatar -->
                            @php
                                $avatarColors = ['bg-violet-600','bg-blue-600','bg-emerald-600','bg-orange-500','bg-rose-500'];
                                $avatarColor  = $avatarColors[Auth::id() % count($avatarColors)];
                            @endphp
                            <div class="w-7 h-7 rounded-full {{ $avatarColor }}
                                        flex items-center justify-center text-xs font-bold text-white shrink-0">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="hidden md:flex items-center gap-2">
                                <span class="text-sm font-medium text-stone-700">{{ Auth::user()->name }}</span>
                                @if(Auth::user()->hasRole('manager'))
                                    <span class="badge badge-green">Manager</span>
                                @else
                                    <span class="badge badge-stone">Staff</span>
                                @endif
                            </div>
                            <svg class="w-3.5 h-3.5 text-stone-400 hidden md:block" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b border-stone-100">
                            <p class="text-xs font-semibold text-stone-800 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-stone-400 truncate mt-0.5">{{ Auth::user()->email }}</p>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')" class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profile
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="flex items-center gap-2 text-red-500 hover:text-red-600 hover:bg-red-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Sign out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Mobile hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-lg text-stone-400
                               hover:text-stone-600 hover:bg-stone-50 focus:outline-none transition">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-stone-100">
        <div class="pt-2 pb-3 space-y-0.5 px-3">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
                    Projects
                </x-responsive-nav-link>
            @endauth
        </div>

        <div class="pt-3 pb-4 border-t border-stone-100">
            <div class="px-4 flex items-center gap-3 mb-3">
                @php
                    $avatarColors = ['bg-violet-600','bg-blue-600','bg-emerald-600','bg-orange-500','bg-rose-500'];
                    $avatarColor  = $avatarColors[Auth::id() % count($avatarColors)];
                @endphp
                <div class="w-9 h-9 rounded-full {{ $avatarColor }}
                            flex items-center justify-center text-sm font-bold text-white shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div>
                    <div class="font-semibold text-sm text-stone-800">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-stone-400">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <div class="space-y-0.5 px-3">
                <x-responsive-nav-link :href="route('profile.edit')">Profile</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        Sign out
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
