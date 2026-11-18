<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Services\FirebaseService;
use App\Services\QrisService;
use App\Services\ShippingService;
use App\Models\Promotion;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {

        $timeNow = now()->format('H:i');

        $merchant = DB::table('merchants')
        ->where('id', $request->input('merchantId'))->first();

        if ($timeNow < $merchant->open) {
            return response()->json([
                'message' => 'Mohon Maaf, order belum dibuka'
            ]);
        }
        
        if ($timeNow > $merchant->closed) {
            return response()->json([
                'message' => 'Mohon Maaf, order sudah ditutup'
            ]);
        }

        $productId = $request->input('id');
        $productName = $request->input('name');
        $productPrice = $request->input('price');
        $quantity = $request->input('quantity', 1);
        $img = $request->input('productImage');
        $weight = $request->input('weight');

        //get cart
        $cart = session()->get('cart', []);

        //sudah ada di cart
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            //produk baru ke cart
            $cart[$productId] = [
                'product_id' => $productId,
                'product_name' => $productName,
                'price' => $productPrice,
                'quantity' => $quantity,
                'product_discount' => '0',
                'promotion_id' => '',
                'note' => '',
                'weight' => $weight,
                'image' => $img,
            ];
        }

        session()->put('cart', $cart);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan ke keranjang.',
            'cart' => $cart,
        ]);
    }

    public function viewCart()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect('/')
                ->with('error', 'Keranjang belanja Anda kosong.');
        }

        // Ambil customer yang sedang login
        $customer = Auth::guard('customer')->user();

        // Ambil alamat customer
        $addresses = $customer
            ? $customer->addresses()
                ->orderByDesc('is_default')
                ->latest()
                ->get()
            : collect();

        // Ambil merchant
        $merchant = DB::table('merchants')->first();

        $cartCount = count($cart);

        // Hit API Merchant
        $merchant = DB::table('merchants')->first();
        $cartCount = count($cart);

        //RESET PROMO
        foreach ($cart as $key => $item) {
            $cart[$key]['promotion_id'] = '';
            $cart[$key]['product_discount'] = 0;
            session()->put('cart', $cart);
        }

        // GET PROMO
        $promotions = Promotion::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_at')
                    ->orWhereTime('start_at', '<=', now()->format('H:i:s'));
            })
            ->where(function ($query) {
                $query->whereNull('end_at')
                    ->orWhereTime('end_at', '>=', now()->format('H:i:s'));
            })
            ->with(['products', 'categories'])
            ->get();



        foreach ($promotions as $promotion) {
            foreach ($cart as $key => $item) {
                $product = Product::find($item['product_id']);

                if (!$product) {
                    continue;
                }

                // Cek apakah produk memenuhi promo
                if (!$this->isApplicablePromo($promotion, $product)) {
                    continue;
                }

                if ($promotion->type === 'percentage') {
                    $itemDiscount = $item['price'] * ($promotion->value / 100);
                } elseif ($promotion->discount_type === 'fixed') {
                    // $itemDiscount =
                    //     min($promotion->promo_value, $itemTotal);
                } else {
                    $itemDiscount = 0;
                }

                $cart[$key]['promotion_id'] = $promotion->id;
                $cart[$key]['product_discount'] = $itemDiscount;
                session()->put('cart', $cart);

            }
        }
            

        return response()
        ->view('home-page.cart', compact(
            'cart',
            'merchant',
            'cartCount',
            'addresses',
            'customer'
        ))
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
    }

    private function isApplicablePromo($promotion, $product)
    {
        if ($promotion->scope === 'all') {
            return true;
        }

        if ($promotion->scope === 'product') {
            return $promotion->products
                ->contains('product_id', $product->id);
        }

        if ($promotion->scope === 'category') {
            return $promotion->categories
                ->contains('category_id', $product->category_id);
        }

        return false;
    }

    public function remove($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return redirect()
        ->route('cart.view')
        ->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    public function update(Request $request, $id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = $request->quantity;
            
            session()->put('cart', $cart);

            $itemTotal = $cart[$id]['price'] * $cart[$id]['quantity'];
            $itemDiscountTotal = $cart[$id]['product_discount'] * $cart[$id]['quantity'];

            $grandTotal = 0;
            $resDiscount = 0;
            foreach ($cart as $item) {
                $grandTotal += $item['price'] * $item['quantity'];
                $resDiscount += $item['product_discount'] * $item['quantity'];

            }

            $total = $grandTotal - $resDiscount;

            return response()->json([
                'success' => true,
                'itemTotal' => number_format($itemTotal, 0, ',', '.'),
                'productDiscountTotal' => number_format($itemDiscountTotal, 0, ',', '.'),
                'grandTotal' => number_format($grandTotal, 0, ',', '.'),
                'discount' => number_format($resDiscount, 0, ',', '.'),
                'total' => number_format($total, 0, ',', '.')
            ]);
        }

        return response()->json(['success' => false]);
    }

    public function updateNote(Request $request, $id)
    {
        $request->validate([
            'note' => 'nullable|string|max:255',
        ]);

        $cart = session()->get('cart', []);

        if (!isset($cart[$id])) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan di cart.'
            ], 404);
        }

        $cart[$id]['note'] = $request->note;

        session()->put('cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Catatan berhasil disimpan.'
        ]);
    }

    
    public function checkout(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        // Pastikan customer sudah login
        if (!$customer) {
            return redirect()->route('customer.login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Validasi alamat pengiriman
        $request->validate([
            'address_id'       => ['required', 'integer'],
            'shipping_courier' => ['required', 'string', 'max:45'],
            'shipping_service' => ['required', 'string', 'max:45'],
        ]);

        // Pastikan alamat benar-benar milik customer yang login
        $address = DB::table('customer_addresses')
            ->where('id', $request->address_id)
            ->where('customer_id', $customer->id)
            ->first();

        if (!$address) {
            return back()->with('error', 'Alamat pengiriman tidak valid.');
        }

        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect('/')
                ->with('error', 'Keranjang belanja kosong.');
        }

        try {
            $merchant = DB::table('merchants')->first();

            if (!$merchant) {
                throw new \Exception('Data merchant tidak ditemukan.');
            }

            $invoiceNumber = 'ORDER' . Carbon::now()->format('YmdHis');

            $details = [];
            $subtotal = 0;
            $subtotalDiscount = 0;
            $promotionId = null;

            // Hitung subtotal dan diskon
            foreach ($cart as $id => $item) {
                $qty = (int) ($item['quantity'] ?? 0);
                $price = (int) ($item['price'] ?? 0);
                $discount = (int) ($item['product_discount'] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                $itemSubtotal = $qty * $price;
                $itemDiscount = $qty * $discount;

                $subtotal += $itemSubtotal;
                $subtotalDiscount += $itemDiscount;

                if (!empty($item['promotion_id'])) {
                    $promotionId = $item['promotion_id'];
                }

                $details[] = [
                    'product_id'   => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'price'        => $price,
                    'quantity'     => $qty,
                    'discount'     => $itemDiscount,
                    'subtotal'     => $itemSubtotal,
                    'note'         => $item['note'] ?? '',
                ];
            }

            if (empty($details)) {
                return redirect('/')
                    ->with('error', 'Tidak ada produk valid di keranjang.');
            }

            

            // ==================================
            // VALIDASI TOTAL PRODUK
            // ==================================

            if ($subtotal - $subtotalDiscount < 0) {
                throw new \Exception('Total transaksi tidak valid.');
            }

            // ==================================
            // HITUNG BERAT KERANJANG
            // ==================================

            $shippingWeight = 0;

            foreach ($cart as $item) {
                $qty = (int) ($item['quantity'] ?? 0);
                $weight = (int) ($item['weight'] ?? 0);

                if ($qty > 0) {
                    $shippingWeight += $qty * $weight;
                }
            }

            if ($shippingWeight <= 0) {
                throw new \Exception('Berat produk tidak valid.');
            }

            // ==================================
            // DATA PENGIRIMAN
            // ==================================

            $shippingCourier = strtolower(trim(
                $request->shipping_courier
            ));

            $shippingService = trim(
                $request->shipping_service
            );

            $origin = (int) config('services.rajaongkir.origin_id');
            $destination = (int) ($address->district_id ?? 0);

            if ($origin <= 0) {
                throw new \Exception('Origin ID RajaOngkir belum dikonfigurasi.');
            }

            if ($destination <= 0) {
                throw new \Exception('District ID alamat tujuan tidak tersedia.');
            }

            // ==================================
            // HITUNG ULANG ONGKIR RAJAONGKIR
            // ==================================

            $shippingOptions = app(ShippingService::class)->calculate(
                $origin,
                $destination,
                $shippingWeight
            );

            // Cari layanan sesuai pilihan customer
            $selectedShipping = collect($shippingOptions)->first(
                function ($option) use ($shippingCourier, $shippingService) {

                    return strtolower(trim($option['code'] ?? '')) === $shippingCourier
                        && strtolower(trim($option['service'] ?? '')) === strtolower($shippingService);
                }
            );

            if (!$selectedShipping) {
                throw new \Exception(
                    'Layanan pengiriman tidak ditemukan. Silakan kembali ke keranjang dan pilih ulang kurir.'
                );
            }

            // Ambil biaya aktual dari RajaOngkir
            $shippingCost = (int) ($selectedShipping['cost'] ?? 0);

            if ($shippingCost <= 0) {
                throw new \Exception('Biaya pengiriman tidak valid.');
            }

            // Ambil estimasi pengiriman dari API
            $shippingEtd = $selectedShipping['etd'] ?? null;

            // ==================================
            // HITUNG GRAND TOTAL
            // ==================================

            $grandTotal = $subtotal - $subtotalDiscount + $shippingCost;

            // ==================================
            // GENERATE QRIS DYNAMIC
            // ==================================

            $qrisDynamic = QrisService::makeDynamicQR(
                $merchant->qris_info,
                $grandTotal
            );

            $now = Carbon::now();

            // Simpan transaksi dan detail secara atomik
            $transactionId = DB::transaction(function () use (
                $invoiceNumber,
                $customer,
                $address,
                $details,
                $subtotal,
                $subtotalDiscount,
                $promotionId,
                $shippingCost,
                $shippingCourier,
                $shippingService,
                $shippingEtd,
                $shippingWeight,
                $grandTotal,
                $qrisDynamic,
                $now
            ) {
                // Simpan transaksi
                $transactionId = DB::table('transactions')->insertGetId([
                    'invoice_number'       => $invoiceNumber,
                    'source'               => 'WEB ORDER',

                    // Identitas customer
                    'customer_id'          => $customer->id,

                    // Snapshot alamat pengiriman
                    'shipping_recipient'   => $address->recipient_name,
                    'shipping_phone'       => $address->recipient_phone,
                    'shipping_address'     => $address->address,
                    'shipping_city'        => $address->city,
                    'shipping_postal_code' => $address->postal_code,
                    'shipping_latitude'    => $address->latitude,
                    'shipping_longitude'   => $address->longitude,

                    // Pengiriman
                    'shipping_courier' => $shippingCourier,
                    'shipping_service' => $shippingService,
                    'shipping_etd'     => $shippingEtd,
                    'shipping_weight'  => $shippingWeight,
                    'shipping_cost'    => $shippingCost,
                    'tracking_number'  => null,

                    'cashier_id'           => null,
                    'qr_code'              => $qrisDynamic,

                    'subtotal'             => $subtotal,
                    'discount'             => $subtotalDiscount,
                    'tax'                  => 0,
                    'service_charge'       => 0,
                    'grand_total'          => $grandTotal,

                    'payment_status'       => 'UNPAID',
                    'payment_method'       => 'QRIS',
                    'payment_proof'        => null,

                    'order_status'         => 'UNPAID',

                    'notes'                => '',
                    'promotion_id'         => $promotionId,
                    'promotion_code'       => null,

                    'created_at'           => $now,
                    'updated_at'           => $now,
                ]);

                // Simpan detail transaksi
                foreach ($details as $item) {
                    DB::table('transaction_items')->insert([
                        'transaction_id' => $transactionId,
                        'product_id'     => $item['product_id'],
                        'product_name'   => $item['product_name'],
                        'price'          => $item['price'],
                        'quantity'       => $item['quantity'],
                        'discount'       => $item['discount'],
                        'subtotal'       => $item['subtotal'],
                        'note'           => $item['note'],
                        'created_at'     => $now,
                    ]);
                }

                // Catat penggunaan promo
                if ($promotionId) {
                    DB::table('promotion_usages')->insert([
                        'promotion_id'    => $promotionId,
                        'transaction_id'  => $transactionId,
                        'customer_id'     => $customer->id,
                        'discount_amount' => $subtotalDiscount,
                        'used_at'         => $now,
                    ]);
                }

                return $transactionId;
            });

            // Notifikasi FCM setelah transaksi berhasil disimpan
            try {
                $admin = User::where('role', 'kasir')->first();

                if ($admin && !empty($admin->fcm_token)) {
                    $firebase = app(FirebaseService::class);

                    $firebase->sendToToken(
                        $admin->fcm_token,
                        'Order Baru',
                        'Ada order baru dari ' . $customer->name,
                        [
                            'type'        => 'NEW_ORDER',
                            'idTransaksi' => (string) $transactionId,
                            'customer'    => $customer->name,
                            'status'      => 'UNPAID',
                        ]
                    );
                } else {
                    Log::warning('Admin kasir atau FCM token tidak tersedia.');
                }
            } catch (\Throwable $e) {
                Log::error('Gagal kirim FCM', [
                    'error' => $e->getMessage(),
                ]);
            }

            // Kosongkan cart setelah transaksi berhasil
            session()->forget('cart');

            return redirect()->route('customer.orders.detail', [
                'id' => $transactionId,
                'from' => 'checkout'
            ]);

        } catch (\Throwable $e) {
            Log::error('Gagal proses checkout', [
                'error' => $e->getMessage(),
                'customer_id' => $customer->id,
            ]);

            return back()->with(
                'error',
                'Gagal melakukan checkout. Silakan coba kembali.'
            );
        }
    }


    public function success($id)
    {
        try{

            $transaction = DB::table('transactions')->where('transaction_id', $id)->first();

            if (!$transaction) {
                abort(404);
            }

            $merchant = DB::table('merchants')->first();
            $phone_wa = $merchant->phone; 

            // Ambil data dari request
            $nama = $transaction->customer;
            $meja = $transaction->table_id;
            $qrisDynamic = $transaction->qr_code;
            $totalTagihan = $transaction->subtotal;
            $details = DB::table('transaction_items')->where('transaction_id', $id)->get();

            if($transaction->payment_method == 'qris'){

                $textHeading = 'Order berhasil dibuat!';
                $textBody = 'Segera lakukan pembayaran untuk proses pemesanan makanan!';

                return view('home-page.checkout', [
                    'phone_wa' => $phone_wa,
                    'cartCount' => 0,
                    'qrisDynamic' => $qrisDynamic,
                    'isQRIS' => true,
                    'head' => $textHeading,
                    'body' => $textBody,
                    'nama' => $nama,
                    'meja' => $meja,
                    'metodePembayaran' => 'QRIS',
                    'discount' => $transaction->discount,
                    'totalTagihan' => $totalTagihan,
                    'details' => $details,
                    'idtransaksi' => $id,
                    'invoiceNumber' => $transaction->invoice_number
                ]);

            }else{
                $textHeading = 'Order berhasil dibuat!';
                $textBody = 'Segera lakukan pembayaran dikasir!';

                return view('home-page.checkout', [
                    'phone_wa' => $phone_wa,
                    'cartCount' => 0,
                    'qrisImage' => $qrisImage,
                    'isQRIS' => false,
                    'head' => $textHeading,
                    'body' => $textBody,
                    'nama' => $nama,
                    'meja' => $meja,
                    'metodePembayaran' => 'TUNAI',
                    'discount' => $transaction->discount,
                    'totalTagihan' => $totalTagihan,
                    'details' => $details,
                    'idtransaksi' => $id,
                    'invoiceNumber' => $transaction->invoice_number
                ]);
            }

        }catch (\Exception $e) {
            Log::error('Gagal proses data', [
                'error' => $e->getMessage()
            ]);
    
            return back()->with(
                'error',
                'Gagal melakukan checkout. Silakan coba kembali.'
            );
        }
    }

    public function upload(Request $request)
    {
        $request->validate([
            'bukti_pembayaran' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'idtransaksi' => 'required|integer',
        ]);

        $tmp = null;
        $source = null;
        $filename = 'buktitransfer_' . $request->input('idtransaksi') . '.jpg';
        $directory = public_path('invoice');
        $outputPath = $directory . DIRECTORY_SEPARATOR . $filename;

        try {
            $image = $request->file('bukti_pembayaran');
            $imagePath = $image->getRealPath();
            $mimeType = $image->getMimeType();

            // Pastikan gambar valid.
            $imageInfo = getimagesize($imagePath);

            if ($imageInfo === false) {
                throw new \RuntimeException('File gambar tidak valid.');
            }

            $widthOrig = $imageInfo[0];
            $heightOrig = $imageInfo[1];

            if ($widthOrig <= 0 || $heightOrig <= 0) {
                throw new \RuntimeException('Dimensi gambar tidak valid.');
            }

            // Batas dimensi maksimal: 600 x 1000, tanpa memperbesar gambar.
            $scale = min(
                600 / $widthOrig,
                1000 / $heightOrig,
                1
            );

            $width = max(1, (int) round($widthOrig * $scale));
            $height = max(1, (int) round($heightOrig * $scale));

            // Baca gambar sumber.
            if ($mimeType === 'image/jpeg') {
                $source = imagecreatefromjpeg($imagePath);
            } elseif ($mimeType === 'image/png') {
                /*
                * Abaikan hanya warning iCCP tertentu.
                * Warning PHP lainnya tetap diteruskan.
                */
                set_error_handler(function (
                    $severity,
                    $message,
                    $file,
                    $line
                ) {
                    if (strpos(
                        $message,
                        'iCCP: known incorrect sRGB profile'
                    ) !== false) {
                        return true;
                    }

                    return false;
                });

                try {
                    $source = imagecreatefrompng($imagePath);
                } finally {
                    restore_error_handler();
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Format gambar tidak didukung.',
                ], 422);
            }

            if ($source === false) {
                throw new \RuntimeException('Gagal membaca gambar.');
            }

            // Buat kanvas JPEG dengan latar belakang putih.
            $tmp = imagecreatetruecolor($width, $height);

            if ($tmp === false) {
                throw new \RuntimeException('Gagal membuat kanvas gambar.');
            }

            $white = imagecolorallocate($tmp, 255, 255, 255);
            imagefill($tmp, 0, 0, $white);

            // Resize gambar sesuai dimensi yang dihitung.
            if (!imagecopyresampled(
                $tmp,
                $source,
                0,
                0,
                0,
                0,
                $width,
                $height,
                $widthOrig,
                $heightOrig
            )) {
                throw new \RuntimeException('Gagal mengubah ukuran gambar.');
            }

            // Tambahkan watermark.
            $font = public_path('arial.ttf');

            if (!file_exists($font)) {
                throw new \RuntimeException('File font arial.ttf tidak ditemukan.');
            }

            $fontSize = 12;
            $textColor = imagecolorallocate($tmp, 0, 0, 0);
            $timestamp = 'fivestar : ' .
                Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');

            imagettftext(
                $tmp,
                $fontSize,
                0,
                20,
                min(50, $height - 5),
                $textColor,
                $font,
                $timestamp
            );

            // Pastikan direktori tujuan tersedia.
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                    throw new \RuntimeException(
                        'Gagal membuat direktori invoice.'
                    );
                }
            }

            if (!is_writable($directory)) {
                throw new \RuntimeException(
                    'Direktori invoice tidak dapat ditulisi.'
                );
            }

            // Semua output disimpan sebagai JPEG.
            if (!imagejpeg($tmp, $outputPath, 80)) {
                throw new \RuntimeException('Gagal menyimpan gambar.');
            }

            // Bersihkan resource GD.
            imagedestroy($tmp);
            $tmp = null;

            imagedestroy($source);
            $source = null;

            // Update transaksi setelah gambar berhasil disimpan.
            $updated = DB::table('transactions')
                ->where('transaction_id', $request->input('idtransaksi'))
                ->update([
                    'payment_proof' => $filename,
                    'order_status' => 'PAID',
                    'payment_status' => 'PAID',
                    'updated_at' => Carbon::now('Asia/Jakarta')
                        ->format('Y-m-d H:i:s'),
                ]);

            $transaction = DB::table('transactions')
                ->where('transaction_id', $request->input('idtransaksi'))
                ->first();

            if (!$transaction) {
                throw new \RuntimeException('Transaksi tidak ditemukan.');
            }

            // Notifikasi FCM tidak boleh menggagalkan upload.
            try {
                $admin = User::where('role', 'kasir')->first();

                if (!$admin) {
                    Log::warning('Admin kasir tidak ditemukan');
                } elseif (empty($admin->fcm_token)) {
                    Log::warning('FCM token admin kosong', [
                        'admin_id' => $admin->id,
                    ]);
                } else {
                    $firebase = app(FirebaseService::class);

                    $firebase->sendToToken(
                        $admin->fcm_token,
                        'Order Sudah Di Bayar',
                        'Ada order baru sudah di bayar dari ' . $transaction->customer,
                        [
                            'type' => 'NEW_ORDER',
                            'idTransaksi' => (string) $transaction->transaction_id,
                            'customer' => $transaction->customer,
                            'meja' => (string) $transaction->table_id,
                            'status' => $transaction->payment_status,
                        ]
                    );
                }
            } catch (\Throwable $e) {
                Log::error('Gagal kirim FCM', [
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'imageUrl' => url('webkopinggir/public/invoice/' . $filename),
            ]);

        } catch (\Throwable $e) {
            Log::error('Gagal upload bukti pembayaran', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupload file.',
            ], 500);

        } finally {
            if ($tmp !== null && $tmp !== false) {
                imagedestroy($tmp);
            }

            if ($source !== null && $source !== false) {
                imagedestroy($source);
            }
        }
    }
    
    public function status($invoice)
    {
        $merchant = DB::table('merchants')->first();
        $phone_wa = $merchant->phone; 

        $transaction = Transaction::where(
            'invoice_number',
            $invoice
        )->firstOrFail();

        $details = DB::table('transaction_items')->where('transaction_id', $transaction->transaction_id)->get();
        
        $pesanDetail= '';
        
        foreach ($details as $item) {
            $pesanDetail = $pesanDetail. $item->product_name.' ['.$item->quantity.'] Rp '.($item->price * $item->quantity) - ($item->discount * $item->quantity).'%0A';
            $pesanDetail = $pesanDetail.'Catatan : '.$item->note.' %0A';
        }

        $mimage = 'fivestar/public/invoice/'. $transaction->payment_proof;
        $waMessage = 'Hallo, saya ingin mengkonfirmasi pesanan saya.%0A%0A'.
                    'ID Transaksi: '.$invoice.'%0A'.
                    'Nama: '.$transaction->customer.'%0A'.
                    'Nomor Meja: '.$transaction->table_id.'%0A'.
                    'Metode Pembayaran: '.$transaction->payment_method.'%0A'.
                    'Sub Total Pemesanan: Rp '.number_format($transaction->subtotal, 0, ',', '.').'%0A'.
                    'Diskon: Rp '.number_format($transaction->discount, 0, ',', '.').'%0A'.
                    'Total Pembayaran: Rp '.number_format($transaction->grand_total, 0, ',', '.').'%0A%0A'.
                    'Bukti Transfer: Rp '.url($mimage).'%0A%0A'.
                    '*Detail Pesanan:*%0A'.$pesanDetail.'%0A%0A ';

        $waLink = 'https://wa.me/'.$phone_wa.'?text='.$waMessage;

        return view('home-page/status', [
            'transaction' => $transaction, 
            'whatsappUrl' => $waLink,
            'cartCount' => 0
        ]);
    }

}
