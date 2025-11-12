@extends('layouts.app')

@section('content')
@php
    // Get the authenticated user and their roles at the very top of the content section
    $user = Auth::user();
    $roleNames = $user ? $user->roles->pluck('name')->toArray() : [];
    $isAdmin = in_array('Admin', $roleNames);
    $istestOnly = in_array('test', $roleNames) && !$isAdmin;
@endphp
<style>
    body, html {
        overflow: hidden; /* Hide any scrollbars */
    }
    .card-link-hover:hover .card {
        background-color: #FFFFFF; /* A brighter, lighter blue on hover */
        border-color: #e9ecef !important;
        transform: translateY(-5px); /* Lift card slightly */
        transition: transform 0.3s ease-in-out, background-color 0.3s ease-in-out;
    }
    .card-link-hover .card {
        transition: transform 0.3s ease-in-out, background-color 0.3s ease-in-out;
    }
</style>

<div class="d-flex flex-column justify-content-center align-items-center vh-100">
    <div class="container py-4">
        <h2 class="text-center mb-4">Welcome, {{ $user->name }}</h2>
        
        <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center mt-4">

            {{-- Card for 'Manage Users' --}}
            @if($isAdmin)
            <div class="col-md-4">
                <a href="{{ route('users.index') }}" class="text-decoration-none card-link-hover">
                    <div class="card h-100 text-center border-0 shadow-sm p-4">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center">
                            <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                            <h5 class="card-title">Manage Users</h5>
                            <p class="card-text text-muted">View and manage all user accounts.</p>
                        </div>
                    </div>
                </a>
            </div>
            @endif

            {{-- Card for 'Manage Roles' --}}
            @if($isAdmin)
            <div class="col-md-4">
                <a href="{{ route('roles.index') }}" class="text-decoration-none card-link-hover">
                    <div class="card h-100 text-center border-0 shadow-sm p-4">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center">
                            <i class="fas fa-user-tag fa-3x mb-3 text-success"></i>
                            <h5 class="card-title">Manage Roles</h5>
                            <p class="card-text text-muted">Assign and modify user roles and permissions.</p>
                        </div>
                    </div>
                </a>
            </div>
            @endif
            
            <div class="col-md-4">
                <a href="{{ route('items.index') }}" class="text-decoration-none card-link-hover">
                    <div class="card h-100 text-center border-0 shadow-sm p-4">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center">
                            <i class="fas fa-box-open fa-3x mb-3 text-info"></i>
                            <h5 class="card-title">Manage Items</h5>
                            <p class="card-text text-muted">Manage and track product requests.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Display logged-in status message below the cards --}}
        @if (session('status'))
            <div class="alert alert-success text-center mt-5" role="alert">
                {{ session('status') }}
            </div>
        @endif
    </div>
</div>
@endsection
