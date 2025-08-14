<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - InsightEdu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .error-illustration {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translatey(0px);
            }

            50% {
                transform: translatey(-20px);
            }

            100% {
                transform: translatey(0px);
            }
        }

        .btn-hover {
            transition: all 0.3s ease;
        }

        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .star {
            animation: twinkle 2s infinite;
        }

        @keyframes twinkle {

            0%,
            100% {
                opacity: 0.3;
            }

            50% {
                opacity: 1;
            }
        }

        .star:nth-child(odd) {
            animation-delay: 1s;
        }
    </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="star absolute top-20 left-10 w-2 h-2 bg-white rounded-full"></div>
        <div class="star absolute top-40 right-20 w-1 h-1 bg-white rounded-full"></div>
        <div class="star absolute bottom-32 left-1/4 w-2 h-2 bg-white rounded-full"></div>
        <div class="star absolute top-60 left-1/3 w-1 h-1 bg-white rounded-full"></div>
        <div class="star absolute bottom-20 right-1/3 w-2 h-2 bg-white rounded-full"></div>
        <div class="star absolute top-32 right-1/2 w-1 h-1 bg-white rounded-full"></div>
    </div>

    <div class="container mx-auto px-6 text-center relative z-10">
        <div class="max-w-4xl mx-auto">
            <!-- Error Illustration -->
            <div class="error-illustration mb-8">
                <div class="relative">
                    <!-- Main 404 Text -->
                    <div class="text-white text-9xl md:text-[12rem] font-bold opacity-20 mb-4">
                        404
                    </div>

                    <!-- Floating Elements -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="relative">
                            <!-- Book Icon -->
                            <div class="bg-white bg-opacity-20 rounded-full p-8 mb-4 backdrop-blur-sm">
                                <i class="fas fa-book-open text-6xl text-white"></i>
                            </div>

                            <!-- Floating Question Mark -->
                            <div class="absolute -top-4 -right-4 bg-yellow-400 rounded-full w-12 h-12 flex items-center justify-center text-yellow-900 text-xl font-bold">
                                ?
                            </div>

                            <!-- Floating Magnifying Glass -->
                            <div class="absolute -bottom-2 -left-4 bg-blue-400 rounded-full w-10 h-10 flex items-center justify-center text-blue-900 text-lg">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div class="mb-8">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">
                    Oops! Page Not Found
                </h1>
                <p class="text-xl md:text-2xl text-white text-opacity-90 mb-2">
                    The page you're looking for seems to have wandered off...
                </p>
                <p class="text-lg text-white text-opacity-75">
                    Don't worry, even the best students sometimes take a wrong turn!
                </p>
            </div>

            <!-- Search Suggestion -->
            <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-2xl p-6 mb-8 max-w-2xl mx-auto">
                <h3 class="text-xl font-semibold text-white mb-4">
                    <i class="fas fa-lightbulb mr-2 text-yellow-300"></i>
                    Let's help you find what you need
                </h3>
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" placeholder="Search for courses, assignments, or resources..."
                            class="w-full px-4 py-3 rounded-lg border-0 focus:ring-2 focus:ring-white focus:ring-opacity-50 text-gray-900 placeholder-gray-500">
                    </div>
                    <button class="btn-hover bg-white text-purple-600 px-6 py-3 rounded-lg font-semibold">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <a href="{{ route('dashboard') }}" class="btn-hover bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 text-white hover:bg-opacity-20 transition-all duration-300">
                    <i class="fas fa-home text-3xl mb-3 text-blue-300"></i>
                    <h4 class="font-semibold text-lg mb-2">Dashboard</h4>
                    <p class="text-sm text-white text-opacity-75">Return to your dashboard</p>
                </a>

                <a href="{{ route('courses.manage') }}" class="btn-hover bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 text-white hover:bg-opacity-20 transition-all duration-300">
                    <i class="fas fa-graduation-cap text-3xl mb-3 text-green-300"></i>
                    <h4 class="font-semibold text-lg mb-2">Courses</h4>
                    <p class="text-sm text-white text-opacity-75">Manage your courses</p>
                </a>

                <a href="{{ route('assignments.index') }}" class="btn-hover bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 text-white hover:bg-opacity-20 transition-all duration-300">
                    <i class="fas fa-tasks text-3xl mb-3 text-yellow-300"></i>
                    <h4 class="font-semibold text-lg mb-2">Assignments</h4>
                    <p class="text-sm text-white text-opacity-75">Check your assignments</p>
                </a>
            </div>

            <!-- Help Section -->
            <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-2xl p-6 max-w-2xl mx-auto">
                <h3 class="text-xl font-semibold text-white mb-4">
                    <i class="fas fa-question-circle mr-2 text-blue-300"></i>
                    Still need help?
                </h3>
                <p class="text-white text-opacity-75 mb-4">
                    If you believe this is an error or need assistance finding what you're looking for, our support team is here to help.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="mailto:support@insightedu.com" class="btn-hover bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold">
                        <i class="fas fa-envelope mr-2"></i>Contact Support
                    </a>
                    <a href="{{ route('help.index') }}" class="btn-hover bg-transparent border-2 border-white text-white hover:bg-white hover:text-purple-600 px-6 py-3 rounded-lg font-semibold">
                        <i class="fas fa-book mr-2"></i>Help Center
                    </a>
                </div>
            </div>

            <!-- Fun Error Codes -->
            <div class="mt-8 text-center">
                <p class="text-white text-opacity-50 text-sm">
                    Error Code: PAGE_NOT_FOUND_404 | Time: {{ now()->format('Y-m-d H:i:s') }}
                </p>
            </div>
        </div>
    </div>

    <!-- JavaScript for Interactive Elements -->
    <script>
        // Add some interactive hover effects
        document.querySelectorAll('.btn-hover').forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px) scale(1.02)';
            });

            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Search functionality
        document.querySelector('input[type="text"]').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchTerm = this.value;
                if (searchTerm.trim()) {
                    // Redirect to search results page
                    window.location.href = `/search?q=${encodeURIComponent(searchTerm)}`;
                }
            }
        });

        // Add floating animation to error illustration on hover
        document.querySelector('.error-illustration').addEventListener('mouseenter', function() {
            this.style.animationDuration = '3s';
        });

        document.querySelector('.error-illustration').addEventListener('mouseleave', function() {
            this.style.animationDuration = '6s';
        });

        // Random star twinkling
        document.querySelectorAll('.star').forEach(star => {
            const randomDelay = Math.random() * 3;
            star.style.animationDelay = randomDelay + 's';
        });
    </script>
</body>

</html>