<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-bold text-stone-900">Sign in</h1>
        <p class="text-sm text-stone-500 mt-1">Welcome back to Pinboard.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email"
                          :value="old('email')" required autofocus autocomplete="username"
                          placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password"
                          required autocomplete="current-password"
                          placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember"
                       class="w-4 h-4 rounded border-stone-300 text-green-600 focus:ring-green-500/30">
                <span class="text-sm text-stone-600">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="text-sm text-stone-500 hover:text-stone-800 transition-colors">
                    Forgot password?
                </a>
            @endif
        </div>

        <x-primary-button class="w-full justify-center mt-2">
            Sign in
        </x-primary-button>

        @if (Route::has('register'))
            <p class="text-center text-sm text-stone-400 mt-2">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-stone-700 font-medium hover:text-green-700 transition-colors">
                    Create one
                </a>
            </p>
        @endif
    </form>
</x-guest-layout>
