{{-- Flash messages - handled by JavaScript NotificationManager --}}
{{-- These are now processed client-side to prevent duplicates --}}

@if(session('success'))
    <meta name="session-success" content="{{ session('success') }}">
@endif

@if(session('error'))
    <meta name="session-error" content="{{ session('error') }}">
@endif

@if(session('warning'))
    <meta name="session-warning" content="{{ session('warning') }}">
@endif

@if(session('info'))
    <meta name="session-info" content="{{ session('info') }}">
@endif

{{-- Legacy flash display (hidden by default, processed by JS) --}}
@if(session('success') || session('error') || session('warning') || session('info'))
    <div class="flash-messages" style="display: none;">
        @if(session('success'))
            <div class="flash-success" data-message="{{ session('success') }}" data-type="success"></div>
        @endif

        @if(session('error'))
            <div class="flash-error" data-message="{{ session('error') }}" data-type="error"></div>
        @endif

        @if(session('warning'))
            <div class="flash-warning" data-message="{{ session('warning') }}" data-type="warning"></div>
        @endif

        @if(session('info'))
            <div class="flash-info" data-message="{{ session('info') }}" data-type="info"></div>
        @endif
    </div>
@endif


