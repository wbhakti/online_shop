
@extends('home-page.layouts.app-home')

@section('content')

<div class="container py-5">

    <div class="row g-4">

        {{-- SIDEBAR PROFILE --}}
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center p-4">

                    <div class="mb-3">
                        <div class="rounded-circle bg-light d-inline-flex
                                    align-items-center justify-content-center"
                             style="width:90px;height:90px;">

                            <i class="bi bi-person-fill text-secondary"
                               style="font-size:45px;"></i>

                        </div>
                    </div>

                    <h5 class="fw-bold mb-1">
                        {{ $customer->name }}
                    </h5>

                    <p class="text-muted small mb-3">
                        {{ $customer->customer_code }}
                    </p>

                    <span class="badge bg-success-subtle text-success">
                        Customer
                    </span>

                    <hr>

                    <div class="text-start">

                        <a href="{{ route('customer.profile') }}"
                           class="btn btn-light w-100 text-start mb-2">
                            <i class="bi bi-person me-2"></i>
                            Profil Saya
                        </a>

                        <a href="{{ route('customer.addresses.index') }}"
                           class="btn btn-light w-100 text-start mb-2">
                            <i class="bi bi-geo-alt me-2"></i>
                            Alamat Saya
                        </a>

                        <a href="{{ route('customer.orders') }}"
                           class="btn btn-light w-100 text-start mb-2">
                            <i class="bi bi-receipt me-2"></i>
                            Pesanan Saya
                        </a>

                        <form action="{{ route('customer.logout') }}"
                              method="POST">
                            @csrf

                            <button type="submit"
                                    class="btn btn-light text-danger
                                           w-100 text-start">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </button>
                        </form>

                    </div>

                </div>
            </div>

        </div>


        {{-- PROFILE CONTENT --}}
        <div class="col-lg-8">

            <div class="card border-0 shadow-sm rounded-4">

                <div class="card-body p-4">

                    <div class="d-flex justify-content-between
                                align-items-center mb-4">

                        <div>
                            <h4 class="fw-bold mb-1">Profil Saya</h4>
                            <p class="text-muted small mb-0">
                                Kelola informasi akun Anda.
                            </p>
                        </div>

                        <i class="bi bi-person-circle text-secondary"
                           style="font-size:30px;"></i>

                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
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

                    <form action="{{ route('customer.profile.update') }}"
                          method="POST">

                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nama Lengkap
                            </label>

                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="{{ old('name', $customer->name) }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nomor Telepon
                            </label>

                            <input type="text"
                                   name="phone"
                                   class="form-control"
                                   value="{{ old('phone', $customer->phone) }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Email
                            </label>

                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="{{ old('email', $customer->email) }}">
                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Tanggal Lahir
                                </label>

                                <input type="date"
                                    name="birth_date"
                                    class="form-control"
                                    value="{{ old('birth_date', $customer->birth_date ? \Carbon\Carbon::parse($customer->birth_date)->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Jenis Kelamin
                                </label>

                                <select name="gender"
                                        class="form-select">

                                    <option value="">
                                        Pilih jenis kelamin
                                    </option>

                                    <option value="male"
                                        @selected(old('gender', $customer->gender) == 'male')>
                                        Laki-laki
                                    </option>

                                    <option value="female"
                                        @selected(old('gender', $customer->gender) == 'female')>
                                        Perempuan
                                    </option>

                                </select>
                            </div>

                        </div>

                        <hr>

                        <div class="d-flex justify-content-end">

                            <button type="submit"
                                    class="btn btn-primary px-4">
                                <i class="bi bi-save me-2"></i>
                                Simpan Perubahan
                            </button>

                        </div>

                    </form>

                </div>
            </div>

        </div>

    </div>

</div>

@endsection
