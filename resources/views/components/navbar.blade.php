<nav class="bg-white shadow-lg border-b border-gray-200 fixed w-full top-0 z-40">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo & Menu Toggle -->
            <div class="flex items-center">
                <button id="sidebarToggle" class="p-2 rounded-md text-gray-600 hover:text-indigo-600 hover:bg-gray-100 lg:hidden">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="flex items-center ml-2 lg:ml-0">
                    <i class="fas fa-graduation-cap text-2xl text-indigo-600 mr-3"></i>
                    <h1 class="text-xl font-bold text-gray-800">InsightEdu</h1>
                    <span class="ml-2 text-sm bg-purple-100 text-purple-700 px-2 py-1 rounded-full">{{ ucfirst(Auth::user()->role) }}</span>
                </div>
            </div>

            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <div class="relative">
                    <button data-dropdown-toggle="notification-dropdown" class="p-2 text-gray-600 hover:text-indigo-600 relative">
                        <i class="fas fa-bell text-xl"></i>
                        @if(isset($notifications) && $notifications->count() > 0)
                        <span class="notification-badge absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full text-xs text-white flex items-center justify-center">{{ $notifications->count() }}</span>
                        @endif
                    </button>

                    <!-- Notification Dropdown -->
                    <div id="notification-dropdown" class="dropdown-menu hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                        </div>
                        <div class="max-h-64 overflow-y-auto">
                            @if(isset($notifications) && $notifications->count() > 0)
                            @foreach($notifications as $notification)
                            <div class="p-3 border-b border-gray-100 hover:bg-gray-50">
                                <p class="text-sm text-gray-900">{{ $notification->message }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                            @endforeach
                            @else
                            <div class="p-4 text-center text-gray-500">
                                <i class="fas fa-bell-slash text-2xl mb-2"></i>
                                <p>No new notifications</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <div class="relative">
                    <button data-dropdown-toggle="profile-dropdown" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100">
                        <div class="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-white text-sm font-medium">{{ strtoupper(substr(Auth::user()->first_name,0,1)) }}{{ strtoupper(substr(Auth::user()->last_name,0,1)) }}</span>
                        </div>
                        <div class="text-left hidden sm:block">
                            <p class="text-sm font-medium text-gray-700">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</p>
                            <p class="text-xs text-gray-500">{{ ucfirst(Auth::user()->role) }}</p>
                        </div>
                        <i class="fas fa-chevron-down text-gray-400"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="profile-dropdown" class="dropdown-menu hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Quick Actions</p>
                        </div>
                        <a href="{{ route('profile.settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user mr-2"></i>Profile Settings
                        </a>
                        <a href="{{ route('help.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-question-circle mr-2"></i>Help & Support
                        </a>
                        <hr class="my-1">
                        <a href="{{ route('logout') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
<script>
// Fallback dropdown init (idempotent)
if(!window.__navDropdownInit){
    window.__navDropdownInit = true;
    document.addEventListener('DOMContentLoaded', function(){
        const triggers = document.querySelectorAll('[data-dropdown-toggle]');
        function closeAll(except){
            document.querySelectorAll('.dropdown-menu').forEach(d=>{ if(!except || d.id!==except) d.classList.add('hidden'); });
        }
        triggers.forEach(btn=>{
            btn.addEventListener('click', function(e){
                e.stopPropagation();
                const id = this.getAttribute('data-dropdown-toggle');
                if(!id) return;
                const target = document.getElementById(id);
                if(!target) return;
                const willShow = target.classList.contains('hidden');
                closeAll(id);
                if(willShow) target.classList.remove('hidden'); else target.classList.add('hidden');
            });
        });
        document.addEventListener('click', ()=> closeAll());
        document.addEventListener('keydown', e=> { if(e.key==='Escape') closeAll(); });
    });
}
</script>
