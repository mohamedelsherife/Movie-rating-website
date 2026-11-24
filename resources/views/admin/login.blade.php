@extends('layouts.app')

@section('title', 'Admin login')
@section('body-class', 'auth-page')

@section('content')
    {{-- Background: a dimmed wall of real posters from the catalogue (decorative). --}}
    <div class="auth-wall" aria-hidden="true">
        @foreach ($posters as $poster)
            <img src="{{ asset('storage/movies/' . $poster) }}" alt="" loading="lazy">
        @endforeach
    </div>

    <section class="auth-card">
        <div class="auth-brand">
            <img src="{{ asset('images/logo.png') }}" class="brand-mark" alt="" aria-hidden="true">
            <span class="brand-name">CineRate</span>
            <span class="brand-tag">Admin</span>
        </div>

        <h1 class="auth-title">Welcome back</h1>
        <p class="auth-sub">Log in to manage the movies and TV series in the catalogue.</p>

        @error('login')
            <x-alert type="error" :dismissible="false" class="form-alert">{{ $message }}</x-alert>
        @enderror

        <form action="{{ route('admin.login.submit') }}" method="POST" class="auth-form" data-loading novalidate>
            @csrf

            <x-form.field label="Email" for="email">
                <x-form.input name="email" placeholder="Your admin email" autocomplete="username" autofocus required />
            </x-form.field>

            <x-form.field label="Password" for="password">
                <div class="password-field" data-password-field>
                    <x-form.input type="password" name="password" autocomplete="current-password" placeholder="••••••••" required />
                    <button type="button" class="password-toggle" data-password-toggle
                            aria-label="Show password" aria-pressed="false">
                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </x-form.field>

            <label class="check">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                <span class="check-box" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
                <span>Remember me</span>
            </label>

            <x-button icon="right-to-bracket" class="auth-submit">Log in</x-button>
        </form>
    </section>
@endsection
