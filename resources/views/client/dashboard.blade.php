<x-layouts.app title="Tableau de bord — Client">

    {{-- Carte Vendeur --}}
    @if($stats['seller'])
        <div class="bg-gradient-to-r from-sky-500 to-indigo-600 rounded-xl p-6 mb-6 text-white">
            <p class="text-sky-100 text-xs font-medium uppercase tracking-wide mb-1">Votre vendeur</p>
            <p class="text-xl font-bold">{{ $stats['seller']->name }}</p>
            <div class="flex items-center gap-6 mt-3 text-sm text-sky-100">
                @if($stats['seller']->email)
                    <span>{{ $stats['seller']->email }}</span>
                @endif
                @if($stats['seller']->phone)
                    <span>{{ $stats['seller']->phone }}</span>
                @endif
                <span class="bg-white/20 px-2 py-0.5 rounded-full text-white font-medium">
                    {{ number_format($stats['unit_price'], 2, ',', ' ') }} €/unité
                </span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <x-stat-card label="Commandes passées" :value="$stats['total_orders']" color="indigo">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="À payer" :value="number_format($stats['pending_payment'], 2, ',', ' ') . ' €'" color="amber">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Total dépensé" :value="number_format($stats['total_spent'], 2, ',', ' ') . ' €'" color="emerald">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    {{-- Accès rapide --}}
    <div class="mb-8">
        <a href="{{ route('client.orders') }}"
           class="flex items-center gap-4 bg-white rounded-xl border border-slate-200 p-5 hover:border-indigo-300 hover:bg-indigo-50 transition-colors group shadow-sm">
            <div class="w-12 h-12 bg-indigo-100 group-hover:bg-indigo-200 rounded-xl flex items-center justify-center text-indigo-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            </div>
            <div>
                <p class="font-semibold text-slate-800">Passer une commande</p>
                <p class="text-sm text-slate-500 mt-0.5">Consultez le stock disponible et commandez</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-400 ml-auto" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
        </a>
    </div>

    {{-- Dernières commandes --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">Mes dernières commandes</h2>
            <a href="{{ route('client.orders') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Voir tout →</a>
        </div>
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
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 font-mono text-xs font-medium">{{ $order->order_number }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ \Carbon\Carbon::parse($order->week_start)->format('d/m/Y') }}</td>
                        <td class="px-6 py-3 text-right">{{ $order->quantity }}</td>
                        <td class="px-6 py-3 text-right font-semibold">{{ number_format($order->total_amount, 2, ',', ' ') }} €</td>
                        <td class="px-6 py-3">
                            @if($order->payment)<x-payment-badge :status="$order->payment->status" />
                            @else <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400">Aucune commande</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-layouts.app>
