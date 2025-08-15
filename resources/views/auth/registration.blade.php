<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InsightEdu - Registration</title>
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
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            border-color: rgba(255, 255, 255, 0.4) !important;
            background: rgba(255, 255, 255, 0.15) !important;
        }

        .input-focus {
            transition: all 0.3s ease;
        }

        .input-focus:hover {
            border-color: rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.12);
        }

        .role-card {
            transition: all 0.3s ease;
            position: relative;
            cursor: pointer;
        }

        .role-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.12);
        }

        .role-card.selected {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
            color: white;
            border-color: #4f46e5 !important;
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(79, 70, 229, 0.32);
        }

        .role-card.selected::before {
            content: '✓';
            position: absolute;
            top: -10px;
            right: -10px;
            background: #10b981;
            color: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            border: 3px solid white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .role-card.selected i {
            color: white !important;
        }

        .role-card.selected p {
            color: white !important;
        }
    </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center p-4" @if(session('error')) data-session-error="{{ e(session('error')) }}" @endif @if(session('success')) data-session-success="{{ e(session('success')) }}" @endif>
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-lg mb-4">
                <i class="fas fa-graduation-cap text-2xl text-indigo-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">InsightEdu</h1>
            <p class="text-indigo-100">Create your account to get started</p>
        </div>

        <!-- Error Messages at Top -->
        @if($errors->any())
        <div class="mb-6 bg-red-500/20 border border-red-400/30 rounded-xl p-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-400 mr-3"></i>
                <div>
                    <h3 class="text-red-400 font-medium">Registration Failed</h3>
                    <ul class="mt-2 text-red-300 text-sm list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-500/20 border border-red-400/30 rounded-xl p-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-400 mr-3"></i>
                <div>
                    <h3 class="text-red-400 font-medium">Access Denied</h3>
                    <p class="text-red-300 text-sm">{{ session('error') }}</p>
                </div>
            </div>
        </div>
        @endif

        @if(session('success'))
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
        <!-- Registration Form -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl border border-white/20">
            <form id="registrationForm" class="space-y-6" method="POST" action="{{ route('register.submit') }}">
                @csrf
                <!-- Role Selection -->
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-white mb-3">Select Your Role</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="role-card bg-white/10 p-4 rounded-xl cursor-pointer border border-white/20 hover:bg-white/15 transition-all" data-role="student">
                            <div class="text-center">
                                <i class="fas fa-user-graduate text-3xl mb-2 text-white"></i>
                                <p class="text-sm font-semibold text-white">Student</p>
                                <p class="text-xs text-white/70 mt-1">Enroll in courses</p>
                            </div>
                        </div>
                        <div class="role-card bg-white/10 p-4 rounded-xl cursor-pointer border border-white/20 hover:bg-white/15 transition-all" data-role="instructor">
                            <div class="text-center">
                                <i class="fas fa-chalkboard-teacher text-3xl mb-2 text-white"></i>
                                <p class="text-sm font-semibold text-white">Instructor</p>
                                <p class="text-xs text-white/70 mt-1">Create & manage courses</p>
                            </div>
                        </div>
                    </div>
                    <div id="roleError" class="text-red-300 text-sm hidden">
                        <i class="fas fa-exclamation-circle mr-1"></i>Please select a role to continue
                    </div>
                    <!-- Hidden input for role -->
                    <input type="hidden" id="selectedRole" name="role" value="{{ old('role') }}">
                </div>
                <!-- Personal Information -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">First Name</label>
                        <input type="text" id="firstName" name="first_name" required class="input-focus w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30 transition-all duration-300" placeholder="Abu" value="{{ old('first_name') }}">
                        @error('first_name')<div class="text-red-300 text-sm mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">Last Name</label>
                        <input type="text" id="lastName" name="last_name" required class="input-focus w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30 transition-all duration-300" placeholder="Zahed" value="{{ old('last_name') }}">
                        @error('last_name')<div class="text-red-300 text-sm mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-white mb-2">University Email Address</label>
                    <input type="email" id="email" name="email" required class="input-focus w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30 transition-all duration-300" placeholder="your.email@diu.edu.bd" value="{{ old('email') }}">
                    @error('email')<div class="text-red-300 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <!-- Student ID (for students) / Employee ID (for instructors) -->
                <div id="idField" class="hidden">
                    <label id="idLabel" class="block text-sm font-medium text-white mb-2">Student ID</label>
                    <input type="text" id="userId" name="user_id" class="input-focus w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30 transition-all duration-300" placeholder="221-15-4716" value="{{ old('user_id') }}">
                    @error('user_id')<div class="text-red-300 text-sm mt-1">{{ $message }}</div>@enderror
                    
                    <!-- Hidden backend-specific fields -->
                    <input type="hidden" id="student_id" name="student_id" value="{{ old('student_id') }}">
                    <input type="hidden" id="employee_id" name="employee_id" value="{{ old('employee_id') }}">
                </div>
                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required class="input-focus w-full px-4 py-3 pr-12 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30 transition-all duration-300" placeholder="Create a strong password">
                        <button type="button" id="togglePassword" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white/60 hover:text-white">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')<div class="text-red-300 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Confirm Password</label>
                    <div class="relative">
                        <input type="password" id="password_confirmation" name="password_confirmation" required class="input-focus w-full px-4 py-3 pr-12 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30 transition-all duration-300" placeholder="Confirm your password">
                        <button type="button" id="togglePasswordConfirmation" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white/60 hover:text-white">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                </div>
                <!-- Terms and Conditions -->
                <div class="flex items-start space-x-3">
                    <input type="checkbox" id="terms" name="terms" required class="mt-1 w-4 h-4 text-indigo-600 bg-white/10 border-white/20 rounded focus:ring-indigo-500">
                    <label for="terms" class="text-sm text-white/90">
                        I agree to the <a href="#" class="text-indigo-200 hover:text-white underline">Terms of Service</a>
                        and <a href="#" class="text-indigo-200 hover:text-white underline">Privacy Policy</a>
                    </label>
                </div>
                <!-- Submit Button -->
                <button type="submit" id="submitBtn" class="w-full bg-white text-indigo-600 py-3 px-6 rounded-xl font-semibold hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-white/30 transition-all duration-300 transform hover:scale-105">
                    <span id="submitText">Create Account</span>
                    <i id="submitLoader" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                </button>
            </form>

            <!-- Divider -->
            <div class="flex items-center my-6">
                <div class="flex-1 border-t border-white/20"></div>
                <span class="px-4 text-white/60 text-sm">or</span>
                <div class="flex-1 border-t border-white/20"></div>
            </div>

            <!-- Google & GitHub Signup Buttons -->
            <div class="space-y-4">
                <!-- Google Signup Button -->
                <a href="{{ route('google.register') }}" class="w-full bg-white hover:bg-gray-50 text-gray-700 py-3 px-6 rounded-xl font-semibold focus:outline-none focus:ring-2 focus:ring-white/30 transition-all duration-300 transform hover:scale-105 flex items-center justify-center border border-gray-300">
                    <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>
                    Sign up with Google
                </a>

                <!-- GitHub Signup Button -->
                <a href="{{ route('github.register') }}" class="w-full bg-gray-900 hover:bg-gray-800 text-white py-3 px-6 rounded-xl font-semibold focus:outline-none focus:ring-2 focus:ring-white/30 transition-all duration-300 transform hover:scale-105 flex items-center justify-center border border-gray-700">
                    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0C5.374 0 0 5.373 0 12 0 17.302 3.438 21.8 8.207 23.387c.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z" />
                    </svg>
                    Sign up with GitHub
                </a>

                <!-- GitHub Account Selection Helper -->
                <div class="text-center mt-2">
                    <p class="text-xs text-white/50 mb-1">
                        <i class="fas fa-user-friends mr-1"></i>
                        Need to switch GitHub account?
                    </p>
                    <a href="{{ route('github.logout.register') }}" class="text-xs text-blue-400 hover:text-blue-300 underline">
                        Logout from GitHub first
                    </a>
                </div>

                <div class="text-center">
                    <p class="text-xs text-white/60">
                        <i class="fas fa-info-circle mr-1"></i>
                        Only @diu.edu.bd email addresses are allowed
                    </p>
                </div>
            </div>

            <!-- Login Link -->
            <div class="text-center mt-6">
                <p class="text-white/80">Already have an account?
                    <a href="{{ route('login') }}" class="text-indigo-200 hover:text-white font-medium underline">Sign In</a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>
