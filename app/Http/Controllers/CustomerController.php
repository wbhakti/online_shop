<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function profile()
    {
        $customer = Auth::guard('customer')->user();

        return view('customer.profile', compact('customer'));
    }

    public function updateProfile(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],

            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('customers', 'phone')->ignore($customer->id),
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('customers', 'email')->ignore($customer->id),
            ],

            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
        ]);

        // Jika email berubah, verifikasi email perlu diulang.
        if ($validated['email'] !== $customer->email) {
            $customer->email_verified_at = null;
        }

        // Jika nomor telepon berubah, verifikasi telepon perlu diulang.
        if ($validated['phone'] !== $customer->phone) {
            $customer->phone_verified_at = null;
        }

        $customer->fill($validated);
        $customer->save();

        return redirect()
            ->route('customer.profile')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function orders()
    {
        $customer = Auth::guard('customer')->user();

        $orders = DB::table('transactions')
            ->where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('customer.orders', compact('orders'));
    }

    public function orderDetail(Request $request, $id)
    {
        $customer = Auth::guard('customer')->user();

        $order = DB::table('transactions')
            ->where('transaction_id', $id)
            ->where('customer_id', $customer->id)
            ->first();

        if (!$order) {
            abort(404);
        }

        $items = DB::table('transaction_items')
            ->where('transaction_id', $order->transaction_id)
            ->get();

        $from = $request->query('from', 'history');

        return view('customer.order-detail', compact(
            'order',
            'items',
            'from'
        ));
    }

    public function payment($id)
    {
        $customer = Auth::guard('customer')->user();

        $order = DB::table('transactions')
            ->where('transaction_id', $id)
            ->where('customer_id', $customer->id)
            ->first();

        if (!$order) {
            abort(404);
        }

        if ($order->payment_status === 'PAID') {
            return redirect()->route('customer.orders.detail', $id);
        }

        return view('customer.order-payment', compact('order'));
    }

    public function uploadPaymentProof(Request $request, $id)
    {
        $customer = Auth::guard('customer')->user();

        $order = DB::table('transactions')
            ->where('transaction_id', $id)
            ->where('customer_id', $customer->id)
            ->first();

        if (!$order) {
            abort(404);
        }

        if ($order->payment_status !== 'UNPAID') {
            return back()->with('error', 'Pesanan tidak dapat menerima bukti pembayaran.');
        }

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        try {
            $path = $request->file('payment_proof')
                ->store('payment-proofs', 'public');

            DB::table('transactions')
                ->where('transaction_id', $id)
                ->update([
                    'payment_status' => 'PAID',
                    'order_status' => 'CONFIRMATION',
                    'payment_proof' => $path,
                    'updated_at' => now(),
                ]);

            return redirect()
                ->route('customer.orders.detail', $id)
                ->with('success', 'Bukti pembayaran berhasil diupload. Menunggu verifikasi.');

        } catch (\Throwable $e) {
            Log::error('Upload bukti pembayaran gagal', [
                'transaction_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal mengupload bukti pembayaran.');
        }
    }
}
