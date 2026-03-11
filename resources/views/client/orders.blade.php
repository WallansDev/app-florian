<x-layouts.app title="Mes Commandes">
<div x-data="{ showOrder: false }">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Mes Commandes</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $myOrders->count() }} commande(s) au total</p>
        </div>
        @if($availableStock->count())
            <button @click="showOrder = true"
                    class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Nouvelle commande
            </button>
        @endif
    </div>

    {{-- Stock disponible --}}
    @if($availableStock->count())
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6 flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            <div class="text-sm text-emerald-800">
                <span class="font-semibold">Stock disponible !</span>
                {{ $availableStock->sum('remaining_qty') }} unités disponibles à
                <strong>{{ number_format($client->unit_price, 2, ',', ' ') }} €/unité</strong>
            </div>
        </div>
    @else
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
            <span class="text-sm text-amber-800">Aucun stock disponible pour cette semaine. Votre vendeur n'a pas encore alloué de stock.</span>
        </div>
    @endif

    {{-- Tableau des commandes --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-slate-500 uppercase tracking-wide bg-slate-50 border-b border-slate-200">
                    <th class="text-left px-6 py-3 font-medium">N° Commande</th>
                    <th class="text-left px-6 py-3 font-medium">Semaine</th>
                    <th class="text-right px-6 py-3 font-medium">Quantité</th>
                    <th class="text-right px-6 py-3 font-medium">Prix/unité</th>
                    <th class="text-right px-6 py-3 font-medium">Total</th>
                    <th class="text-left px-6 py-3 font-medium">Paiement</th>
                    <th class="text-left px-6 py-3 font-medium">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($myOrders as $order)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-mono text-xs font-medium">{{ $order->order_number }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ \Carbon\Carbon::parse($order->week_start)->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-right font-semibold">{{ $order->quantity }}</td>
                        <td class="px-6 py-4 text-right text-slate-500">{{ number_format($order->unit_price, 2, ',', ' ') }} €</td>
                        <td class="px-6 py-4 text-right font-bold text-slate-800">{{ number_format($order->total_amount, 2, ',', ' ') }} €</td>
                        <td class="px-6 py-4">
                            @if($order->payment)<x-payment-badge :status="$order->payment->status" />
                            @else <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-500 text-xs">{{ $order->created_at->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">Aucune commande pour l'instant</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Nouvelle commande --}}
    <div x-show="showOrder" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div @click.outside="showOrder = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-800">Nouvelle commande</h3>
                <button @click="showOrder = false" class="text-slate-400 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('client.orders.place') }}" class="px-6 py-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Semaine disponible</label>
                    <select name="allocation_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">Sélectionner une semaine...</option>
                        @foreach($availableStock as $alloc)
                            <option value="{{ $alloc->id }}">
                                Semaine du {{ \Carbon\Carbon::parse($alloc->weeklyStock->week_start)->format('d/m/Y') }}
                                — {{ $alloc->remaining_qty }} unités dispo
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Quantité (à {{ number_format($client->unit_price, 2, ',', ' ') }} €/unité)
                    </label>
                    <input type="number" name="quantity" min="1" required
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-sm font-medium transition-colors">Commander</button>
                    <button type="button" @click="showOrder = false" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-lg text-sm font-medium transition-colors">Annuler</button>
                </div>
            </form>
        </div>
    </div>

</div>
</x-layouts.app>
