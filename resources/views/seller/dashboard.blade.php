<x-layouts.app title="Tableau de bord — Vendeur">

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <x-stat-card label="Clients actifs" :value="$stats['active_clients'] . ' / ' . $stats['total_clients']" color="emerald">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Stock restant" :value="$stats['total_remaining_stock'] . ' unités'" color="sky">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Mes paiements en attente" :value="$stats['my_pending_payments']" color="amber">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Clients en retard" :value="$stats['client_payments_late']" color="red">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    {{-- Raccourcis rapides --}}
    <div class="grid grid-cols-3 gap-4 mb-8">
        <a href="{{ route('seller.clients') }}" class="flex items-center gap-3 bg-white rounded-xl border border-slate-200 p-4 hover:border-emerald-300 hover:bg-emerald-50 transition-colors group">
            <div class="w-10 h-10 bg-emerald-100 group-hover:bg-emerald-200 rounded-lg flex items-center justify-center text-emerald-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-800">Fiches clients</p>
                <p class="text-xs text-slate-500">Ajouter, modifier</p>
            </div>
        </a>
        <a href="{{ route('seller.orders') }}" class="flex items-center gap-3 bg-white rounded-xl border border-slate-200 p-4 hover:border-indigo-300 hover:bg-indigo-50 transition-colors group">
            <div class="w-10 h-10 bg-indigo-100 group-hover:bg-indigo-200 rounded-lg flex items-center justify-center text-indigo-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" /></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-800">Commandes clients</p>
                <p class="text-xs text-slate-500">Créer et suivre</p>
            </div>
        </a>
        <a href="{{ route('seller.allocations') }}" class="flex items-center gap-3 bg-white rounded-xl border border-slate-200 p-4 hover:border-sky-300 hover:bg-sky-50 transition-colors group">
            <div class="w-10 h-10 bg-sky-100 group-hover:bg-sky-200 rounded-lg flex items-center justify-center text-sky-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-800">Mon stock</p>
                <p class="text-xs text-slate-500">Allocations reçues</p>
            </div>
        </a>
    </div>

    {{-- Dernières commandes --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">Mes dernières commandes</h2>
            <a href="{{ route('seller.allocations') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Gérer →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-slate-500 uppercase tracking-wide border-b border-slate-100">
                        <th class="text-left px-6 py-3 font-medium">N° Commande</th>
                        <th class="text-left px-6 py-3 font-medium">Semaine</th>
                        <th class="text-right px-6 py-3 font-medium">Qté</th>
                        <th class="text-right px-6 py-3 font-medium">Total</th>
                        <th class="text-left px-6 py-3 font-medium">Paiement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 font-mono text-xs font-medium">{{ $order->order_number }}</td>
                            <td class="px-6 py-3 text-slate-500">{{ \Carbon\Carbon::parse($order->week_start)->format('d/m/Y') }}</td>
                            <td class="px-6 py-3 text-right">{{ $order->quantity }}</td>
                            <td class="px-6 py-3 text-right font-semibold">{{ number_format($order->total_amount, 2, ',', ' ') }} €</td>
                            <td class="px-6 py-3">
                                @if($order->payment)
                                    <x-payment-badge :status="$order->payment->status" />
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400">Aucune commande</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
