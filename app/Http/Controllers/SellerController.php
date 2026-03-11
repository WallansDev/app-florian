<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SellerAllocation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    // ─── Gestion des Clients ────────────────────────────────────────────────

    public function clients(Request $request): JsonResponse
    {
        $seller = $request->user('api')->seller;

        $clients = $seller->clients()
            ->with('user')
            ->get()
            ->map(fn($c) => [
                'id'         => $c->id,
                'unit_price' => $c->unit_price,
                'user'       => $c->user->only('id', 'name', 'email', 'phone', 'is_active'),
            ]);

        return response()->json($clients);
    }

    public function createClient(Request $request): JsonResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|max:50',
            'password'   => 'required|string|min:8',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $seller = $request->user('api')->seller;

        $client = DB::transaction(function () use ($request, $seller) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'role'     => User::ROLE_CLIENT,
                'password' => Hash::make($request->password),
            ]);

            return Client::create([
                'user_id'    => $user->id,
                'seller_id'  => $seller->id,
                'unit_price' => $request->unit_price,
            ]);
        });

        return response()->json($client->load('user'), 201);
    }

    public function updateClient(Request $request, int $clientId): JsonResponse
    {
        $seller = $request->user('api')->seller;
        $client = $seller->clients()->findOrFail($clientId);

        $request->validate([
            'name'       => 'sometimes|string|max:255',
            'phone'      => 'nullable|string|max:50',
            'unit_price' => 'sometimes|numeric|min:0',
            'is_active'  => 'sometimes|boolean',
        ]);

        DB::transaction(function () use ($request, $client) {
            $client->user->update($request->only('name', 'phone', 'is_active'));
            $client->update($request->only('unit_price'));
        });

        return response()->json($client->fresh()->load('user'));
    }

    public function deleteClient(Request $request, int $clientId): JsonResponse
    {
        $seller = $request->user('api')->seller;
        $client = $seller->clients()->findOrFail($clientId);
        $client->user->delete();

        return response()->json(['message' => 'Client supprimé.']);
    }

    // ─── Stocks et Allocations reçues ────────────────────────────────────────

    public function myAllocations(Request $request): JsonResponse
    {
        $seller = $request->user('api')->seller;

        $allocations = $seller->allocations()
            ->with('weeklyStock.supplier.user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($allocations);
    }

    // ─── Commandes passées au Fournisseur ────────────────────────────────────

    public function placeOrder(Request $request): JsonResponse
    {
        $request->validate([
            'allocation_id' => 'required|integer|exists:seller_allocations,id',
            'quantity'      => 'required|integer|min:1',
        ]);

        $seller = $request->user('api')->seller;

        $allocation = $seller->allocations()
            ->with('weeklyStock')
            ->findOrFail($request->allocation_id);

        if ($allocation->remaining_qty < $request->quantity) {
            return response()->json([
                'message' => 'Quantité demandée supérieure au stock restant (' . $allocation->remaining_qty . ').',
            ], 422);
        }

        $order = DB::transaction(function () use ($request, $seller, $allocation) {
            $allocation->decrement('remaining_qty', $request->quantity);

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'buyer_id'     => $seller->user_id,
                'supplier_id'  => $seller->supplier_id,
                'week_start'   => $allocation->weeklyStock->week_start,
                'quantity'     => $request->quantity,
                'unit_price'   => $allocation->weeklyStock->unit_price,
                'status'       => Order::STATUS_CONFIRMED,
            ]);

            Payment::create([
                'order_id'  => $order->id,
                'payer_id'  => $seller->user_id,
                'amount'    => $order->total_amount,
                'status'    => Payment::STATUS_PENDING,
                'due_date'  => now()->addDays(30),
            ]);

            return $order;
        });

        return response()->json($order->load('payment'), 201);
    }

    // ─── Suivi des paiements ─────────────────────────────────────────────────

    public function payments(Request $request): JsonResponse
    {
        $seller = $request->user('api')->seller;

        $ownPayments = Payment::where('payer_id', $seller->user_id)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->get();

        $clientPayments = Payment::whereHas('order', fn($q) => $q->where('seller_id', $seller->id))
            ->with(['order', 'payer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'my_payments_to_supplier' => $ownPayments,
            'client_payments_to_me'   => $clientPayments,
        ]);
    }

    public function updateClientPaymentStatus(Request $request, int $paymentId): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,paid,late,disputed',
            'notes'  => 'nullable|string',
        ]);

        $seller = $request->user('api')->seller;

        $payment = Payment::whereHas('order', fn($q) => $q->where('seller_id', $seller->id))
            ->findOrFail($paymentId);

        $payment->update([
            'status'  => $request->status,
            'notes'   => $request->notes,
            'paid_at' => $request->status === 'paid' ? now() : $payment->paid_at,
        ]);

        return response()->json($payment->fresh());
    }

    // ─── Dashboard ───────────────────────────────────────────────────────────

    public function dashboard(Request $request): JsonResponse
    {
        $seller = $request->user('api')->seller;

        $totalStock = $seller->allocations()->sum('remaining_qty');

        return response()->json([
            'total_clients'               => $seller->clients()->count(),
            'active_clients'              => $seller->clients()->whereHas('user', fn($q) => $q->where('is_active', true))->count(),
            'total_remaining_stock'       => $totalStock,
            'my_pending_payments'         => Payment::where('payer_id', $seller->user_id)->where('status', 'pending')->count(),
            'client_payments_pending'     => Payment::whereHas('order', fn($q) => $q->where('seller_id', $seller->id))->where('status', 'pending')->count(),
            'client_payments_late'        => Payment::whereHas('order', fn($q) => $q->where('seller_id', $seller->id))->where('status', 'late')->count(),
        ]);
    }
}
