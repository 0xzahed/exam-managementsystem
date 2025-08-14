<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - InsightEdu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }

        .error-illustration {
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .btn-hover {
            transition: all 0.3s ease;
        }

        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .glitch {
            animation: glitch 2s infinite;
        }

        @keyframes glitch {

            0%,
            100% {
                transform: translate(0);
            }

            20% {
                transform: translate(-2px, 2px);
            }

            40% {
                transform: translate(-2px, -2px);
            }

            60% {
                transform: translate(2px, 2px);
            }

            80% {
                transform: translate(2px, -2px);
            }
        }

        .spark {
            animation: spark 3s infinite;
        }

        @keyframes spark {

            0%,
            100% {
                opacity: 0;
                transform: scale(0);
            }

            50% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .spark:nth-child(odd) {
            animation-delay: 1s;
        }

        .spark:nth-child(even) {
            animation-delay: 2s;
        }
    </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="spark absolute top-20 left-10 w-3 h-3 bg-yellow-400 rounded-full"></div>
        <div class="spark absolute top-40 right-20 w-2 h-2 bg-orange-400 rounded-full"></div>
        <div class="spark absolute bottom-32 left-1/4 w-3 h-3 bg-yellow-400 rounded-full"></div>
        <div class="spark absolute top-60 left-1/3 w-2 h-2 bg-orange-400 rounded-full"></div>
        <div class="spark absolute bottom-20 right-1/3 w-3 h-3 bg-yellow-400 rounded-full"></div>
        <div class="spark absolute top-32 right-1/2 w-2 h-2 bg-orange-400 rounded-full"></div>
    </div>

    <div class="container mx-auto px-6 text-center relative z-10">
        <div class="max-w-4xl mx-auto">
            <!-- Error Illustration -->
            <div class="error-illustration mb-8">
                <div class="relative">
                    <!-- Main 500 Text with Glitch Effect -->
                    <div class="glitch text-white text-9xl md:text-[12rem] font-bold opacity-20 mb-4">
                        500
                    </div>

                    <!-- Floating Elements -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="relative">
                            <!-- Server Icon -->
                            <div class="bg-white bg-opacity-20 rounded-full p-8 mb-4 backdrop-blur-sm">
                                <i class="fas fa-server text-6xl text-white"></i>
                            </div>

                            <!-- Floating Error Symbol -->
                            <div class="absolute -top-4 -right-4 bg-yellow-400 rounded-full w-12 h-12 flex items-center justify-center text-red-900 text-xl font-bold">
                                !
                            </div>

                            <!-- Floating Wrench -->
                            <div class="absolute -bottom-2 -left-4 bg-orange-400 rounded-full w-10 h-10 flex items-center justify-center text-orange-900 text-lg">
                                <i class="fas fa-wrench"></i>
                            </div>

                            <!-- Spark Effects -->
                            <div class="absolute top-2 right-2 w-2 h-2 bg-yellow-300 rounded-full spark"></div>
                            <div class="absolute bottom-4 left-2 w-1 h-1 bg-orange-300 rounded-full spark"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div class="mb-8">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">
                    Something Went Wrong
                </h1>
                <p class="text-xl md:text-2xl text-white text-opacity-90 mb-2">
                    Our servers are experiencing some technical difficulties
                </p>
                <p class="text-lg text-white text-opacity-75">
                    Don't worry - our technical team has been notified and is working on it!
                </p>
            </div>

            <!-- Status Information -->
            <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-2xl p-6 mb-8 max-w-2xl mx-auto">
                <h3 class="text-xl font-semibold text-white mb-4">
                    <i class="fas fa-info-circle mr-2 text-blue-300"></i>
                    What happened?
                </h3>
                <div class="text-left space-y-3">
                    <div class="flex items-center text-white text-opacity-75">
                        <i class="fas fa-check-circle text-green-300 mr-3"></i>
                        <span>The error has been logged and reported</span>
                    </div>
                    <div class="flex items-center text-white text-opacity-75">
                        <i class="fas fa-tools text-orange-300 mr-3"></i>
                        <span>Our development team is investigating</span>
                    </div>
                    <div class="flex items-center text-white text-opacity-75">
                        <i class="fas fa-clock text-blue-300 mr-3"></i>
                        <span>Expected resolution time: Within 30 minutes</span>
                    </div>
                </div>
            </div>

            <!-- Retry Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="btn-hover bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 text-white hover:bg-opacity-20 transition-all duration-300 cursor-pointer" onclick="location.reload()">
                    <i class="fas fa-redo text-3xl mb-3 text-green-300"></i>
                    <h4 class="font-semibold text-lg mb-2">Try Again</h4>
                    <p class="text-sm text-white text-opacity-75">Refresh the page to retry your request</p>
                </div>

                <a href="{{ route('dashboard.student') }}" class="btn-hover bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 text-white hover:bg-opacity-20 transition-all duration-300">
                    <i class="fas fa-home text-3xl mb-3 text-blue-300"></i>
                    <h4 class="font-semibold text-lg mb-2">Go Home</h4>
                    <p class="text-sm text-white text-opacity-75">Return to your dashboard</p>
                </a>
            </div>

            <!-- Alternative Actions -->
            <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-2xl p-6 mb-8 max-w-2xl mx-auto">
                <h3 class="text-xl font-semibold text-white mb-4">
                    <i class="fas fa-route mr-2 text-yellow-300"></i>
                    While you wait, you can:
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <a href="{{ route('courses.manage') }}" class="btn-hover bg-white bg-opacity-10 rounded-lg p-4 text-white hover:bg-opacity-20 transition-all duration-300">
                        <i class="fas fa-book text-xl mb-2 text-purple-300"></i>
                        <p class="text-sm font-medium">My Courses</p>
                    </a>
                    <a href="{{ route('assignments.index') }}" class="btn-hover bg-white bg-opacity-10 rounded-lg p-4 text-white hover:bg-opacity-20 transition-all duration-300">
                        <i class="fas fa-tasks text-xl mb-2 text-green-300"></i>
                        <p class="text-sm font-medium">View Assignments</p>
                    </a>
                    <a href="{{ route('profile.settings') }}" class="btn-hover bg-white bg-opacity-10 rounded-lg p-4 text-white hover:bg-opacity-20 transition-all duration-300">
                        <i class="fas fa-user text-xl mb-2 text-blue-300"></i>
                        <p class="text-sm font-medium">Profile Settings</p>
                    </a>
                </div>
            </div>

            <!-- Contact Support -->
            <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-2xl p-6 max-w-2xl mx-auto">
                <h3 class="text-xl font-semibold text-white mb-4">
                    <i class="fas fa-life-ring mr-2 text-orange-300"></i>
                    Need immediate assistance?
                </h3>
                <p class="text-white text-opacity-75 mb-4">
                    If this error persists or you need urgent help, please contact our support team with the error details below.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-4">
                    <a href="mailto:support@insightedu.com?subject=Server Error 500&body=Error occurred at: {{ request()->fullUrl() }}%0ATime: {{ now()->format('Y-m-d H:i:s') }}"
                        class="btn-hover bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold">
                        <i class="fas fa-envelope mr-2"></i>Report Error
                    </a>
                    <button onclick="copyErrorDetails()" class="btn-hover bg-transparent border-2 border-white text-white hover:bg-white hover:text-red-600 px-6 py-3 rounded-lg font-semibold">
                        <i class="fas fa-copy mr-2"></i>Copy Error Details
                    </button>
                </div>

                <!-- Error Details (Hidden) -->
                <div id="error-details" class="hidden text-left">
                    <div class="bg-black bg-opacity-30 rounded p-3 text-xs text-white font-mono">
                        URL: {{ request()->fullUrl() }}<br>
                        Time: {{ now()->format('Y-m-d H:i:s') }}<br>
                        User Agent: <span id="user-agent"></span><br>
                        Error ID: {{ Str::uuid() }}
                    </div>
                </div>
            </div>

            <!-- Automatic Retry Counter -->
            <div class="mt-8 text-center">
                <p class="text-white text-opacity-50 text-sm mb-2">
                    Page will automatically retry in <span id="retry-counter" class="font-bold text-white">60</span> seconds
                </p>
                <div class="w-64 bg-white bg-opacity-20 rounded-full h-2 mx-auto">
                    <div id="retry-progress" class="bg-white h-2 rounded-full transition-all duration-1000" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Interactive Elements -->
    <script>
        // Set user agent
        document.getElementById('user-agent').textContent = navigator.userAgent;

        // Copy error details functionality
        function copyErrorDetails() {
            const errorDetails = `
URL: ${window.location.href}
Time: {{ now()->format('Y-m-d H:i:s') }}
User Agent: ${navigator.userAgent}
Error ID: {{ Str::uuid() }}
            `.trim();

            navigator.clipboard.writeText(errorDetails).then(() => {
                // Show temporary feedback
                const button = event.target.closest('button');
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check mr-2"></i>Copied!';
                button.classList.add('bg-green-500');

                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('bg-green-500');
                }, 2000);
            });
        }

        // Automatic retry countdown
        let retryCount = 60;
        const retryCounterElement = document.getElementById('retry-counter');
        const retryProgressElement = document.getElementById('retry-progress');

        const retryTimer = setInterval(() => {
            retryCount--;
            retryCounterElement.textContent = retryCount;

            // Update progress bar
            const progress = ((60 - retryCount) / 60) * 100;
            retryProgressElement.style.width = progress + '%';

            if (retryCount <= 0) {
                clearInterval(retryTimer);
                location.reload();
            }
        }, 1000);

        // Add hover effects
        document.querySelectorAll('.btn-hover').forEach(button => {
            button.addEventListener('mouseenter', function() {
                if (!this.classList.contains('cursor-pointer')) {
                    this.style.transform = 'translateY(-2px) scale(1.02)';
                }
            });

            button.addEventListener('mouseleave', function() {
                if (!this.classList.contains('cursor-pointer')) {
                    this.style.transform = 'translateY(0) scale(1)';
                }
            });
        });

        // Stop auto-retry if user interacts with page
        let userInteracted = false;
        document.addEventListener('click', () => {
            if (!userInteracted) {
                userInteracted = true;
                clearInterval(retryTimer);
                document.querySelector('.mt-8.text-center').style.opacity = '0.5';
            }
        });

        // Random spark timing
        document.querySelectorAll('.spark').forEach(spark => {
            const randomDelay = Math.random() * 4;
            spark.style.animationDelay = randomDelay + 's';
        });
    </script>
</body>

</html>