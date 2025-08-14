@extends('layouts.auth')

@section('title', 'Activate Google Account')

@section('content')
<div class="container mx-auto max-w-lg p-6">
    <div class="bg-white rounded-xl shadow border border-gray-200 p-6 text-center">
        <h1 class="text-2xl font-semibold text-gray-800 mb-2">Confirm Account Activation</h1>
        <p class="text-gray-600 mb-4">We fetched your Google profile. Click the button below to create your InsightEdu account and receive a verification email.</p>

        <div class="bg-gray-50 rounded-lg p-4 text-left mb-4">
            @php($g = session('google_user_data'))
            <p class="text-sm text-gray-700"><strong>Name:</strong> {{ $g['first_name'] }} {{ $g['last_name'] }}</p>
            <p class="text-sm text-gray-700"><strong>Email:</strong> {{ $g['email'] }}</p>
            <p class="text-sm text-gray-700"><strong>Role:</strong> {{ ucfirst($g['role']) }}</p>
        </div>

        <form action="{{ route('auth.google.register') }}" method="POST" class="mt-4">
            @csrf
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Activate & Send Verification Email
            </button>
        </form>

        <p class="text-xs text-gray-500 mt-3">We will not create your account until you click the button above.</p>
    </div>
</div>
@endsection


