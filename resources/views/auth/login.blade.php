@extends('layouts.guest')

@section('content')
<div class="d-flex justify-content-center align-items-center vh-100">
    <div class="row w-100 justify-content-center">
        <div class="col-md-6 col-lg-5"> {{-- Changed column size to control card width --}}
            <div class="card shadow-lg">
                <div class="row g-0">
                    {{-- Top side: Image --}}
                    <div class="col-12 d-flex align-items-center justify-content-center bg-light p-4"> {{-- Image now takes full width on top --}}
                        <img src="{{ asset('images/cnk.png') }}" alt="CNK" class="img-fluid" style="max-height: 250px;">
                    </div>

                    {{-- Bottom side: Form --}}
                    <div class="col-12 p-4"> {{-- Form now takes full width on the bottom --}}
                        <h4 class="mb-4 text-center">{{ __('Login') }}</h4>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">{{ __('Email Address') }}</label>
                                <input id="email" type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">{{ __('Password') }}</label>
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    name="password" required autocomplete="current-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="mb-3 form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                    {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                {{ __('Login') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection