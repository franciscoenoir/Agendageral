<x-guest-layout>

    <x-auth-session-status class="mb-4 text-green-400 text-sm text-center" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- E-mail --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">
                E-mail
            </label>
            <input id="email"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required autofocus autocomplete="username"
                   class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white placeholder-gray-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            @error('email')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Senha --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">
                Senha
            </label>
            <input id="password"
                   type="password"
                   name="password"
                   required autocomplete="current-password"
                   class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white placeholder-gray-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            @error('password')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Lembrar --}}
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="remember"
                       class="w-4 h-4 rounded border-white/20 bg-white/10 text-blue-500 focus:ring-blue-500 focus:ring-offset-0">
                <span class="text-sm text-gray-400">Lembrar-me</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="text-xs text-gray-400 hover:text-blue-400 transition">
                    Esqueceu a senha?
                </a>
            @endif
        </div>

        {{-- Botão --}}
        <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold py-2.5 rounded-xl text-sm transition shadow-lg shadow-blue-900/30 mt-2">
            Entrar
        </button>
    </form>

</x-guest-layout>
