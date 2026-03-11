<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SellerWebController extends Controller
{
    private function seller()
    {
        return Auth::user()->seller;
    }

    public function dashboard(): View
    {
        $seller = $this->seller();

        $stats = [
            'total_clients'          => $seller->clients()->count(),
            'active_clients'         => $seller->clients()->whereHas('user', fn($q) => $q->where('is_active', true))->count(),
            'total_remaining_stock'  => $seller->allocations()->sum('remaining_qty'),
            'my_pending_payments'    => Payment::where('payer_id', $seller->user_id)->where('status', 'pending')->count(),
            'client_payments_late'   => Payment::whereHas('order', fn($q) => $q->where('seller_id', $seller->id))->where('status', 'late')->count(),
        ];

        $recentOrders = Order::where('buyer_id', $seller->user_id)
            ->with('payment')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('seller.dashboard', compact('stats', 'recentOrders'));
    }

    // ─── Clients ─────────────────────────────────────────────────────────────

    public function clients(): View
    {
        $clients = $this->seller()->clients()->with('user')->get();
        return view('seller.clients', compact('clients'));
    }

    public function createClient(Request $request): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|max:50',
            'unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'role'      => 'client',
                'is_active' => false,
                'password'  => Hash::make(Str::random(32)),
            ]);
            Client::create([
                'user_id'    => $user->id,
                'seller_id'  => $this->seller()->id,
                'unit_price' => $request->unit_price,
            ]);
        });

        return redirect()->route('seller.clients')->with('success', 'Client créé avec succès.');
    }

    public function updateClient(Request $request, int $clientId): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'phone'      => 'nullable|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $client = $this->seller()->clients()->findOrFail($clientId);

        DB::transaction(function () use ($request, $client) {
            $client->user->update([
                'name'      => $request->name,
                'phone'     => $request->phone,
                'is_active' => $request->has('is_active'),
            ]);
            $client->update(['unit_price' => $request->unit_price]);
        });

        return redirect()->route('seller.clients')->with('success', 'Client mis à jour.');
    }

    public function deleteClient(int $clientId): RedirectResponse
    {
        $client = $this->seller()->clients()->findOrFail($clientId);
        $client->user->delete();

        return redirect()->route('seller.clients')->with('success', 'Client supprimé.');
    }

    // ─── Allocations reçues ──────────────────────────────────────────────────

    public function allocations(): View
    {
        $seller = $this->seller();

        $allocations = $seller->allocations()
            ->with('weeklyStock.supplier.user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('seller.allocations', compact('allocations'));
    }

    // ─── Commandes : vendeur crée pour ses clients ────────────────────────────

    public function orders(): View
    {
        $seller = $this->seller();

        $orders = Order::where('seller_id', $seller->id)
            ->with(['buyer', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();

        $clients = $seller->clients()->with('user')->get();

        $allocations = $seller->allocations()
            ->with('weeklyStock')
            ->where('remaining_qty', '>', 0)
            ->get();

        return view('seller.orders', compact('orders', 'clients', 'allocations'));
    }

    public function createOrderForClient(Request $request): RedirectResponse
    {
        $request->validate([
            'client_id'     => 'required|integer|exists:clients,id',
            'allocation_id' => 'required|integer|exists:seller_allocations,id',
            'quantity'      => 'required|integer|min:1',
        ]);

        $seller     = $this->seller();
        $client     = $seller->clients()->findOrFail($request->client_id);
        $allocation = $seller->allocations()->with('weeklyStock')->findOrFail($request->allocation_id);

        if ($allocation->remaining_qty < $request->quantity) {
            return back()->withErrors(['quantity' => 'Quantité supérieure au stock restant (' . $allocation->remaining_qty . ').']);
        }

        DB::transaction(function () use ($request, $seller, $client, $allocation) {
            $allocation->decrement('remaining_qty', $request->quantity);

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'buyer_id'     => $client->user_id,
                'seller_id'    => $seller->id,
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

        return redirect()->route('seller.orders')->with('success', 'Commande créée pour le client.');
    }

    public function updateOrderStatus(Request $request, int $orderId): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,delivered,cancelled',
        ]);

        $seller = $this->seller();
        $order  = Order::where('seller_id', $seller->id)->findOrFail($orderId);
        $order->update(['status' => $request->status]);

        return redirect()->route('seller.orders')->with('success', 'Statut mis à jour.');
    }

    // ─── Paiements ───────────────────────────────────────────────────────────

    public function payments(): View
    {
        $seller = $this->seller();

        $myPayments = Payment::where('payer_id', $seller->user_id)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->get();

        $clientPayments = Payment::whereHas('order', fn($q) => $q->where('seller_id', $seller->id))
            ->with(['order', 'payer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('seller.payments', compact('myPayments', 'clientPayments'));
    }

    public function updatePayment(Request $request, int $paymentId): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,paid,late,disputed',
            'notes'  => 'nullable|string',
        ]);

        $seller  = $this->seller();
        $payment = Payment::whereHas('order', fn($q) => $q->where('seller_id', $seller->id))
            ->findOrFail($paymentId);

        $payment->update([
            'status'  => $request->status,
            'notes'   => $request->notes,
            'paid_at' => $request->status === 'paid' ? now() : $payment->paid_at,
        ]);

        return redirect()->route('seller.payments')->with('success', 'Paiement mis à jour.');
    }
}
