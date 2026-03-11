<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SellerAllocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClientWebController extends Controller
{
    private function client()
    {
        return Auth::user()->client;
    }

    public function dashboard(): View
    {
        $client = $this->client();

        $stats = [
            'total_orders'    => Order::where('buyer_id', $client->user_id)->count(),
            'pending_payment' => Payment::where('payer_id', $client->user_id)->where('status', 'pending')->sum('amount'),
            'total_spent'     => Payment::where('payer_id', $client->user_id)->where('status', 'paid')->sum('amount'),
            'unit_price'      => $client->unit_price,
            'seller'          => $client->seller?->user,
        ];

        $recentOrders = Order::where('buyer_id', $client->user_id)
            ->with('payment')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('client.dashboard', compact('stats', 'recentOrders'));
    }

    // ─── Passer une commande ─────────────────────────────────────────────────

    public function orders(): View
    {
        $client = $this->client();
        $seller = $client->seller;

        $availableStock = $seller->allocations()
            ->with('weeklyStock')
            ->where('remaining_qty', '>', 0)
            ->get();

        $myOrders = Order::where('buyer_id', $client->user_id)
            ->with('payment')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('client.orders', compact('availableStock', 'myOrders', 'client'));
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'allocation_id' => 'required|integer|exists:seller_allocations,id',
            'quantity'      => 'required|integer|min:1',
        ]);

        $client     = $this->client();
        $allocation = SellerAllocation::where('seller_id', $client->seller_id)
            ->with('weeklyStock')
            ->findOrFail($request->allocation_id);

        if ($allocation->remaining_qty < $request->quantity) {
            return back()->withErrors(['quantity' => 'Quantité supérieure au stock disponible (' . $allocation->remaining_qty . ').']);
        }

        DB::transaction(function () use ($request, $client, $allocation) {
            $allocation->decrement('remaining_qty', $request->quantity);

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'buyer_id'     => $client->user_id,
                'seller_id'    => $client->seller_id,
                'week_start'   => $allocation->weeklyStock->week_start,
                'quantity'     => $request->quantity,
                'unit_price'   => $client->unit_price,
                'status'       => Order::STATUS_CONFIRMED,
            ]);

            Payment::create([
                'order_id' => $order->id,
                'payer_id' => $client->user_id,
                'amount'   => $order->total_amount,
                'status'   => Payment::STATUS_PENDING,
                'due_date' => now()->addDays(7),
            ]);
        });

        return redirect()->route('client.orders')->with('success', 'Commande passée avec succès.');
    }
}
