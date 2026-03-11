<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Seller;
use App\Models\SellerAllocation;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WeeklyStock;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Fournisseur ──────────────────────────────────────────────────────
        $supplierUser = User::create([
            'name'      => 'Marc Dupont',
            'email'     => 'fournisseur@demo.com',
            'phone'     => '0600000001',
            'role'      => User::ROLE_SUPPLIER,
            'password'  => Hash::make('password'),
            'api_token' => Str::random(60),
        ]);

        $supplier = Supplier::create([
            'user_id' => $supplierUser->id,
            'company' => 'Dupont Distribution',
            'address' => '12 rue du Commerce, 75001 Paris',
            'siret'   => '12345678900001',
        ]);

        // ── Vendeurs ─────────────────────────────────────────────────────────
        $sellerUserA = User::create([
            'name'      => 'Alice Martin',
            'email'     => 'vendeur.alice@demo.com',
            'phone'     => '0600000002',
            'role'      => User::ROLE_SELLER,
            'password'  => Hash::make('password'),
            'api_token' => Str::random(60),
        ]);

        $sellerA = Seller::create([
            'user_id'     => $sellerUserA->id,
            'supplier_id' => $supplier->id,
            'unit_price'  => 5.00, // prix fournisseur → vendeur
        ]);

        $sellerUserB = User::create([
            'name'      => 'Bruno Leroy',
            'email'     => 'vendeur.bruno@demo.com',
            'phone'     => '0600000003',
            'role'      => User::ROLE_SELLER,
            'password'  => Hash::make('password'),
            'api_token' => Str::random(60),
        ]);

        $sellerB = Seller::create([
            'user_id'     => $sellerUserB->id,
            'supplier_id' => $supplier->id,
            'unit_price'  => 5.50,
        ]);

        // ── Clients ──────────────────────────────────────────────────────────
        $clientUser1 = User::create([
            'name'      => 'Claire Bernard',
            'email'     => 'client.claire@demo.com',
            'phone'     => '0600000004',
            'role'      => User::ROLE_CLIENT,
            'password'  => Hash::make('password'),
            'api_token' => Str::random(60),
        ]);

        $client1 = Client::create([
            'user_id'    => $clientUser1->id,
            'seller_id'  => $sellerA->id,
            'unit_price' => 7.00, // prix vendeur → client
        ]);

        $clientUser2 = User::create([
            'name'      => 'David Petit',
            'email'     => 'client.david@demo.com',
            'phone'     => '0600000005',
            'role'      => User::ROLE_CLIENT,
            'password'  => Hash::make('password'),
            'api_token' => Str::random(60),
        ]);

        $client2 = Client::create([
            'user_id'    => $clientUser2->id,
            'seller_id'  => $sellerA->id,
            'unit_price' => 7.50,
        ]);

        // ── Stock hebdomadaire ───────────────────────────────────────────────
        $weekStart = Carbon::now()->startOfWeek()->toDateString();

        $stock = WeeklyStock::create([
            'supplier_id'   => $supplier->id,
            'week_start'    => $weekStart,
            'total_qty'     => 1000,
            'available_qty' => 200, // 800 déjà alloués
            'unit_price'    => 5.00,
        ]);

        // ── Allocations ──────────────────────────────────────────────────────
        $allocA = SellerAllocation::create([
            'weekly_stock_id' => $stock->id,
            'seller_id'       => $sellerA->id,
            'allocated_qty'   => 400,
            'remaining_qty'   => 250, // 150 déjà commandés
        ]);

        SellerAllocation::create([
            'weekly_stock_id' => $stock->id,
            'seller_id'       => $sellerB->id,
            'allocated_qty'   => 400,
            'remaining_qty'   => 400,
        ]);

        // ── Commande Vendeur A → Fournisseur ─────────────────────────────────
        $orderSellerToSupplier = Order::create([
            'order_number' => 'ORD-DEMOSUP01',
            'buyer_id'     => $sellerUserA->id,
            'supplier_id'  => $supplier->id,
            'week_start'   => $weekStart,
            'quantity'     => 150,
            'unit_price'   => 5.00,
            'status'       => Order::STATUS_CONFIRMED,
        ]);

        Payment::create([
            'order_id'       => $orderSellerToSupplier->id,
            'payer_id'       => $sellerUserA->id,
            'amount'         => 750.00,
            'status'         => Payment::STATUS_PENDING,
            'due_date'       => now()->addDays(30),
            'payment_method' => 'virement',
        ]);

        // ── Commande Client 1 → Vendeur A ────────────────────────────────────
        $orderClientToSeller = Order::create([
            'order_number' => 'ORD-DEMOCLI01',
            'buyer_id'     => $clientUser1->id,
            'seller_id'    => $sellerA->id,
            'week_start'   => $weekStart,
            'quantity'     => 50,
            'unit_price'   => 7.00,
            'status'       => Order::STATUS_DELIVERED,
        ]);

        Payment::create([
            'order_id'       => $orderClientToSeller->id,
            'payer_id'       => $clientUser1->id,
            'amount'         => 350.00,
            'status'         => Payment::STATUS_PAID,
            'due_date'       => now()->subDays(5),
            'paid_at'        => now()->subDays(2),
            'payment_method' => 'espèces',
        ]);

        $this->command->info('✅ Données de démonstration créées avec succès.');
        $this->command->info('');
        $this->command->info('Comptes de démonstration :');
        $this->command->info('  Fournisseur : fournisseur@demo.com / password');
        $this->command->info('  Vendeur A   : vendeur.alice@demo.com / password');
        $this->command->info('  Vendeur B   : vendeur.bruno@demo.com / password');
        $this->command->info('  Client 1    : client.claire@demo.com / password');
        $this->command->info('  Client 2    : client.david@demo.com / password');
    }
}
