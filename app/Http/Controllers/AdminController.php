<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Transaction;

class AdminController extends Controller
{
    public function Login()
    {
        return view('admin/login');
    }

    public function postlogin(Request $request)
    {
        try {

            $validated = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            $passHash = base64_encode(hash_hmac('sha256', $request->input('username') . ':' . $request->input('password'), '#@R4dJaAN91n?#@', true));
            $user = DB::table('users')
                ->where('username', $request->input('username'))
                ->where('password', $passHash)
                ->first();

            if ($user) {
                session(['user_id' => $user->id, 'user_name' => $request->input('username'), 'role' => $user->role]);
                if ($user->role == "kasir") {
                    return redirect()->route('dayTransaction');
                } else {
                    return redirect()->route('dashboard');
                }
            } else {
                return back()->with('error', 'Username atau password salah');
            }
        } catch (\Exception $e) {
            //dd($e);
            Log::error('Error occurred report : ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan : ' . $e->getMessage());
        }
    }

    public function dashboard()
    {
        if (!session()->has('user_id')) {
            return redirect()->route('Login')->with('error', 'You must be logged in to access the menu.');
        }

        return view('admin/dashboard');
    }

    public function MasterMerchant()
    {
        try {

            if (!session()->has('user_id')) {
                return redirect()->route('Login')->with('error', 'You must be logged in to access the menu.');
            }

            $dataMerchant = DB::table('merchants')->get();
            return view('admin/mastermerchant', [
                'data' => $dataMerchant
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal memuat data merchant: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Gagal memuat data merchant');
        }
    }

    public function postmerchant(Request $request)
    {
        try {

            if (!session()->has('user_id')) {
                return redirect()->route('Login')->with('error', 'You must be logged in to access the menu.');
            }

            if($request->input('proses') == 'edit'){

                if ($request->hasFile('img_merchant')){
                    $file = $request->file('img_merchant');
                    $filename = $file->getClientOriginalName();
                    $file->move(public_path('img'), $filename);


                    DB::table('merchants')
                    ->where('id', $request->input('merchant_id'))
                    ->update([ 'name' => $request->input('name'), 'open' => $request->input('open_time'),'closed' => $request->input('closed_time'),'is_active' => $request->input('is_active'), 'logo' => $filename]);

                    return redirect()->route('MasterMerchant')->with('success', 'berhasil edit data');

                }else{

                    DB::table('merchants')
                    ->where('id', $request->input('merchant_id'))
                    ->update([ 'name' => $request->input('name'), 'open' => $request->input('open_time'),'closed' => $request->input('closed_time'),'is_active' => $request->input('is_active')]);

                    return redirect()->route('MasterMerchant')->with('success', 'berhasil edit data');
                }
            }
            else{
                return redirect()->route('MasterMerchant')->with('success', 'gagal');
            }

        } catch (\Exception $e) {
            Log::error('Gagal proses data: ' . $e->getMessage());
            return redirect()->route('MasterMerchant')->with('error', 'gagal save merchant');
        }
    }

    public function logout(Request $request)
    {
        // Menghapus semua data dari sesi
        $request->session()->flush();
        return redirect()->route('menu');
    }

    public function transaction(Request $request)
    {
        try{

            if (!session()->has('user_id')) {
                return redirect()->route('Login')->with('error', 'You must be logged in to access the menu.');
            }

            $dataTransaksi = Transaction::with('details')
            ->where('created_at', '>=', $request->date_start . ' 00:00:00')
            ->where('created_at', '<=', $request->date_end . ' 23:59:59')
            ->orderBy('created_at', 'desc')
            ->get();
            
                return view('admin/mastertransaksi', [
                    'data' => $dataTransaksi,
                    'date_start' => $request->date_start,
                    'date_end' => $request->date_end,
                ]);

        }catch (\Exception $e) {
            Log::error('Gagal proses data: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'gagal proses data');
        }
    }

    public function dayTransaction()
    {
        try {

            if (!session()->has('user_id')) {
                return redirect()->route('Login')->with('error', 'You must be logged in to access the menu.');
            }
            $today = Carbon::now('Asia/Jakarta');

            $dataTransaksi = DB::table('transactions')
                ->whereDate('created_at', $today )
                ->orderByDesc('created_at')
                ->get();

            return view('admin/transaksi', [
                'data' => $dataTransaksi
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal memuat data transaksi: ' . $e->getMessage());
            return redirect()->route('MasterMerchant')->with('error', 'gagal load menu');
        }
    }

    public function UpdateStatus(Request $request)
    {
        try {
            if (!session()->has('user_id')) {
                return response()->json(['success' => false, 'message' => 'You must be logged in to access the menu.'], 401);
            }
    
            $updated = DB::table('transactions')
                ->where('id_transaksi', $request->input('id'))
                ->update(['status' => $request->input('status')]);
    
            if ($updated) {
                return response()->json(['success' => true, 'message' => 'Status berhasil diperbarui']);
            } else {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan']);
            }
    
        } catch (\Exception $e) {
            Log::error('Gagal proses data: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem'], 500);
        }
    }

    public function settingorder()
    {
        try {

            if (!session()->has('user_id')) {
                return redirect()->route('Login')->with('error', 'You must be logged in to access the menu.');
            }

            $closeorder = DB::table('configuration')->where('parameter', 'close_order')->first();
            return view('admin/settingorder', [
                'data' => $closeorder
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal memuat data setting: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'gagal load setting');
        }
    }

    public function CloseOrder(Request $request)
    {
        try {
            if (!session()->has('user_id')) {
                return response()->json(['success' => false, 'message' => 'You must be logged in to access the menu.'], 401);
            }
    
            if ($request->input('proses') == 'open'){
                DB::table('configuration')
                ->where('parameter', 'close_order')
                ->update(['value' => 'open']);

                return redirect()->route('settingorder')->with('success', 'berhasil open order');
            } else if ($request->input('proses') == 'closed'){
                DB::table('configuration')
                ->where('parameter', 'close_order')
                ->update(['value' => 'closed']);

                return redirect()->route('settingorder')->with('success', 'berhasil close order');
            }
    
        } catch (\Exception $e) {
            Log::error('Gagal proses data: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem'], 500);
        }
    }

    public function MasterPromo()
    {
        try {

            if (!session()->has('user_id')) {
                return redirect()->route('Login')->with('error', 'You must be logged in to access the menu.');
            }

            $dataPromo = DB::table('configuration')->where('parameter', 'popup_banner')->get();
            return view('admin/masterpromo', [
                'data' => $dataPromo
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal memuat data promo: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Gagal memuat data merchant');
        }
    }

    public function postPopupPromo(Request $request)
    {
        try {
            
            if (!session()->has('user_id')) {
                return redirect()->route('Login')->with('error', 'You must be logged in to access the menu.');
            }

            if($request->input('proses') == 'edit'){

                if ($request->hasFile('img_promo')){
                    $file = $request->file('img_promo');
                    $filename = 'promo_'.date('YmdHis').'.jpg';
                    $file->move(base_path('../public/img'), $filename);

                    DB::table('configuration')
                    ->where('id', $request->input('id_promo'))
                    ->update([ 
                        'value' => $filename,
                        'description' => $request->input('editTitle')
                    ]);

                }else{

                    DB::table('configuration')
                    ->where('id', $request->input('id_promo'))
                    ->update([ 
                        'description' => $request->input('editTitle')
                    ]);
                }

                return redirect()->route('MasterPromo')->with('success', 'berhasil edit promo');
            }
            else if ($request->input('proses') == 'delete'){

                DB::table('configuration')->where('id', $request->input('id_promo'))->delete();

                return redirect()->route('MasterPromo')->with('success', 'berhasil hapus promo');
            }
            else{
                $file = $request->file('img_promo');
                $filename = 'promo_'.date('YmdHis').'.jpg';
                $file->move(base_path('../public/img'), $filename);

                DB::table('configuration')->insert([
                    'description' => $request->input('description'),
                    'parameter' => 'popup_banner',
                    'value' => $filename
                ]);
                return redirect()->route('MasterPromo')->with('success', 'berhasil tambah data');
            }

        } catch (\Exception $e) {
            Log::error('Gagal proses data: ' . $e->getMessage());
            return redirect()->route('MasterPromo')->with('error', 'gagal simpan promo');
        }
    }

    public function MasterDiskon()
    {
        try {

            if (!session()->has('user_id')) {
                return redirect()->route('Login')->with('error', 'You must be logged in to access the menu.');
            }

            $dataPromo = DB::table('configuration')->where('parameter', 'diskon')->get();
            return view('admin/masterdiskon', [
                'data' => $dataPromo
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal memuat data diskon: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Gagal memuat data merchant');
        }
    }

    public function postDiskon(Request $request)
    {
        try {
            
            if (!session()->has('user_id')) {
                return redirect()->route('Login')->with('error', 'You must be logged in to access the menu.');
            }

            if($request->input('proses') == 'edit'){

                $dateStart = $request->input('editAwal');
                $dateEnd = $request->input('editAkir');
                $diskon = $request->input('editDiskon');
                
                $description = $dateStart.'-'.$dateEnd;

                DB::table('configuration')
                    ->where('id', $request->input('id_diskon'))
                    ->update([ 
                        'value' => $diskon,
                        'description' => $description
                    ]);

                return redirect()->route('MasterDiskon')->with('success', 'berhasil edit diskon');
            }
            else if ($request->input('proses') == 'delete'){

                DB::table('configuration')->where('id', $request->input('id_promo'))->delete();

                return redirect()->route('MasterDiskon')->with('success', 'berhasil hapus diskon');
            }
            else{
                $dateStart = $request->input('startdiskon');
                $dateEnd = $request->input('enddiskon');
                $description = $dateStart.'-'.$dateEnd;

                DB::table('configuration')->insert([
                    'description' => $description,
                    'parameter' => 'diskon',
                    'value' => $request->input('diskon')
                ]);
                return redirect()->route('MasterDiskon')->with('success', 'berhasil tambah data');
            }

        } catch (\Exception $e) {
            Log::error('Gagal proses data: ' . $e->getMessage());
            return redirect()->route('MasterDiskon')->with('error', 'gagal simpan diskon');
        }
    }

    public function subscribe(Request $request)
    {
        $adminId = session('user_id');

        if (!$adminId) {
            return response()->json([
                'message' => 'Admin belum login'
            ], 401);
        }

        $admin = User::find($adminId);

        if (!$admin) {
            return response()->json([
                'message' => 'Admin tidak ditemukan'
            ], 404);
        }

        $admin->updatePushSubscription(
            $request->endpoint,
            $request->keys['p256dh'],
            $request->keys['auth'],
            $request->contentEncoding ?? 'aesgcm'
        );

        return response()->json([
            'success' => true
        ]);
    }

    // ========================================
    // ADMIN ORDER LIST
    // ========================================
    public function orderList(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect()->route('Login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $query = Transaction::with(['customer', 'details'])
            ->orderByDesc('created_at');

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->where(function ($q) use ($keyword) {
                $q->where('invoice_number', 'like', "%{$keyword}%")
                ->orWhere('transaction_id', $keyword);
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }


    // ========================================
    // ADMIN ORDER DETAIL
    // ========================================
    public function orderDetail($id)
    {
        if (!session()->has('user_id')) {
            return redirect()->route('Login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $order = Transaction::with(['customer', 'details'])
            ->where('transaction_id', $id)
            ->firstOrFail();

        return view('admin.orders.detail', compact('order'));
    }


    // ========================================
    // APPROVE PAYMENT
    // ========================================
    public function approvePayment($id)
    {
        if (!session()->has('user_id')) {
            return back()->with('error', 'Silakan login terlebih dahulu.');
        }

        $order = Transaction::where('transaction_id', $id)->firstOrFail();

        if ($order->payment_status === 'PAID') {
            return back()->with('error', 'Pembayaran sudah dikonfirmasi.');
        }

        if (empty($order->payment_proof)) {
            return back()->with('error', 'Bukti pembayaran belum diunggah customer.');
        }

        if ($order->order_status !== 'UNPAID') {
            return back()->with('error', 'Status order tidak memungkinkan verifikasi pembayaran.');
        }

        DB::transaction(function () use ($order) {
            $order->payment_status = 'PAID';
            $order->order_status = 'PAID';
            $order->save();
        });

        return redirect()
            ->route('admin.orders.detail', $id)
            ->with('success', 'Pembayaran berhasil dikonfirmasi.');
    }


    // ========================================
    // REJECT PAYMENT
    // ========================================
    public function rejectPayment(Request $request, $id)
    {
        if (!session()->has('user_id')) {
            return back()->with('error', 'Silakan login terlebih dahulu.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $order = Transaction::where('transaction_id', $id)->firstOrFail();

        if ($order->payment_status === 'PAID') {
            return back()->with('error', 'Pembayaran sudah dikonfirmasi dan tidak dapat ditolak.');
        }

        if (empty($order->payment_proof)) {
            return back()->with('error', 'Tidak ada bukti pembayaran untuk ditolak.');
        }

        if ($order->order_status !== 'UNPAID') {
            return back()->with('error', 'Status order tidak memungkinkan penolakan pembayaran.');
        }

        $oldNotes = trim((string) $order->notes);
        $rejectionNote = '[PAYMENT REJECTED] ' . $request->rejection_reason;

        $order->payment_status = 'UNPAID';
        $order->order_status = 'UNPAID';
        $order->notes = $oldNotes
            ? $oldNotes . "\n" . $rejectionNote
            : $rejectionNote;

        // Bukti lama tetap disimpan sebagai riwayat.
        $order->save();

        return redirect()
            ->route('admin.orders.detail', $id)
            ->with('success', 'Bukti pembayaran ditolak. Customer perlu mengunggah bukti baru.');
    }


    // ========================================
    // UPDATE ORDER STATUS
    // ========================================
    public function updateOrderStatus(Request $request, $id)
    {
        if (!session()->has('user_id')) {
            return back()->with('error', 'Silakan login terlebih dahulu.');
        }

        $request->validate([
            'order_status' => 'required|in:PROCESSING,COMPLETED,CANCELLED',
        ]);

        $order = Transaction::where('transaction_id', $id)->firstOrFail();

        $newStatus = $request->order_status;

        // Hanya order yang pembayarannya sudah terverifikasi
        // yang boleh diproses atau diselesaikan.
        if (in_array($newStatus, ['PROCESSING', 'COMPLETED'])
            && $order->payment_status !== 'PAID') {
            return back()->with('error', 'Pembayaran harus terverifikasi terlebih dahulu.');
        }

        // Validasi urutan status.
        if ($newStatus === 'PROCESSING' && $order->order_status !== 'PAID') {
            return back()->with('error', 'Order harus berstatus PAID sebelum diproses.');
        }

        if ($newStatus === 'COMPLETED' && $order->order_status !== 'PROCESSING') {
            return back()->with('error', 'Order harus berstatus PROCESSING sebelum diselesaikan.');
        }

        if ($order->order_status === 'COMPLETED'
            || $order->order_status === 'CANCELLED') {
            return back()->with('error', 'Order sudah berada pada status akhir.');
        }

        if ($newStatus === 'CANCELLED' && $order->order_status === 'PROCESSING') {
            return back()->with('error', 'Order yang sedang diproses perlu ditangani melalui prosedur pembatalan.');
        }

        $order->order_status = $newStatus;
        $order->save();

        return back()->with('success', 'Status order berhasil diperbarui.');
    }


    // ========================================
    // UPDATE SHIPPING INFORMATION
    // ========================================
    public function updateShipping(Request $request, $id)
    {
        if (!session()->has('user_id')) {
            return back()->with('error', 'Silakan login terlebih dahulu.');
        }

        $request->validate([
            'shipping_courier' => 'required|string|max:100',
            'shipping_service' => 'nullable|string|max:100',
            'tracking_number' => 'required|string|max:100',
        ]);

        $order = Transaction::where('transaction_id', $id)->firstOrFail();

        if (!in_array($order->order_status, ['PROCESSING'])) {
            return back()->with('error', 'Informasi pengiriman hanya dapat diisi saat order sedang diproses.');
        }

        $order->shipping_courier = $request->shipping_courier;
        $order->shipping_service = $request->shipping_service;
        $order->tracking_number = $request->tracking_number;
        $order->save();

        return back()->with('success', 'Informasi pengiriman berhasil disimpan.');
    }
}
