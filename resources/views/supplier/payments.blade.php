<x-layouts.app title="Paiements — Fournisseur">
<div x-data="{ editPayment: null }">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Suivi des Paiements</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $payments->count() }} paiement(s) au total</p>
        </div>
        {{-- Résumé --}}
        <div class="flex items-center gap-3 text-sm">
            <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full font-medium">
                {{ $payments->where('status', 'pending')->count() }} en attente
            </span>
            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full font-medium">
                {{ $payments->where('status', 'late')->count() }} en retard
            </span>
            <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full font-medium">
                {{ $payments->where('status', 'paid')->count() }} payés
            </span>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-slate-500 uppercase tracking-wide bg-slate-50 border-b border-slate-200">
                    <th class="text-left px-6 py-3 font-medium">Vendeur</th>
                    <th class="text-left px-6 py-3 font-medium">Commande</th>
                    <th class="text-left px-6 py-3 font-medium">Semaine</th>
                    <th class="text-right px-6 py-3 font-medium">Montant</th>
                    <th class="text-left px-6 py-3 font-medium">Échéance</th>
                    <th class="text-left px-6 py-3 font-medium">Statut</th>
                    <th class="text-right px-6 py-3 font-medium">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($payments as $payment)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-medium">{{ $payment->payer->name }}</td>
                        <td class="px-6 py-4 text-slate-500 font-mono text-xs">{{ $payment->order->order_number }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ \Carbon\Carbon::parse($payment->order->week_start)->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-right font-semibold">{{ number_format($payment->amount, 2, ',', ' ') }} €</td>
                        <td class="px-6 py-4 text-slate-500 text-xs">
                            @if($payment->due_date)
                                {{ \Carbon\Carbon::parse($payment->due_date)->format('d/m/Y') }}
                                @if($payment->status === 'pending' && \Carbon\Carbon::parse($payment->due_date)->isPast())
                                    <span class="text-red-500 font-medium"> (dépassé)</span>
                                @endif
                            @else —
                            @endif
                        </td>
                        <td class="px-6 py-4"><x-payment-badge :status="$payment->status" /></td>
                        <td class="px-6 py-4 text-right">
                            <button @click="editPayment = {{ $payment->toJson() }}"
                                    class="text-slate-400 hover:text-indigo-600 transition-colors text-xs font-medium border border-slate-200 hover:border-indigo-300 px-2 py-1 rounded-lg">
                                Modifier
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">Aucun paiement</td></tr>
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
                <form :action="`/supplier/payments/${editPayment.id}`" method="POST" class="px-6 py-5 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <p class="text-sm text-slate-600">Montant : <strong x-text="editPayment.amount + ' €'"></strong></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Statut</label>
                        <select name="status" :value="editPayment.status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
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
