@if(session('success') || session('error'))
    <div class="mb-4">
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg p-4 bg-green-50 border border-green-200">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-600 text-lg mr-3"></i>
                        <span class="text-sm text-green-800">{{ session('success') }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg p-4 bg-red-50 border border-red-200">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-600 text-lg mr-3"></i>
                        <span class="text-sm text-red-800">{{ session('error') }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif


