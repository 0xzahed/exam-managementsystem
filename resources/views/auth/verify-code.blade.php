@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="flex justify-center">
                <i class="fas fa-envelope-open text-4xl text-indigo-600"></i>
            </div>
            <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">
                Verify Your Email
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Enter the 6-digit code sent to your email
            </p>
            @if($email ?? session('email'))
                <p class="mt-1 text-center text-sm text-indigo-600 font-medium">
                    {{ $email ?? session('email') }}
                </p>
            @endif
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 text-lg mr-3"></i>
                    <div>
                        <h3 class="text-red-800 font-medium">Error</h3>
                        <ul class="text-red-700 text-sm mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Success Messages -->
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 text-lg mr-3"></i>
                    <div>
                        <h3 class="text-green-800 font-medium">Success!</h3>
                        <p class="text-green-700 text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('verify.code.submit') }}" method="POST">
            @csrf
            <!-- Hidden Email Field -->
            <input type="hidden" name="email" value="{{ $email ?? session('email') ?? old('email') }}">
            
            <div class="space-y-4">
                @if($email ?? session('email'))
                    <div class="text-center p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                        <p class="text-sm text-gray-600">Verification code sent to:</p>
                        <p class="font-medium text-indigo-800">{{ $email ?? session('email') }}</p>
                    </div>
                @endif

                <!-- Verification Code -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 text-center mb-4">Enter Verification Code</label>
                    <input id="code" name="code" type="text" required maxlength="6" 
                           class="appearance-none relative block w-full px-6 py-4 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-center text-3xl font-mono tracking-widest bg-gray-50"
                           placeholder="000000" 
                           autofocus>
                    <p class="mt-2 text-xs text-gray-500 text-center">Enter the 6-digit code from your email</p>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" 
                        class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-lg font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-check text-indigo-500 group-hover:text-indigo-400"></i>
                    </span>
                    Confirm & Activate Account
                </button>
            </div>

            <!-- Resend Code -->
            <div class="text-center space-y-2">
                <p class="text-sm text-gray-600">Didn't receive the code?</p>
                <form action="{{ route('resend.code') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email ?? session('email') ?? old('email') }}">
                    <button type="submit" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium underline">
                        Resend Code
                    </button>
                </form>
            </div>

            <div class="text-center">
                <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-500">
                    ← Back to Login
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
