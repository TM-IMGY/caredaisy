<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel='stylesheet' href="{{ mix('/css/extra.css') }}">
</head>
{{-- @extends('layouts.app') --}}
{{-- @section('content') --}}
<body>
<div id="caredaisy_login">
    {{-- ロゴ --}}
    <img id="caredaisy_logo" src="{{ asset('/sozai/logo1.png') }}">

    <form method="POST" action="{{ route('login') }}">
        @csrf
        {{-- エラーメッセージ --}}
        @error('employee_number')
            <div class="login_error">
                <img class="login_error_icon" src="{{ asset('/sozai/login_error.png') }}">
                <span class="login_error_message">{!! $message !!}</span>
            </div>
        @enderror

        {{-- ID --}}
        <input
            id="employee_number"
            type="employee_number"
            class="login_input @error('employee_number') is-invalid @enderror"
            name="employee_number" value="{{ old('employee_number') }}"
            required
            autocomplete="employee_number"
            autofocus
            placeholder="ID">

        {{-- パスワード --}}
        <input
            id="password"
            type="password"
            class="login_input @error('employee_number') is-invalid @enderror"
            name="password"
            required
            autocomplete="current-password"
            placeholder="パスワード">

        {{-- Remember Me --}}
        {{-- <div class="form-group row">
            <div class="col-md-6 offset-md-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                    <label class="form-check-label" for="remember">
                        {{ __('Remember Me') }}
                    </label>
                </div>
            </div>
        </div> --}}

        <button id="loging_btn" type="submit">ログイン</button>

        {{-- @if (Route::has('password.request'))
            <a id="password_request_link" href="{{ route('password.request') }}">
                パスワードを変更したい方・忘れた方はこちら
            </a>
        @endif --}}
    </form>
    <script src="{{ mix('/js/login.js') }}"></script>
</div>
</body>
{{-- @endsection --}}
