<x-layouts.app title="Mes Clients">
<div x-data="{ showCreate: false, editClient: null }">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Mes Clients</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $clients->count() }} client(s) dans votre réseau</p>
        </div>
        <button @click="showCreate = true"
                class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Ajouter un client
        </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-slate-500 uppercase tracking-wide bg-slate-50 border-b border-slate-200">
                    <th class="text-left px-6 py-3 font-medium">Nom</th>
                    <th class="text-left px-6 py-3 font-medium">Email</th>
                    <th class="text-left px-6 py-3 font-medium">Téléphone</th>
                    <th class="text-right px-6 py-3 font-medium">Prix unitaire</th>
                    <th class="text-left px-6 py-3 font-medium">Statut</th>
                    <th class="text-right px-6 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($clients as $client)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-medium">{{ $client->user->name }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ $client->user->email }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ $client->user->phone ?? '—' }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-emerald-600">{{ number_format($client->unit_price, 2, ',', ' ') }} €</td>
                        <td class="px-6 py-4">
                            @if($client->user->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Actif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="editClient = {{ $client->toJson() }}; editClient.user = {{ $client->user->toJson() }}"
                                    class="text-slate-400 hover:text-indigo-600 transition-colors mr-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                            </button>
                            <form method="POST" action="{{ route('seller.clients.delete', $client->id) }}" class="inline"
                                  onsubmit="return confirm('Supprimer ce client ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">Aucun client pour l'instant</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Créer --}}
    <div x-show="showCreate" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div @click.outside="showCreate = false" class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-800">Nouveau client</h3>
                <button @click="showCreate = false" class="text-slate-400 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('seller.clients.create') }}" class="px-6 py-5 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nom complet</label>
                        <input type="text" name="name" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone</label>
                        <input type="text" name="phone" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Mot de passe</label>
                        <input type="password" name="password" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Confirmation</label>
                        <input type="password" name="password_confirmation" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Prix de vente unitaire (€)</label>
                        <input type="number" name="unit_price" step="0.01" min="0" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-sm font-medium transition-colors">Créer</button>
                    <button type="button" @click="showCreate = false" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-lg text-sm font-medium transition-colors">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Modifier --}}
    <div x-show="editClient !== null" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div @click.outside="editClient = null" class="bg-white rounded-2xl shadow-xl w-full max-w-lg" x-cloak>
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-800">Modifier le client</h3>
                <button @click="editClient = null" class="text-slate-400 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <template x-if="editClient">
                <form :action="`/seller/clients/${editClient.id}`" method="POST" class="px-6 py-5 space-y-4">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nom complet</label>
                            <input type="text" name="name" :value="editClient.user?.name" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone</label>
                            <input type="text" name="phone" :value="editClient.user?.phone" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Prix unitaire (€)</label>
                            <input type="number" name="unit_price" step="0.01" min="0" :value="editClient.unit_price" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div class="col-span-2 flex items-center gap-2">
                            <input type="checkbox" id="is_active_c" name="is_active" :checked="editClient.user?.is_active" class="w-4 h-4 rounded border-slate-300 text-indigo-600">
                            <label for="is_active_c" class="text-sm text-slate-700">Compte actif</label>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-sm font-medium transition-colors">Enregistrer</button>
                        <button type="button" @click="editClient = null" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-lg text-sm font-medium transition-colors">Annuler</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
</x-layouts.app>
