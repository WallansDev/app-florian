<x-layouts.app title="Paiements — Vendeur">
<div x-data="{ editPayment: null }">

    <h2 class="text-xl font-bold text-slate-800 mb-6">Suivi des Paiements</h2>

    {{-- Mes paiements vers le fournisseur --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-8">
        <div class="flex items-center gap-2 px-6 py-4 border-b border-slate-100">
            <span class="w-2.5 h-2.5 rounded-full bg-violet-500"></span>
            <h3 class="font-semibold text-slate-800">Mes paiements au fournisseur</h3>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-slate-500 uppercase tracking-wide bg-slate-50 border-b border-slate-200">
                    <th class="text-left px-6 py-3 font-medium">Commande</th>
                    <th class="text-left px-6 py-3 font-medium">Semaine</th>
                    <th class="text-right px-6 py-3 font-medium">Montant</th>
                    <th class="text-left px-6 py-3 font-medium">Échéance</th>
                    <th class="text-left px-6 py-3 font-medium">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($myPayments as $p)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 font-mono text-xs">{{ $p->order->order_number }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ \Carbon\Carbon::parse($p->order->week_start)->format('d/m/Y') }}</td>
                        <td class="px-6 py-3 text-right font-semibold">{{ number_format($p->amount, 2, ',', ' ') }} €</td>
                        <td class="px-6 py-3 text-slate-500 text-xs">{{ $p->due_date ? \Carbon\Carbon::parse($p->due_date)->format('d/m/Y') : '—' }}</td>
                        <td class="px-6 py-3"><x-payment-badge :status="$p->status" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400">Aucun paiement</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paiements clients --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <h3 class="font-semibold text-slate-800">Paiements de mes clients</h3>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">
                    {{ $clientPayments->where('status', 'pending')->count() }} en attente
                </span>
                <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">
                    {{ $clientPayments->where('status', 'late')->count() }} en retard
                </span>
            </div>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-slate-500 uppercase tracking-wide bg-slate-50 border-b border-slate-200">
                    <th class="text-left px-6 py-3 font-medium">Client</th>
                    <th class="text-left px-6 py-3 font-medium">Commande</th>
                    <th class="text-right px-6 py-3 font-medium">Montant</th>
                    <th class="text-left px-6 py-3 font-medium">Échéance</th>
                    <th class="text-left px-6 py-3 font-medium">Statut</th>
                    <th class="text-right px-6 py-3 font-medium">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($clientPayments as $p)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 font-medium">{{ $p->payer->name }}</td>
                        <td class="px-6 py-3 font-mono text-xs text-slate-500">{{ $p->order->order_number }}</td>
                        <td class="px-6 py-3 text-right font-semibold">{{ number_format($p->amount, 2, ',', ' ') }} €</td>
                        <td class="px-6 py-3 text-slate-500 text-xs">{{ $p->due_date ? \Carbon\Carbon::parse($p->due_date)->format('d/m/Y') : '—' }}</td>
                        <td class="px-6 py-3"><x-payment-badge :status="$p->status" /></td>
                        <td class="px-6 py-3 text-right">
                            <button @click="editPayment = {{ $p->toJson() }}"
                                    class="text-slate-400 hover:text-indigo-600 text-xs border border-slate-200 hover:border-indigo-300 px-2 py-1 rounded-lg transition-colors">
                                Modifier
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400">Aucun paiement client</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal modifier statut --}}
    <div x-show="editPayment !== null" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div @click.outside="editPayment = null" class="bg-white rounded-2xl shadow-xl w-full max-w-md" x-cloak>
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-800">Modifier le statut</h3>
                <button @click="editPayment = null" class="text-slate-400 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <template x-if="editPayment">
                <form :action="`/seller/payments/${editPayment.id}`" method="POST" class="px-6 py-5 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Statut</label>
                        <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="pending">En attente</option>
                            <option value="paid">Payé</option>
                            <option value="late">En retard</option>
                            <option value="disputed">Contesté</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2" x-text="editPayment.notes" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none"></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-sm font-medium transition-colors">Enregistrer</button>
                        <button type="button" @click="editPayment = null" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-lg text-sm font-medium transition-colors">Annuler</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
</x-layouts.app>
