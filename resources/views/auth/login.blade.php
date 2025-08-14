<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InsightEdu - Login</title>
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

        .role-tab {
            transition: all 0.2s ease;
        }

        .role-tab.active {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .shape {
            position: absolute;
            opacity: 0.05;
            /* Softer, slower animation */
            animation: float 12s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            top: 20%;
            right: 10%;
            animation-delay: 3s;
        }

        .shape:nth-child(3) {
            bottom: 10%;
            left: 20%;
            animation-delay: 6s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-4px) rotate(20deg); }
        }
    </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center p-4 relative" @if(session('error')) data-session-error="{{ e(session('error')) }}" @endif @if(session('success')) data-session-success="{{ e(session('success')) }}" @endif>
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape w-32 h-32 bg-white rounded-full"></div>
        <div class="shape w-24 h-24 bg-white rounded-full"></div>
        <div class="shape w-16 h-16 bg-white rounded-full"></div>
    </div>
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-lg mb-4">
                <i class="fas fa-graduation-cap text-3xl text-indigo-600"></i>
            </div>
            <h1 class="text-4xl font-bold text-white mb-2">InsightEdu</h1>
            <p class="text-indigo-100">Welcome back! Please sign in to continue</p>
        </div>

        <!-- Error Messages at Top -->
        @if ($errors->any())
        <div class="mb-6 bg-red-500/20 border border-red-400/30 rounded-xl p-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-400 mr-3"></i>
                <div>
                    <h3 class="text-red-400 font-medium">Login Failed</h3>
                    <p class="text-red-300 text-sm">{{ $errors->first() }}</p>
                </div>
            </div>
        </div>
        @endif

        @if (session('error'))
        <div class="mb-6 bg-red-500/20 border border-red-400/30 rounded-xl p-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-400 mr-3"></i>
                <div class="flex-1">
                    <h3 class="text-red-400 font-medium">Access Denied</h3>
                    <p class="text-red-300 text-sm">{{ session('error') }}</p>
                    @if (session('redirect_to_register'))
                    <div class="mt-3">
                        <form action="{{ route('auth.google.register') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                <i class="fas fa-user-plus mr-2"></i>Go to Registration
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if (session('success'))
        <div class="mb-6 bg-green-500/20 border border-green-400/30 rounded-xl p-4">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-400 mr-3"></i>
                <div>
                    <h3 class="text-green-400 font-medium">Success!</h3>
                    <p class="text-green-300 text-sm">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif
        <!-- Login Form -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl border border-white/20">
            <!-- Role Selection Tabs -->
            <div class="flex bg-white/10 rounded-xl p-1 mb-6 role-tab-container">
                <button class="role-tab flex-1 py-2 px-4 rounded-lg text-sm font-medium text-white/70 active" data-role="student">
                    <i class="fas fa-user-graduate mr-2"></i>Student
                </button>
                <button class="role-tab flex-1 py-2 px-4 rounded-lg text-sm font-medium text-white/70" data-role="instructor">
                    <i class="fas fa-chalkboard-teacher mr-2"></i>Instructor
                </button>
                <button class="role-tab flex-1 py-2 px-4 rounded-lg text-sm font-medium text-white/70" data-role="admin">
                    <i class="fas fa-user-shield mr-2"></i>Admin
                </button>
            </div>
            <form id="loginForm" class="space-y-6" method="POST" action="{{ route('login.submit') }}">
                @csrf
                <input type="hidden" name="role" id="roleInput" value="student">
                <!-- Email/Username -->
                <div>
                    <label class="block text-sm font-medium text-white mb-2">
                        <span id="emailLabel">University Email Address</span>
                    </label>
                    <div class="relative">
                        <input type="email" id="emailInput" name="email" required class="input-focus w-full px-4 py-3 pl-12 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30 transition-all duration-300" placeholder="your.email@diu.edu.bd">
                        <i class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-white/60"></i>
                    </div>
                    <div id="emailError" class="text-red-300 text-sm mt-1 hidden">Please enter a valid university email address</div>
                </div>
                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Password</label>
                    <div class="relative">
                        <input type="password" id="passwordInput" name="password" required class="input-focus w-full px-4 py-3 pl-12 pr-12 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30 transition-all duration-300" placeholder="Enter your password">
                        <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-white/60"></i>
                        <button type="button" id="togglePassword" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white/60 hover:text-white">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="passwordError" class="text-red-300 text-sm mt-1 hidden">Password is required</div>
                </div>
                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" id="rememberMe" name="remember" class="w-4 h-4 text-indigo-600 bg-white/10 border-white/20 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-white/90">Remember me</span>
                    </label>
                    <a href="#" id="forgotPasswordLink" class="text-sm text-indigo-200 hover:text-white underline">
                        <i class="fas fa-lock mr-1"></i>Forgot password?
                    </a>
                </div>
                <!-- Login Button -->
                <button type="submit" id="loginButton" class="w-full bg-white text-indigo-600 py-3 px-6 rounded-xl font-semibold hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-white/30 transition-all duration-200 transform hover:scale-[1.01]">
                    <span id="loginText">Sign In</span>
                    <i id="loginLoader" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                </button>
            </form>

            <!-- Divider -->
            <div class="flex items-center my-6">
                <div class="flex-1 border-t border-white/20"></div>
                <span class="px-4 text-white/60 text-sm">or</span>
                <div class="flex-1 border-t border-white/20"></div>
            </div>

            <!-- Enhanced OAuth Buttons Section -->
            <div class="space-y-4">
                <!-- Google Login Button with Improved Design -->
                <div class="space-y-3">
                    <button onclick="loginWithGoogle('student')" class="group w-full bg-white hover:bg-gray-50 text-gray-700 py-4 px-6 rounded-2xl font-semibold focus:outline-none focus:ring-4 focus:ring-blue-300/50 transition-all duration-200 transform hover:scale-[1.01] hover:shadow-xl flex items-center justify-center border border-gray-200 shadow-lg">
                        <svg class="w-6 h-6 mr-4" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                        </svg>
                        <span class="text-lg">Continue with Google</span>
                    </button>
                </div>

                <!-- GitHub Login Button with Improved Design -->
                <a href="#" id="githubLogin" class="group w-full bg-gradient-to-r from-gray-900 to-gray-700 hover:from-gray-800 hover:to-gray-600 text-white py-4 px-6 rounded-2xl font-semibold focus:outline-none focus:ring-4 focus:ring-gray-500/50 transition-all duration-200 transform hover:scale-[1.01] hover:shadow-xl flex items-center justify-center border border-gray-600 shadow-lg">
                    <svg class="w-6 h-6 mr-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0C5.374 0 0 5.373 0 12 0 17.302 3.438 21.8 8.207 23.387c.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z" />
                    </svg>
                    <span class="text-lg">Continue with GitHub</span>
                </a>

                <!-- Info Notice with Enhanced Design -->
                <div class="bg-blue-50/10 backdrop-blur-sm border border-blue-200/20 rounded-xl p-4 text-center">
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5 text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm text-blue-200 font-medium">
                            Only @diu.edu.bd email addresses are allowed
                        </p>
                    </div>
                </div>
            </div>

            <!-- Register Link -->
            <div class="text-center mt-6">
                <p class="text-white/80">Don't have an account?
                    <a href="{{ route('register') }}" id="registerLink" class="text-indigo-200 hover:text-white font-medium underline">Create Account</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Prevent back button after logout
        (function() {
            if (window.history && window.history.pushState) {
                window.addEventListener('load', function() {
                    window.history.pushState({}, '', window.location.href);
                    
                    window.addEventListener('popstate', function() {
                        window.location.reload();
                    });
                });
            }
        })();
        
        // Clear all cached data
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for(let registration of registrations) {
                    registration.unregister();
                }
            });
        }
        
        // Clear storage
        if (typeof(Storage) !== "undefined") {
            sessionStorage.clear();
            localStorage.clear();
        }
    </script>
</body>
</html>
