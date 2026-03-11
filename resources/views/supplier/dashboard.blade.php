<x-layouts.app title="Tableau de bord — Fournisseur">

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <x-stat-card label="Vendeurs actifs" :value="$stats['active_sellers'] . ' / ' . $stats['total_sellers']" color="violet">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Paiements en attente" :value="$stats['pending_payments']" color="amber">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Paiements en retard" :value="$stats['late_payments']" color="red">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Revenus encaissés" :value="number_format($stats['total_revenue'], 2, ',', ' ') . ' €'" color="emerald">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
            </x-slot:icon>
        </x-stat-card>

        @if($stats['current_stock'])
        <x-stat-card label="Stock semaine courante" :value="$stats['current_stock']->available_qty . ' unités'" color="sky">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
            </x-slot:icon>
        </x-stat-card>
        @endif
    </div>

    {{-- Derniers paiements --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">Derniers paiements</h2>
            <a href="{{ route('supplier.payments') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Voir tout →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-slate-500 uppercase tracking-wide border-b border-slate-100">
                        <th class="text-left px-6 py-3 font-medium">Vendeur</th>
                        <th class="text-left px-6 py-3 font-medium">Commande</th>
                        <th class="text-right px-6 py-3 font-medium">Montant</th>
                        <th class="text-left px-6 py-3 font-medium">Statut</th>
                        <th class="text-left px-6 py-3 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($recentPayments as $payment)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 font-medium">{{ $payment->payer->name }}</td>
                            <td class="px-6 py-3 text-slate-500">{{ $payment->order->order_number }}</td>
                            <td class="px-6 py-3 text-right font-semibold">{{ number_format($payment->amount, 2, ',', ' ') }} €</td>
                            <td class="px-6 py-3"><x-payment-badge :status="$payment->status" /></td>
                            <td class="px-6 py-3 text-slate-500">{{ $payment->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400">Aucun paiement récent</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
