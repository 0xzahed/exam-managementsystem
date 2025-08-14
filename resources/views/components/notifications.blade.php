<!-- Enhanced Notifications Component -->
@if($errors->any() || session('success') || session('error') || session('warning') || session('info'))
    <div class="mb-6 space-y-4">
        <!-- Error Messages -->
        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="bg-red-100 p-2 rounded-full">
                            <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-red-800">
                            {{ $errors->count() > 1 ? 'There are some problems' : 'There is a problem' }}
                        </h3>
                        <div class="mt-2">
                            @if($errors->count() > 1)
                                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex-shrink-0 ml-3">
                        <button type="button" class="inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" onclick="this.parentElement.parentElement.parentElement.style.display='none'">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="bg-green-100 p-2 rounded-full">
                            <i class="fas fa-check-circle text-green-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-green-800">Success!</h3>
                        <p class="mt-1 text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                    <div class="flex-shrink-0 ml-3">
                        <button type="button" class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2" onclick="this.parentElement.parentElement.parentElement.style.display='none'">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Error Message -->
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="bg-red-100 p-2 rounded-full">
                            <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-red-800">Error!</h3>
                        <p class="mt-1 text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                    <div class="flex-shrink-0 ml-3">
                        <button type="button" class="inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" onclick="this.parentElement.parentElement.parentElement.style.display='none'">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Warning Message -->
        @if(session('warning'))
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="bg-yellow-100 p-2 rounded-full">
                            <i class="fas fa-exclamation-triangle text-yellow-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-yellow-800">Warning!</h3>
                        <p class="mt-1 text-sm text-yellow-700">{{ session('warning') }}</p>
                    </div>
                    <div class="flex-shrink-0 ml-3">
                        <button type="button" class="inline-flex rounded-md p-1.5 text-yellow-500 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2" onclick="this.parentElement.parentElement.parentElement.style.display='none'">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Info Message -->
        @if(session('info'))
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="bg-blue-100 p-2 rounded-full">
                            <i class="fas fa-info-circle text-blue-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-blue-800">Information</h3>
                        <p class="mt-1 text-sm text-blue-700">{{ session('info') }}</p>
                    </div>
                    <div class="flex-shrink-0 ml-3">
                        <button type="button" class="inline-flex rounded-md p-1.5 text-blue-500 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" onclick="this.parentElement.parentElement.parentElement.style.display='none'">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif
