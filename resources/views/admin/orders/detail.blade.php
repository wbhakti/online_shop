@extends('admin.layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-gray-800 mb-1">Detail Order</h1>
            <p class="text-muted mb-0">{{ $order->invoice_number }}</p>
        </div>

        <a href="{{ route('admin.orders') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">

        {{-- INFORMASI ORDER --}}
        <div class="col-lg-8">

            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Informasi Pesanan
                    </h6>
                </div>

                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Invoice</div>
                        <div class="col-sm-8 font-weight-bold">
                            {{ $order->invoice_number }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Tanggal Order</div>
                        <div class="col-sm-8">
                            {{ $order->created_at
                                ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i')
                                : '-' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Customer</div>
                        <div class="col-sm-8">
                            {{ optional($order->customer)->name ?? '-' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Metode Pembayaran</div>
                        <div class="col-sm-8">
                            {{ $order->payment_method ?? '-' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Payment Status</div>
                        <div class="col-sm-8">
                            <span class="badge badge-info">
                                {{ $order->payment_status }}
                            </span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-4 text-muted">Order Status</div>
                        <div class="col-sm-8">
                            <span class="badge badge-primary">
                                {{ $order->order_status }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- DETAIL PRODUK --}}
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Produk Pesanan
                    </h6>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($order->details as $item)
                                    <tr>
                                        <td>
                                            {{ $item->product_name }}

                                            @if($item->note)
                                                <br>
                                                <small class="text-muted">
                                                    Catatan: {{ $item->note }}
                                                </small>
                                            @endif
                                        </td>

                                        <td>
                                            Rp {{ number_format($item->price, 0, ',', '.') }}
                                        </td>

                                        <td>{{ $item->quantity }}</td>

                                        <td>
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="text-right">
                        <p class="mb-1">
                            Subtotal:
                            Rp {{ number_format($order->subtotal, 0, ',', '.') }}
                        </p>

                        <p class="mb-1">
                            Ongkir:
                            Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}
                        </p>

                        <p class="mb-1">
                            Diskon:
                            Rp {{ number_format($order->discount, 0, ',', '.') }}
                        </p>

                        <h5 class="font-weight-bold text-primary mt-3">
                            Total:
                            Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
            </div>

            {{-- ALAMAT PENGIRIMAN --}}
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Alamat Pengiriman
                    </h6>
                </div>

                <div class="card-body">
                    <h6 class="font-weight-bold">
                        {{ $order->shipping_recipient ?? '-' }}
                    </h6>

                    <p class="mb-1">
                        {{ $order->shipping_phone ?? '-' }}
                    </p>

                    <p class="mb-1">
                        {{ $order->shipping_address ?? '-' }}
                    </p>

                    <p class="mb-0">
                        {{ $order->shipping_city ?? '' }}
                        {{ $order->shipping_postal_code ?? '' }}
                    </p>
                </div>
            </div>

        </div>

        {{-- PANEL ADMIN --}}
        <div class="col-lg-4">

            {{-- VERIFIKASI PEMBAYARAN --}}
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Verifikasi Pembayaran
                    </h6>
                </div>

                <div class="card-body">

                    @if($order->payment_proof)

                        <p class="font-weight-bold mb-2">Bukti Pembayaran</p>

                        <a href="{{ asset($order->payment_proof) }}"
                            target="_blank">
                            <img
                                src="{{ asset($order->payment_proof) }}"
                                class="img-fluid rounded border mb-3"
                                alt="Bukti Pembayaran">
                        </a>

                    @else

                        <div class="alert alert-warning">
                            Customer belum mengunggah bukti pembayaran.
                        </div>

                    @endif

                    @if($order->payment_status == 'UNPAID'
                        && $order->order_status == 'UNPAID'
                        && $order->payment_proof)

                        <form method="POST"
                            action="{{ route('admin.orders.payment.approve', $order->transaction_id) }}"
                            onsubmit="return confirm('Konfirmasi pembayaran ini?')">

                            @csrf

                            <button class="btn btn-success btn-block mb-2">
                                <i class="fas fa-check"></i>
                                Terima Pembayaran
                            </button>

                        </form>

                        <form method="POST"
                            action="{{ route('admin.orders.payment.reject', $order->transaction_id) }}">

                            @csrf

                            <div class="form-group">
                                <label>Alasan Penolakan</label>
                                <textarea
                                    name="rejection_reason"
                                    class="form-control"
                                    rows="3"
                                    required
                                    placeholder="Contoh: Nominal transfer tidak sesuai"></textarea>
                            </div>

                            <button class="btn btn-danger btn-block"
                                onclick="return confirm('Tolak bukti pembayaran ini?')">
                                <i class="fas fa-times"></i>
                                Tolak Pembayaran
                            </button>

                        </form>

                    @elseif($order->payment_status == 'PAID')

                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle"></i>
                            Pembayaran telah terverifikasi.
                        </div>

                    @endif

                </div>
            </div>

            {{-- UPDATE STATUS ORDER --}}
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Status Pesanan
                    </h6>
                </div>

                <div class="card-body">

                    <p>
                        Status sekarang:
                        <strong>{{ $order->order_status }}</strong>
                    </p>

                    @if($order->order_status == 'PAID'
                        || $order->order_status == 'PROCESSING')

                        <form method="POST"
                            action="{{ route('admin.orders.status', $order->transaction_id) }}"
                            onsubmit="return confirm('Simpan perubahan status order?')">

                            @csrf

                            <div class="form-group">
                                <label>Status Baru</label>

                                <select name="order_status" class="form-control" required>

                                    @if($order->order_status == 'PAID')
                                        <option value="PROCESSING">PROCESSING</option>
                                        <option value="CANCELLED">CANCELLED</option>
                                    @endif

                                    @if($order->order_status == 'PROCESSING')
                                        <option value="COMPLETED">COMPLETED</option>
                                    @endif

                                </select>
                            </div>

                            <button class="btn btn-primary btn-block">
                                Update Status
                            </button>

                        </form>

                    @else

                        <p class="text-muted mb-0">
                            Tidak ada perubahan status yang tersedia.
                        </p>

                    @endif

                </div>
            </div>

            {{-- INFORMASI PENGIRIMAN --}}
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Informasi Pengiriman
                    </h6>
                </div>

                <div class="card-body">

                    @if($order->order_status == 'PROCESSING')

                        <form method="POST"
                            action="{{ route('admin.orders.shipping', $order->transaction_id) }}">

                            @csrf

                            <div class="form-group">
                                <label>Kurir</label>
                                <input
                                    type="text"
                                    name="shipping_courier"
                                    class="form-control"
                                    value="{{ $order->shipping_courier }}"
                                    placeholder="Contoh: JNE"
                                    required>
                            </div>

                            <div class="form-group">
                                <label>Layanan</label>
                                <input
                                    type="text"
                                    name="shipping_service"
                                    class="form-control"
                                    value="{{ $order->shipping_service }}"
                                    placeholder="Contoh: REG">
                            </div>

                            <div class="form-group">
                                <label>Nomor Resi</label>
                                <input
                                    type="text"
                                    name="tracking_number"
                                    class="form-control"
                                    value="{{ $order->tracking_number }}"
                                    placeholder="Masukkan nomor resi"
                                    required>
                            </div>

                            <button class="btn btn-info btn-block">
                                <i class="fas fa-truck"></i>
                                Simpan Pengiriman
                            </button>

                        </form>

                    @else

                        <p class="mb-1">
                            Kurir: {{ $order->shipping_courier ?? '-' }}
                        </p>

                        <p class="mb-1">
                            Layanan: {{ $order->shipping_service ?? '-' }}
                        </p>

                        <p class="mb-0">
                            Resi: {{ $order->tracking_number ?? '-' }}
                        </p>

                    @endif

                </div>
            </div>

        </div>
    </div>

</div>

@endsection