
@extends('home-page.layouts.app-home')

@section('content')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <h3 class="text-center fw-bold mb-2">
                        Create Account
                    </h3>

                    <p class="text-center text-muted mb-4">
                        Daftar untuk menikmati layanan Five Star Menu
                    </p>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('customer.store') }}"
                          method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="{{ old('name') }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nomor WhatsApp</label>
                            <input type="text"
                                   name="phone"
                                   class="form-control"
                                   value="{{ old('phone') }}"
                                   placeholder="08xxxxxxxxxx"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email (Opsional)</label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="{{ old('email') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password"
                                   name="password_confirmation"
                                   class="form-control"
                                   required>
                        </div>

                        <button type="submit"
                                class="btn btn-success w-100">
                            Register
                        </button>

                    </form>

                    <div class="text-center mt-4">
                        Sudah punya akun?
                        <a href="{{ route('customer.login') }}">
                            Login
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

@endsection
