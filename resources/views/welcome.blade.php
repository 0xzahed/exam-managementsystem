<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InsightEdu - Welcome</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex items-center justify-center min-h-screen bg-gray-50">
    <div class="max-w-4xl px-4 mx-auto text-center">

        <!-- Image Slider -->
        <div class="mx-auto mb-8 overflow-hidden shadow-2xl slider-container w-80 h-80 rounded-2xl">
            <div class="slide active">
                <svg class="w-full h-full" viewBox="0 0 400 400" fill="none">
                    <rect width="400" height="400" fill="url(#grad1)" />
                    <circle cx="200" cy="150" r="60" fill="white" opacity="0.9" />
                    <rect x="120" y="240" width="160" height="8" rx="4" fill="white" opacity="0.8" />
                    <rect x="140" y="260" width="120" height="6" rx="3" fill="white" opacity="0.6" />
                    <rect x="160" y="280" width="80" height="6" rx="3" fill="white" opacity="0.6" />
                    <path d="M170 130 L190 150 L230 110" stroke="white" stroke-width="4" stroke-linecap="round" fill="none" />
                    <defs>
                        <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <div class="slide">
                <svg class="w-full h-full" viewBox="0 0 400 400" fill="none">
                    <rect width="400" height="400" fill="url(#grad2)" />
                    <rect x="80" y="120" width="240" height="160" rx="12" fill="white" opacity="0.9" />
                    <rect x="100" y="140" width="60" height="40" rx="6" fill="#667eea" />
                    <rect x="170" y="140" width="60" height="40" rx="6" fill="#764ba2" />
                    <rect x="240" y="140" width="60" height="40" rx="6" fill="#667eea" />
                    <rect x="100" y="200" width="200" height="4" rx="2" fill="#667eea" opacity="0.6" />
                    <rect x="100" y="220" width="160" height="4" rx="2" fill="#764ba2" opacity="0.6" />
                    <rect x="100" y="240" width="120" height="4" rx="2" fill="#667eea" opacity="0.6" />
                    <defs>
                        <linearGradient id="grad2" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#764ba2;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#667eea;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <div class="slide">
                <svg class="w-full h-full" viewBox="0 0 400 400" fill="none">
                    <rect width="400" height="400" fill="url(#grad3)" />
                    <circle cx="200" cy="200" r="80" fill="white" opacity="0.9" />
                    <path d="M160 200 L180 220 L240 160" stroke="#667eea" stroke-width="6" stroke-linecap="round" fill="none" />
                    <circle cx="120" cy="120" r="20" fill="white" opacity="0.7" />
                    <circle cx="280" cy="120" r="20" fill="white" opacity="0.7" />
                    <circle cx="120" cy="280" r="20" fill="white" opacity="0.7" />
                    <circle cx="280" cy="280" r="20" fill="white" opacity="0.7" />
                    <defs>
                        <linearGradient id="grad3" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                            <stop offset="50%" style="stop-color:#764ba2;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#667eea;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>

        <!-- Welcome Content -->
        <div class="fade-in">
            <h1 class="mb-6 text-5xl font-bold text-gray-800">
                Welcome to
                <span class="text-purple-600 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text">
                    InsightEdu
                </span>
            </h1>
            <p class="max-w-2xl mx-auto mb-12 text-xl text-gray-600">
                Complete Education Management Platform for Assignments, Exams, and Courses
            </p>
            <!-- Get Started Button -->
            <a href="{{ route('login') }}" class="px-12 py-4 text-xl font-semibold text-white bg-purple-600 shadow-lg rounded-xl hover:bg-purple-700 transition">Get Started</a>
        </div>

        <!-- Slider Dots -->
        <div class="flex justify-center mt-12 space-x-3">
            <button onclick="currentSlide(1)" class="w-3 h-3 transition-all bg-indigo-600 rounded-full slider-dot"></button>
            <button onclick="currentSlide(2)" class="w-3 h-3 transition-all bg-gray-300 rounded-full slider-dot"></button>
            <button onclick="currentSlide(3)" class="w-3 h-3 transition-all bg-gray-300 rounded-full slider-dot"></button>
        </div>
    </div>
</body>

</html>