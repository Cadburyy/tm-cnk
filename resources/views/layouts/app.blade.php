<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <style>
        .fixed-blur-navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
            backdrop-filter: blur(5px);
            background-color: rgba(255, 255, 255, 0.8);
        }
        
        #loading-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            width: 0;
            background-color: #3498db;
            transition: width 0.3s ease-in-out, opacity 0.5s ease-in-out;
            z-index: 1031;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm fixed-blur-navbar">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    Citra Nugerah Karya
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" 
                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto"></ul>

                    <ul class="navbar-nav ms-auto">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif
                        @else
                            @php
                                $user = Auth::user();
                                $roleNames = $user ? $user->roles->pluck('name')->toArray() : [];
                                $isAdmin = in_array('Admin', $roleNames);
                                $isSupplierOnly = in_array('Supplier', $roleNames) && !$isAdmin;
                            @endphp

                            @if($isAdmin)
                                <li><a class="nav-link" href="{{ route('users.index') }}">Manage Users</a></li>
                                <li><a class="nav-link" href="{{ route('roles.index') }}">Manage Role</a></li>
                                <li><a class="nav-link" href="{{ route('products.index') }}">Manage Product</a></li>
                            @elseif($isSupplierOnly)
                                <li><a class="nav-link" href="{{ route('products.index') }}">Manage Product</a></li>
                            @endif

                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" 
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ $user->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
            <div id="loading-bar"></div>
        </nav>

        <main class="py-4 mt-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div>
                            <div class="card-body">
                                @yield('content')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const loadingBar = document.getElementById('loading-bar');
        
        document.addEventListener('DOMContentLoaded', function() {
            loadingBar.style.width = '90%';
        });

        window.addEventListener('load', function() {
            loadingBar.style.width = '100%';
            loadingBar.style.opacity = '0';
        });
    </script>
</body>
</html>
