<x-layouts.app title="Mon Stock">
<div x-data="{ orderAlloc: null }">

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
                        <button @click="orderAlloc = {{ $alloc->toJson() }}"
                                class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                            Commander au fournisseur
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-xl border border-slate-200 py-16 text-center text-slate-400">
                Aucun stock alloué par votre fournisseur pour le moment.
            </div>
        @endforelse
    </div>

    {{-- Modal Commander --}}
    <div x-show="orderAlloc !== null" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div @click.outside="orderAlloc = null" class="bg-white rounded-2xl shadow-xl w-full max-w-md" x-cloak>
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-800">Passer une commande</h3>
                <button @click="orderAlloc = null" class="text-slate-400 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <template x-if="orderAlloc">
                <form method="POST" action="{{ route('seller.orders.place') }}" class="px-6 py-5 space-y-4">
                    @csrf
                    <input type="hidden" name="allocation_id" :value="orderAlloc.id">
                    <div class="bg-slate-50 rounded-lg p-4 text-sm space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Stock disponible :</span>
                            <span class="font-semibold" x-text="orderAlloc.remaining_qty + ' unités'"></span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Quantité à commander</label>
                        <input type="number" name="quantity" min="1" :max="orderAlloc.remaining_qty" required
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-sm font-medium transition-colors">Confirmer la commande</button>
                        <button type="button" @click="orderAlloc = null" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-lg text-sm font-medium transition-colors">Annuler</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
</x-layouts.app>
