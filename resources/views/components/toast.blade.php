@if(session('success') || session('error') || session('warning') || session('info'))
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-3">
        @if(session('success'))
            <div id="success-toast" class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center justify-between transform transition-all duration-500 ease-in-out translate-x-0 opacity-100 border-l-4 border-green-400">
                <div class="flex items-center">
                    <div class="bg-green-400 bg-opacity-20 p-2 rounded-full mr-3">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <span class="font-medium text-sm">{{ session('success') }}</span>
                </div>
                <button onclick="closeToast('success-toast')" class="ml-4 text-white hover:text-green-200 transition-colors p-1 rounded-full hover:bg-green-400 hover:bg-opacity-20">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div id="error-toast" class="bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center justify-between transform transition-all duration-300 ease-in-out translate-x-0 opacity-100">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
                <button onclick="closeToast('error-toast')" class="ml-4 text-white hover:text-red-200 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        @if(session('warning'))
            <div id="warning-toast" class="bg-yellow-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center justify-between transform transition-all duration-300 ease-in-out translate-x-0 opacity-100">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-3 text-xl"></i>
                    <span class="font-medium">{{ session('warning') }}</span>
                </div>
                <button onclick="closeToast('warning-toast')" class="ml-4 text-white hover:text-yellow-200 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        @if(session('info'))
            <div id="info-toast" class="bg-blue-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center justify-between transform transition-all duration-300 ease-in-out translate-x-0 opacity-100">
                <div class="flex items-center">
                    <i class="fas fa-info-circle mr-3 text-xl"></i>
                    <span class="font-medium">{{ session('info') }}</span>
                </div>
                <button onclick="closeToast('info-toast')" class="ml-4 text-white hover:text-blue-200 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif
    </div>

    <script>
        // Auto-hide toasts after 6 seconds
        setTimeout(function() {
            const toasts = document.querySelectorAll('[id$="-toast"]');
            toasts.forEach(toast => {
                if (toast) {
                    closeToast(toast.id);
                }
            });
        }, 6000);

        function closeToast(toastId) {
            const toast = document.getElementById(toastId);
            if (toast) {
                // Slide out animation
                toast.style.transform = 'translateX(100%) scale(0.95)';
                toast.style.opacity = '0';
                
                setTimeout(() => {
                    toast.remove();
                }, 500);
            }
        }

        // Add click outside to close functionality
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#toast-container')) {
                const toasts = document.querySelectorAll('[id$="-toast"]');
                toasts.forEach(toast => {
                    if (toast) {
                        closeToast(toast.id);
                    }
                });
            }
        });

        // Add slide-in animation when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('[id$="-toast"]');
            toasts.forEach(toast => {
                if (toast) {
                    // Start with slide-in animation
                    toast.style.transform = 'translateX(100%) scale(0.95)';
                    toast.style.opacity = '0';
                    
                    // Trigger slide-in after a small delay
                    setTimeout(() => {
                        toast.style.transform = 'translateX(0) scale(1)';
                        toast.style.opacity = '1';
                    }, 100);
                }
            });
        });
    </script>
@endif
