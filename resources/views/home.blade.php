@extends('layouts.app')

@section('content')
@php
    // Get the authenticated user and their roles at the very top of the content section
    $user = Auth::user();
    // Assuming $user->roles is a collection or an array access property
    $roleNames = $user ? $user->roles->pluck('name')->toArray() : [];
    $isAdmin = in_array('Admin', $roleNames);
    $istestOnly = in_array('test', $roleNames) && !$isAdmin;
@endphp

<style>
    body, html {
        overflow-x: hidden;
        overflow-y: auto;
    }

    .card-link-hover:hover .card {
        transform: translateY(-5px);
        /* Increased shadow for better visibility on hover */
        box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important; 
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }

    .card-link-hover .card {
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }

    .card {
        border-radius: 1rem;
        /* Using a light grey fallback for --border */
        border: 1px solid #e9ecef; 
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075)!important;
    }

    .text-primary-dark {
        color: #0056b3;
    }
</style>

<div class="container d-flex flex-column justify-content-center py-5" style="min-height: 80vh;">
    <h2 class="text-center mb-5">Welcome, {{ $user->name }}</h2>
    
    {{-- Main Feature Cards (Limited to 2) --}}
    <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center mt-3">

        {{-- Card for 'Manage Items' --}}
        <div class="col-md-4">
            <a href="{{ route('items.index') }}" class="text-decoration-none card-link-hover">
                <div class="card h-100 text-center shadow-sm p-3">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-box-open fa-3x mb-2 text-info"></i>
                        <h5 class="card-title"><strong>Manage Items</strong></h5>
                        <p class="card-text text-muted"><strong>Manage and track product requests.</strong></p>
                    </div>
                </div>
            </a>
        </div>
        
        {{-- Card for 'Manage Budget' --}}
        <div class="col-md-4">
            <a href="{{ route('budget.index') }}" class="text-decoration-none card-link-hover">
                <div class="card h-100 text-center shadow-sm p-3">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-dollar-sign fa-3x mb-2 text-success"></i>
                        <h5 class="card-title"><strong>Manage Budget</strong></h5>
                        <p class="card-text text-muted"><strong>View and allocate financial resources.</strong></p>
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
@endsection