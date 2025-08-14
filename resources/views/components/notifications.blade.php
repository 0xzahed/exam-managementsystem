<!-- Simplified: only show validation errors inline; page flashes handled by components.flash -->
@if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-start">
            <i class="fas fa-exclamation-circle text-red-600 text-lg mr-3"></i>
            <div>
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
    </div>
@endif
