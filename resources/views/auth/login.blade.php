<x-layouts.guest>
    <div class="w-full max-w-md mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 px-8 py-10 text-center">
                <div class="inline-flex items-center justify-center w-14 h-14 bg-white/20 rounded-2xl mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white">Connexion</h1>
                <p class="text-indigo-200 text-sm mt-1">Accédez à votre espace</p>
            </div>

            {{-- Form --}}
            <div class="px-8 py-8">
                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Adresse e-mail</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('email') border-red-400 @enderror">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Mot de passe</label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="remember" name="remember"
                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="remember" class="text-sm text-slate-600">Se souvenir de moi</label>
                    </div>

                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-4 rounded-lg text-sm transition-colors shadow-sm">
                        Se connecter
                    </button>
                </form>

                {{-- Demo accounts --}}
                <div class="mt-6 pt-5 border-t border-slate-100">
                    <p class="text-xs text-slate-400 text-center mb-3 font-medium uppercase tracking-wide">Comptes démo</p>
                    <div class="grid grid-cols-1 gap-2 text-xs text-slate-500">
                        <div class="flex justify-between bg-slate-50 rounded-lg px-3 py-2">
                            <span class="font-medium text-violet-600">Fournisseur</span>
                            <span>fournisseur@demo.com</span>
                        </div>
                        <div class="flex justify-between bg-slate-50 rounded-lg px-3 py-2">
                            <span class="font-medium text-emerald-600">Vendeur</span>
                            <span>vendeur.alice@demo.com</span>
                        </div>
                        <div class="flex justify-between bg-slate-50 rounded-lg px-3 py-2">
                            <span class="font-medium text-sky-600">Client</span>
                            <span>client.claire@demo.com</span>
                        </div>
                        <p class="text-center text-slate-400 mt-1">Mot de passe : <strong>password</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.guest>
