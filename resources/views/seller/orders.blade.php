<x-layouts.app title="Commandes Clients">
<div x-data="{ showCreate: false, editOrder: null }">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Commandes Clients</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $orders->count() }} commande(s) au total</p>
        </div>
        @if($clients->count() && $allocations->count())
            <button @click="showCreate = true"
                    class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Nouvelle commande
            </button>
        @else
            <div class="text-sm text-slate-400 bg-amber-50 border border-amber-200 text-amber-700 px-3 py-2 rounded-lg">
                @if(!$clients->count()) Ajoutez d'abord des clients.
                @elseif(!$allocations->count()) Aucun stock disponible cette semaine.
                @endif
            </div>
        @endif
    </div>

    {{-- Tableau --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-slate-500 uppercase tracking-wide bg-slate-50 border-b border-slate-200">
                    <th class="text-left px-6 py-3 font-medium">N° Commande</th>
                    <th class="text-left px-6 py-3 font-medium">Client</th>
                    <th class="text-left px-6 py-3 font-medium">Semaine</th>
                    <th class="text-right px-6 py-3 font-medium">Qté</th>
                    <th class="text-right px-6 py-3 font-medium">Prix/u</th>
                    <th class="text-right px-6 py-3 font-medium">Total</th>
                    <th class="text-left px-6 py-3 font-medium">Statut</th>
                    <th class="text-left px-6 py-3 font-medium">Paiement</th>
                    <th class="text-right px-6 py-3 font-medium">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($orders as $order)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-mono text-xs font-medium">{{ $order->order_number }}</td>
                        <td class="px-6 py-4 font-medium">{{ $order->buyer->name }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ \Carbon\Carbon::parse($order->week_start)->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-right">{{ $order->quantity }}</td>
                        <td class="px-6 py-4 text-right text-slate-500">{{ number_format($order->unit_price, 2, ',', ' ') }} €</td>
                        <td class="px-6 py-4 text-right font-bold">{{ number_format($order->total_amount, 2, ',', ' ') }} €</td>
                        <td class="px-6 py-4">
                            @php
                                $statusConfig = match($order->status) {
                                    'confirmed'  => ['bg-blue-100 text-blue-700',   'Confirmée'],
                                    'delivered'  => ['bg-emerald-100 text-emerald-700', 'Livrée'],
                                    'cancelled'  => ['bg-slate-100 text-slate-500', 'Annulée'],
                                    default      => ['bg-amber-100 text-amber-700', 'En attente'],
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusConfig[0] }}">
                                {{ $statusConfig[1] }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($order->payment)
                                <x-payment-badge :status="$order->payment->status" />
                            @else
                                <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($order->status !== 'delivered' && $order->status !== 'cancelled')
                                <button @click="editOrder = {{ $order->toJson() }}"
                                        class="text-slate-400 hover:text-indigo-600 text-xs border border-slate-200 hover:border-indigo-300 px-2 py-1 rounded-lg transition-colors">
                                    Statut
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-16 text-center">
                            <div class="text-slate-300 mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mx-auto" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" /></svg>
                            </div>
                            <p class="text-slate-400 text-sm">Aucune commande client pour l'instant</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Créer commande --}}
    <div x-show="showCreate" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div @click.outside="showCreate = false" class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-800">Créer une commande</h3>
                <button @click="showCreate = false" class="text-slate-400 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('seller.orders.create') }}" class="px-6 py-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Client</label>
                    <select name="client_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">Sélectionner un client...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">
                                {{ $client->user->name }}
                                — {{ number_format($client->unit_price, 2, ',', ' ') }} €/unité
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Semaine / Stock</label>
                    <select name="allocation_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">Sélectionner une semaine...</option>
                        @foreach($allocations as $alloc)
                            <option value="{{ $alloc->id }}">
                                Semaine du {{ \Carbon\Carbon::parse($alloc->weeklyStock->week_start)->format('d/m/Y') }}
                                — {{ $alloc->remaining_qty }} unités disponibles
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Quantité</label>
                    <input type="number" name="quantity" min="1" required
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <p class="text-xs text-slate-400 mt-1">Le prix appliqué sera celui défini pour ce client.</p>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-sm font-medium transition-colors">Créer la commande</button>
                    <button type="button" @click="showCreate = false" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-lg text-sm font-medium transition-colors">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Modifier statut commande --}}
    <div x-show="editOrder !== null" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div @click.outside="editOrder = null" class="bg-white rounded-2xl shadow-xl w-full max-w-sm" x-cloak>
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-800">Changer le statut</h3>
                <button @click="editOrder = null" class="text-slate-400 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <template x-if="editOrder">
                <form :action="`/seller/orders/${editOrder.id}/status`" method="POST" class="px-6 py-5 space-y-4">
                    @csrf @method('PUT')
                    <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-600">
                        Commande <strong x-text="editOrder.order_number"></strong>
                        — <strong x-text="editOrder.quantity + ' unités'"></strong>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nouveau statut</label>
                        <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="confirmed">Confirmée</option>
                            <option value="delivered">Livrée</option>
                            <option value="cancelled">Annulée</option>
                        </select>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-sm font-medium transition-colors">Enregistrer</button>
                        <button type="button" @click="editOrder = null" class="flex-1 bg-slate-100 text-slate-700 py-2.5 rounded-lg text-sm font-medium">Annuler</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
</x-layouts.app>
