
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>PITSTOP TOYS</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="{{ asset('assets/favicon.ico') }}" />
        <!-- Bootstrap icons-->
        <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        <script src="{{ asset('vendor/jquery/jquery.min.js')}}"></script>
        <style>
            body {
                padding-top: 70px;
            }

            .custom-navbar {
                background-color: #000;
                color: white;
                min-height: 56px;
            }

            .custom-navbar .navbar-brand {
                color: #fff;
            }

            .custom-navbar .navbar-brand:hover {
                color: #ddd;
            }

            .custom-navbar .dropdown-menu {
                min-width: 190px;
                margin-top: 8px;
            }

            @media (max-width: 576px) {
                .custom-navbar .container {
                    padding-left: 12px;
                    padding-right: 12px;
                }

                .custom-navbar .btn {
                    font-size: 12px;
                    padding: 6px 9px;
                }
            }
        </style>
    </head>
    <body>
        
        <!-- Navigation -->
        <nav class="navbar custom-navbar fixed-top">
            <div class="container px-4 px-lg-5">

                <!-- Brand -->
                <a class="navbar-brand text-white" href="{{ url('/') }}">
                    <b>Home</b>
                </a>

                <!-- Right Menu -->
                <div class="d-flex align-items-center gap-2">

                    <!-- Cart -->
                    <a href="{{ url('/cart') }}"
                    class="btn btn-outline-light btn-sm">

                        <i class="bi-cart-fill me-1"></i>
                        Cart

                        <span id="cart-badge"
                            class="badge bg-light text-dark ms-1 rounded-pill">
                            {{ $cartCount ?? 0 }}
                        </span>
                    </a>

                    <!-- Customer Authentication -->
                    @if(Auth::guard('customer')->check())

                        <div class="dropdown">

                            <button class="btn btn-success btn-sm dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">

                                <i class="bi bi-person-circle me-1"></i>

                                {{ Auth::guard('customer')->user()->name }}

                            </button>

                            <ul class="dropdown-menu dropdown-menu-end">

                                <li>
                                    <a class="dropdown-item"
                                    href="{{ route('customer.profile') }}">
                                        <i class="bi bi-person me-2"></i>
                                        Profil Saya
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                    href="{{ route('customer.addresses.index') }}">
                                        <i class="bi bi-geo-alt me-2"></i>
                                        Alamat Saya
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                    href="{{ route('customer.orders') }}">
                                        <i class="bi bi-receipt me-2"></i>
                                        Riwayat Pesanan
                                    </a>
                                </li>

                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                <li>
                                    <form action="{{ route('customer.logout') }}"
                                        method="POST">
                                        @csrf

                                        <button type="submit"
                                                class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>
                                            Logout
                                        </button>
                                    </form>
                                </li>

                            </ul>
                        </div>

                    @else

                        <a href="{{ route('customer.login') }}"
                        class="btn btn-outline-light btn-sm">
                            Login
                        </a>

                        <a href="{{ route('customer.register') }}"
                        class="btn btn-success btn-sm">
                            Register
                        </a>

                    @endif

                </div>
            </div>
        </nav>

        
        <!-- Section-->
        @yield('content')
        
        <!-- Footer-->
        <footer class="py-5 bg-dark" style="background-color: #000 !important;">
            <div class="container"><p class="m-0 text-center" style="color: #fff">Copyright &copy; PITSTOP TOYS 2026</p></div>
        </footer>
        <!-- Bootstrap core JS-->
        <script src="{{ asset('frontend-vendor/bootstrap-5.2.3-dist/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>

        <!-- Core theme JS-->
        <script src="{{ asset('js/scripts.js') }}"></script>

        <!-- Page Specific Scripts -->
        @yield('scripts')

    </body>
</html>
