<x-layouts.app title="Mon Stock">
<div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Mon Stock</h2>
            <p class="text-sm text-slate-500 mt-0.5">Allocations reçues du fournisseur</p>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($allocations as $alloc)
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="font-semibold text-slate-800">
                                Semaine du {{ \Carbon\Carbon::parse($alloc->weeklyStock->week_start)->format('d/m/Y') }}
                            </h3>
                            <span class="text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">
                                {{ number_format($alloc->weeklyStock->unit_price, 2, ',', ' ') }} €/unité
                            </span>
                        </div>
                        <p class="text-xs text-slate-400">Fournisseur : {{ $alloc->weeklyStock->supplier->user->name }}</p>
                    </div>
                    <div class="flex items-center gap-6 text-sm">
                        <div class="text-center">
                            <div class="font-bold text-slate-800 text-lg">{{ $alloc->allocated_qty }}</div>
                            <div class="text-xs text-slate-400">Alloué</div>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-indigo-600 text-lg">{{ $alloc->remaining_qty }}</div>
                            <div class="text-xs text-slate-400">Restant</div>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-emerald-600 text-lg">{{ $alloc->allocated_qty - $alloc->remaining_qty }}</div>
                            <div class="text-xs text-slate-400">Commandé</div>
                        </div>
                    </div>
                </div>

                {{-- Barre de progression --}}
                @php $pct = $alloc->allocated_qty > 0 ? round(($alloc->allocated_qty - $alloc->remaining_qty) / $alloc->allocated_qty * 100) : 0; @endphp
                <div class="mt-4">
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">{{ $pct }}% commandé</p>
                </div>

                @if($alloc->remaining_qty > 0)
                    <div class="mt-4">
                        <a href="{{ route('seller.orders') }}"
                           class="inline-flex items-center gap-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" /></svg>
                            Créer une commande client avec ce stock
                        </a>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-xl border border-slate-200 py-16 text-center text-slate-400">
                Aucun stock alloué par votre fournisseur pour le moment.
            </div>
        @endforelse
    </div>


</div>
</x-layouts.app>
