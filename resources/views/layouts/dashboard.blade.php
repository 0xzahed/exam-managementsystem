<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - InsightEdu</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Vite Assets with Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 64px; /* Height of navbar */
            left: 0;
            width: 280px;
            height: calc(100vh - 64px);
            background: white;
            border-right: 1px solid #e5e7eb;
            z-index: 30;
        }
        
        .main-content {
            margin-left: 280px;
            margin-top: 64px;
            min-height: calc(100vh - 64px);
        }
        
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
            }
        }

        /* Scrollbar styles */
        .scrollbar-thin {
            scrollbar-width: thin;
        }
        .scrollbar-thumb-gray-300::-webkit-scrollbar-thumb {
            background-color: #d1d5db;
        }
        .scrollbar-track-gray-100::-webkit-scrollbar-track {
            background-color: #f3f4f6;
        }
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    @include('components.navbar')

    <!-- Sidebar -->
    @include('components.sidebar')

    <!-- Main Content -->
    <main class="main-content">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Enhanced Notifications -->
                @include('components.notifications')
                
                @yield('content')
            </div>
        </div>
    </main>

    <!-- Toast Notifications -->
    @include('components.toast')

    <!-- Sidebar Toggle Script -->
    <script>
        // Prevent back button to login/register pages after authentication
        (function() {
            if (window.history && window.history.pushState) {
                window.addEventListener('load', function() {
                    // Replace current state to prevent going back to login
                    window.history.replaceState({}, '', window.location.href);
                    
                    window.addEventListener('popstate', function(e) {
                        // Prevent going back to login/register pages
                        window.history.pushState({}, '', window.location.href);
                    });
                });
            }
        })();
        
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const closeBtn = document.getElementById('closeSidebar');
            const mainContent = document.querySelector('.main-content');

            function toggleSidebar() {
                sidebar.classList.toggle('-translate-x-full');
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', toggleSidebar);
            }
            
            if (closeBtn) {
                closeBtn.addEventListener('click', toggleSidebar);
            }

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isToggleBtn = toggleBtn && toggleBtn.contains(event.target);
                
                if (!isClickInsideSidebar && !isToggleBtn && window.innerWidth < 1024) {
                    sidebar.classList.add('-translate-x-full');
                }
            });
        });
    </script>

    @yield('scripts')
</body>
</html>
