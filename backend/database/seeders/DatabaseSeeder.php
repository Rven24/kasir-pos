<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users
        $owner = User::create([
            'name' => 'Hendra Wijaya (Owner)',
            'email' => 'owner@kasirku.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);

        $admin = User::create([
            'name' => 'Siti Rahma (Admin Toko)',
            'email' => 'admin@kasirku.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $kasir = User::create([
            'name' => 'Budi Santoso (Kasir Shift 1)',
            'email' => 'kasir@kasirku.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
        ]);

        // 2. Settings
        $settings = [
            'store_name' => 'KasirKu Mart & Café',
            'store_tagline' => 'Smart & Fast Point-of-Sale System',
            'store_address' => 'Jl. Jend. Sudirman No. 45, Jakarta Pusat',
            'store_phone' => '0812-3456-7890',
            'store_email' => 'contact@kasirku.my.id',
            'receipt_footer' => "Terima kasih atas kunjungan Anda!\nBarang yang sudah dibeli tidak dapat ditukar.\nLayanan Pelanggan: 0812-3456-7890",
            'tax_percentage' => '11',
            'currency_symbol' => 'Rp',
            'qris_merchant_name' => 'KASIRKU MART & CAFE',
            'qris_merchant_id' => 'NMID1029384756',
        ];

        foreach ($settings as $key => $value) {
            Setting::create(['key' => $key, 'value' => $value]);
        }

        // 3. Categories
        $catMakanan = Category::create(['name' => 'Makanan Siap Saji', 'slug' => 'makanan-siap-saji']);
        $catMinuman = Category::create(['name' => 'Minuman & Kopi', 'slug' => 'minuman-dan-kopi']);
        $catSnack = Category::create(['name' => 'Snack & Camilan', 'slug' => 'snack-dan-camilan']);
        $catSembako = Category::create(['name' => 'Sembako & Dapur', 'slug' => 'sembako-dan-dapur']);
        $catRumah = Category::create(['name' => 'Perawatan & Rumah', 'slug' => 'perawatan-dan-rumah']);

        // 4. Products
        $productsData = [
            // Makanan
            [
                'category_id' => $catMakanan->id,
                'name' => 'Nasi Goreng Spesial KasirKu',
                'sku' => 'MKN-001',
                'barcode' => '899100100001',
                'buy_price' => 15000,
                'sell_price' => 25000,
                'stock' => 45,
                'min_stock_alert' => 10,
                'image' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=400&auto=format&fit=crop&q=60',
                'description' => 'Nasi goreng dengan telur mata sapi, suwiran ayam, dan kerupuk renyah.',
            ],
            [
                'category_id' => $catMakanan->id,
                'name' => 'Mie Goreng Seafood Telur',
                'sku' => 'MKN-002',
                'barcode' => '899100100002',
                'buy_price' => 16000,
                'sell_price' => 28000,
                'stock' => 30,
                'min_stock_alert' => 8,
                'image' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=400&auto=format&fit=crop&q=60',
                'description' => 'Mie goreng kenyal dengan udang, cumi, dan telur dadar iris.',
            ],
            [
                'category_id' => $catMakanan->id,
                'name' => 'Ayam Geprek Sambal Korek',
                'sku' => 'MKN-003',
                'barcode' => '899100100003',
                'buy_price' => 12000,
                'sell_price' => 20000,
                'stock' => 4, // LOW STOCK ALERT
                'min_stock_alert' => 10,
                'image' => 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=400&auto=format&fit=crop&q=60',
                'description' => 'Ayam goreng renyah dipadu sambal korek pedas nikmat.',
            ],

            // Minuman
            [
                'category_id' => $catMinuman->id,
                'name' => 'Kopi Susu Gula Aren 250ml',
                'sku' => 'MNM-001',
                'barcode' => '899200200001',
                'buy_price' => 8000,
                'sell_price' => 18000,
                'stock' => 60,
                'min_stock_alert' => 15,
                'image' => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=400&auto=format&fit=crop&q=60',
                'description' => 'Espresso robusta & arabika blend dengan susu segar dan gula aren murni.',
            ],
            [
                'category_id' => $catMinuman->id,
                'name' => 'Matcha Latte Iced',
                'sku' => 'MNM-002',
                'barcode' => '899200200002',
                'buy_price' => 10000,
                'sell_price' => 22000,
                'stock' => 25,
                'min_stock_alert' => 10,
                'image' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=400&auto=format&fit=crop&q=60',
                'description' => 'Bubuk matcha Kyoto asli berpadu lembutnya fresh milk.',
            ],
            [
                'category_id' => $catMinuman->id,
                'name' => 'Air Mineral Botol 600ml',
                'sku' => 'MNM-003',
                'barcode' => '899200200003',
                'buy_price' => 2500,
                'sell_price' => 5000,
                'stock' => 120,
                'min_stock_alert' => 24,
                'image' => 'https://images.unsplash.com/photo-1548839140-29a749e1bc4e?w=400&auto=format&fit=crop&q=60',
                'description' => 'Air mineral pegunungan segar dan higienis.',
            ],
            [
                'category_id' => $catMinuman->id,
                'name' => 'Teh Tarik Klasik Hangat',
                'sku' => 'MNM-004',
                'barcode' => '899200200004',
                'buy_price' => 4500,
                'sell_price' => 12000,
                'stock' => 2, // CRITICAL LOW STOCK
                'min_stock_alert' => 15,
                'image' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?w=400&auto=format&fit=crop&q=60',
                'description' => 'Teh hitam kental ditarik berbuih dengan krimer manis.',
            ],

            // Snack
            [
                'category_id' => $catSnack->id,
                'name' => 'Keripik Singkong Balado 150g',
                'sku' => 'SNK-001',
                'barcode' => '899300300001',
                'buy_price' => 7000,
                'sell_price' => 14000,
                'stock' => 50,
                'min_stock_alert' => 12,
                'image' => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&auto=format&fit=crop&q=60',
                'description' => 'Keripik singkong renyah dengan taburan bumbu balado pedas manis.',
            ],
            [
                'category_id' => $catSnack->id,
                'name' => 'Kacang Almond Panggang 100g',
                'sku' => 'SNK-002',
                'barcode' => '899300300002',
                'buy_price' => 18000,
                'sell_price' => 32000,
                'stock' => 18,
                'min_stock_alert' => 8,
                'image' => 'https://images.unsplash.com/photo-1508061253366-f7da158b6d46?w=400&auto=format&fit=crop&q=60',
                'description' => 'Almond utuh premium dipanggang tanpa minyak berlebih.',
            ],
            [
                'category_id' => $catSnack->id,
                'name' => 'Cokelat Bar Hazelnut 65g',
                'sku' => 'SNK-003',
                'barcode' => '899300300003',
                'buy_price' => 11000,
                'sell_price' => 19500,
                'stock' => 3, // LOW STOCK ALERT
                'min_stock_alert' => 10,
                'image' => 'https://images.unsplash.com/photo-1511381939415-e44015466834?w=400&auto=format&fit=crop&q=60',
                'description' => 'Cokelat susu lembut bertabur butiran kacang hazelnut gurih.',
            ],

            // Sembako
            [
                'category_id' => $catSembako->id,
                'name' => 'Beras Pandan Wangi 5kg',
                'sku' => 'SMB-001',
                'barcode' => '899400400001',
                'buy_price' => 65000,
                'sell_price' => 78000,
                'stock' => 35,
                'min_stock_alert' => 10,
                'image' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&auto=format&fit=crop&q=60',
                'description' => 'Beras pulen beraroma pandan alami, bersih tanpa pemutih.',
            ],
            [
                'category_id' => $catSembako->id,
                'name' => 'Minyak Goreng Pouch 2L',
                'sku' => 'SMB-002',
                'barcode' => '899400400002',
                'buy_price' => 28000,
                'sell_price' => 34500,
                'stock' => 40,
                'min_stock_alert' => 12,
                'image' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=400&auto=format&fit=crop&q=60',
                'description' => 'Minyak kelapa sawit murni berkualitas tinggi dua kali penyaringan.',
            ],
            [
                'category_id' => $catSembako->id,
                'name' => 'Gula Pasir Kristal Putih 1kg',
                'sku' => 'SMB-003',
                'barcode' => '899400400003',
                'buy_price' => 14000,
                'sell_price' => 17500,
                'stock' => 25,
                'min_stock_alert' => 10,
                'image' => 'https://images.unsplash.com/photo-1581441363689-1f3c3c414635?w=400&auto=format&fit=crop&q=60',
                'description' => 'Gula tebu murni butiran kristal putih manis alami.',
            ],

            // Perawatan & Rumah
            [
                'category_id' => $catRumah->id,
                'name' => 'Sabun Cuci Piring Jeruk Nipis 750ml',
                'sku' => 'RMH-001',
                'barcode' => '899500500001',
                'buy_price' => 11000,
                'sell_price' => 15500,
                'stock' => 30,
                'min_stock_alert' => 8,
                'image' => 'https://images.unsplash.com/photo-1585670270608-b4be4fb88092?w=400&auto=format&fit=crop&q=60',
                'description' => 'Cairan pencuci piring formula anti lemak aroma jeruk nipis segar.',
            ],
            [
                'category_id' => $catRumah->id,
                'name' => 'Tissue Wajah Lembut 250 sheets',
                'sku' => 'RMH-002',
                'barcode' => '899500500002',
                'buy_price' => 9000,
                'sell_price' => 13000,
                'stock' => 50,
                'min_stock_alert' => 15,
                'image' => 'https://images.unsplash.com/photo-1584556812952-905ffd0c611a?w=400&auto=format&fit=crop&q=60',
                'description' => 'Tisu wajah 2 ply higienis dan halus di kulit.',
            ],
        ];

        $createdProducts = [];
        foreach ($productsData as $data) {
            $createdProducts[] = Product::create($data);
        }

        // 5. Sample Transactions for Analytics & Dashboard History
        $sampleTx = [
            [
                'invoice' => 'INV-20261111-0001',
                'customer' => 'Kak Dimas',
                'items' => [
                    ['p' => $createdProducts[0], 'qty' => 2], // Nasi Goreng
                    ['p' => $createdProducts[3], 'qty' => 2], // Kopi Susu
                ],
                'payment' => 'qris',
                'discount' => 5000,
                'hours_ago' => 4,
            ],
            [
                'invoice' => 'INV-20261111-0002',
                'customer' => 'Ibu Maya',
                'items' => [
                    ['p' => $createdProducts[10], 'qty' => 1], // Beras 5kg
                    ['p' => $createdProducts[11], 'qty' => 2], // Minyak 2L
                    ['p' => $createdProducts[12], 'qty' => 1], // Gula
                ],
                'payment' => 'cash',
                'discount' => 0,
                'hours_ago' => 3,
            ],
            [
                'invoice' => 'INV-20261111-0003',
                'customer' => 'Pelanggan Umum',
                'items' => [
                    ['p' => $createdProducts[1], 'qty' => 1], // Mie Goreng
                    ['p' => $createdProducts[4], 'qty' => 1], // Matcha Latte
                    ['p' => $createdProducts[7], 'qty' => 1], // Keripik Singkong
                ],
                'payment' => 'cash',
                'discount' => 0,
                'hours_ago' => 2,
            ],
            [
                'invoice' => 'INV-20261111-0004',
                'customer' => 'Mas Rizky',
                'items' => [
                    ['p' => $createdProducts[3], 'qty' => 3], // Kopi Susu
                    ['p' => $createdProducts[8], 'qty' => 1], // Kacang Almond
                ],
                'payment' => 'transfer',
                'discount' => 2000,
                'hours_ago' => 1,
            ],
        ];

        foreach ($sampleTx as $tx) {
            $subtotal = 0;
            $detailList = [];

            foreach ($tx['items'] as $item) {
                $p = $item['p'];
                $qty = $item['qty'];
                $itemSubtotal = $p->sell_price * $qty;
                $subtotal += $itemSubtotal;
                $detailList[] = [
                    'product_id' => $p->id,
                    'quantity' => $qty,
                    'unit_price' => $p->sell_price,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $discount = $tx['discount'];
            $taxable = max(0, $subtotal - $discount);
            $tax = round($taxable * 0.11);
            $total = $taxable + $tax;
            $pay = $tx['payment'] === 'cash' ? ceil($total / 50000) * 50000 : $total;
            if ($pay < $total) $pay = $total;
            $change = $pay - $total;

            $createdAt = Carbon::now()->subHours($tx['hours_ago']);

            $transaction = Transaction::create([
                'invoice_number' => $tx['invoice'],
                'user_id' => $kasir->id,
                'customer_name' => $tx['customer'],
                'total_amount' => $total,
                'discount' => $discount,
                'tax' => $tax,
                'pay_amount' => $pay,
                'change_amount' => $change,
                'payment_method' => $tx['payment'],
                'status' => 'completed',
                'notes' => 'Transaksi POS KasirKu Shift Pagi',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($detailList as $detail) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'subtotal' => $detail['subtotal'],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }
}
