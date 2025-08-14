@extends('layouts.dashboard')

@section('title', 'Notification Demo')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <!-- Page Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Notification System Demo</h1>
        <p class="text-gray-600">Test the new unified notification system</p>
    </div>

    <!-- Demo Buttons -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">JavaScript Notifications (No Page Reload)</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <button onclick="showSuccess('This is a success message!')" 
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium transition-colors">
                <i class="fas fa-check-circle mr-2"></i>Show Success
            </button>
            
            <button onclick="showError('This is an error message!')" 
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-medium transition-colors">
                <i class="fas fa-exclamation-circle mr-2"></i>Show Error
            </button>
            
            <button onclick="showWarning('This is a warning message!')" 
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-3 rounded-lg font-medium transition-colors">
                <i class="fas fa-exclamation-triangle mr-2"></i>Show Warning
            </button>
            
            <button onclick="showInfo('This is an info message!')" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium transition-colors">
                <i class="fas fa-info-circle mr-2"></i>Show Info
            </button>
        </div>
    </div>

    <!-- Server-side Notifications Demo -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Server-side Notifications (With Page Reload)</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <form method="POST" action="{{ route('demo.notification') }}">
                @csrf
                <input type="hidden" name="type" value="success">
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-check-circle mr-2"></i>Test Success
                </button>
            </form>
            
            <form method="POST" action="{{ route('demo.notification') }}">
                @csrf
                <input type="hidden" name="type" value="error">
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-exclamation-circle mr-2"></i>Test Error
                </button>
            </form>
            
            <form method="POST" action="{{ route('demo.notification') }}">
                @csrf
                <input type="hidden" name="type" value="warning">
                <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Test Warning
                </button>
            </form>
            
            <form method="POST" action="{{ route('demo.notification') }}">
                @csrf
                <input type="hidden" name="type" value="info">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-info-circle mr-2"></i>Test Info
                </button>
            </form>
        </div>
    </div>

    <!-- Features Overview -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Notification System Features</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-medium text-gray-900 mb-2">JavaScript Notifications</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• No page reload required</li>
                    <li>• Instant feedback</li>
                    <li>• Auto-dismiss after 8 seconds</li>
                    <li>• Manual dismiss with close button</li>
                    <li>• Consistent styling across all pages</li>
                </ul>
            </div>
            <div>
                <h3 class="font-medium text-gray-900 mb-2">Server-side Notifications</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Session-based flash messages</li>
                    <li>• Form validation errors</li>
                    <li>• Automatic inclusion in all dashboard pages</li>
                    <li>• Support for success, error, warning, info</li>
                    <li>• Manual dismiss with close button</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
