<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InsightEdu - Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
        }

        .input-focus:focus {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="min-h-screen gradient-bg flex items-center justify-center p-4">
    <!-- Flash messages container -->
    <div id="flash-messages" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 space-y-2">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-3 rounded-lg shadow-lg">
                <div class="flex items-center space-x-2 mb-2">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="font-medium">Please fix the following errors:</span>
                </div>
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Container -->
    <div class="w-full max-w-md">
        <!-- Card -->
        <div class="glass-effect backdrop-blur-lg bg-white/10 rounded-2xl shadow-2xl border border-white/20 p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4">
                    <i class="fas fa-lock text-2xl text-white"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Forgot Password?</h1>
                <p class="text-white/70">No worries! Enter your email and we'll send you a reset link.</p>
            </div>

            <!-- Form -->
            <form action="{{ route('password.email') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-white/60"></i>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required 
                               class="input-focus w-full px-4 py-3 pl-12 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30 transition-all duration-300" 
                               placeholder="Enter your email address">
                    </div>
                    @error('email')
                        <div class="text-red-300 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-xl flex items-center justify-center space-x-2">
                    <i class="fas fa-paper-plane"></i>
                    <span>Send Reset Link</span>
                </button>

                <!-- Back to Login -->
                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-indigo-200 hover:text-white underline flex items-center justify-center space-x-1">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Login</span>
                    </a>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-white/60 text-sm">
                Don't have an account? 
                <a href="{{ route('register') }}" class="text-indigo-200 hover:text-white underline">Sign up here</a>
            </p>
        </div>
    </div>

    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            const flashMessages = document.getElementById('flash-messages');
            if (flashMessages) {
                flashMessages.style.opacity = '0';
                flashMessages.style.transition = 'opacity 0.5s';
                setTimeout(() => {
                    flashMessages.style.display = 'none';
                }, 500);
            }
        }, 5000);
    </script>
</body>
</html>
