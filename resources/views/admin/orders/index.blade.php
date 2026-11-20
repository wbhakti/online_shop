@extends('admin.layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Admin Order</h1>
            <p class="mb-0 text-muted">Pengelolaan pesanan customer</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- FILTER --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.orders') }}">
                <div class="form-row">

                    <div class="col-md-4 mb-2">
                        <input type="text"
                            name="keyword"
                            class="form-control"
                            placeholder="Cari invoice..."
                            value="{{ request('keyword') }}">
                    </div>

                    <div class="col-md-3 mb-2">
                        <select name="payment_status" class="form-control">
                            <option value="">Semua Pembayaran</option>
                            <option value="UNPAID" {{ request('payment_status') == 'UNPAID' ? 'selected' : '' }}>UNPAID</option>
                            <option value="PAID" {{ request('payment_status') == 'PAID' ? 'selected' : '' }}>PAID</option>
                            <option value="REFUNDED" {{ request('payment_status') == 'REFUNDED' ? 'selected' : '' }}>REFUNDED</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-2">
                        <select name="order_status" class="form-control">
                            <option value="">Semua Status Order</option>
                            @foreach(['UNPAID', 'PAID', 'PROCESSING', 'COMPLETED', 'CANCELLED'] as $status)
                                <option value="{{ $status }}"
                                    {{ request('order_status') == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <button class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- ORDER TABLE --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Pesanan</h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Tanggal</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Status Order</th>
                            <th width="90">Detail</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>
                                    <strong>{{ $order->invoice_number }}</strong>
                                </td>

                                <td>
                                    {{ $order->created_at
                                        ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i')
                                        : '-' }}
                                </td>

                                <td>
                                    {{ optional($order->customer)->name ?? '-' }}
                                </td>

                                <td>
                                    Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                </td>

                                <td>
                                    @if($order->payment_status == 'PAID')
                                        <span class="badge badge-success">PAID</span>
                                    @elseif($order->payment_status == 'REFUNDED')
                                        <span class="badge badge-secondary">REFUNDED</span>
                                    @else
                                        <span class="badge badge-warning">UNPAID</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $order->order_status }}
                                </td>

                                <td>
                                    <a href="{{ route('admin.orders.detail', $order->transaction_id) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Belum ada data order.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $orders->links() }}
            </div>
        </div>
    </div>

</div>

@endsection