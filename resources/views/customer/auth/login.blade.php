
@extends('home-page.layouts.app-home')

@section('content')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <h3 class="text-center fw-bold mb-2">
                        Customer Login
                    </h3>

                    <p class="text-center text-muted mb-4">
                        Login untuk melanjutkan transaksi
                    </p>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('customer.authenticate') }}"
                          method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">
                                Nomor WhatsApp
                            </label>

                            <input type="text"
                                   name="phone"
                                   class="form-control"
                                   value="{{ old('phone') }}"
                                   placeholder="08xxxxxxxxxx"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Password
                            </label>

                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   placeholder="Masukkan password"
                                   required>
                        </div>

                        <button type="submit"
                                class="btn btn-success w-100">
                            <i class="bi bi-box-arrow-in-right me-1"></i>
                            Login
                        </button>

                    </form>

                    <div class="text-center mt-4">
                        Belum punya akun?
                        <a href="{{ route('customer.register') }}">
                            Register
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

@endsection
