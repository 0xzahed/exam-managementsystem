@extends('layouts.dashboard')

@section('title', 'Instructor Profile Settings')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8 space-y-10">
    @php $photoUrl = $user->profile_photo_display_url; @endphp

    <style>
        /* Inline minimal classes since @apply isn't processed in Blade runtime */
        .form-label { display:block; font-size:0.875rem; line-height:1.25rem; font-weight:500; color:#374151; margin-bottom:0.25rem; }
        .form-input { width:100%; border:1px solid #d1d5db; border-radius:0.375rem; padding:0.5rem 0.75rem; font-size:0.875rem; line-height:1.25rem; background:#fff; transition: box-shadow .15s, border-color .15s; }
        .form-input:focus { outline:none; border-color:#7e22ce; box-shadow:0 0 0 2px rgba(168,85,247,0.25); }
        .form-input:disabled { background:#f3f4f6; color:#6b7280; cursor:not-allowed; }
    </style>

    @if(session('status'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-2 rounded flex items-center gap-2">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2 rounded space-y-1">
            @foreach($errors->all() as $err)
                <div class="flex items-center gap-2"><i class="fas fa-exclamation-triangle"></i><span>{{ $err }}</span></div>
            @endforeach
        </div>
    @endif

    <!-- Header Card -->
    <div class="bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 rounded-xl p-6 sm:p-8 text-white flex flex-col sm:flex-row items-center sm:items-start gap-6 shadow">
        <div class="relative">
            @if($photoUrl)
                <img src="{{ $photoUrl }}" class="h-28 w-28 rounded-full object-cover profile-photo-preview ring-4 ring-white/30" alt="Profile"/>
            @else
                <div class="h-28 w-28 rounded-full bg-white/20 backdrop-blur flex items-center justify-center text-4xl font-semibold profile-photo-preview">{{ strtoupper(substr($user->first_name,0,1)) }}</div>
            @endif
            <label class="absolute -bottom-2 -right-2 bg-white text-purple-600 rounded-full h-9 w-9 flex items-center justify-center shadow cursor-pointer hover:scale-105 transition" title="Change Photo">
                <i class="fas fa-camera text-sm"></i>
                <input type="file" id="profile-photo-input" name="profile_photo" form="instructor-profile-form" accept="image/*" class="hidden"/>
            </label>
        </div>
        <div class="flex-1 w-full">
            <h1 class="text-3xl font-bold leading-tight">{{ $user->first_name }} {{ $user->last_name }}</h1>
            <p class="text-sm text-purple-100 mt-1 flex items-center gap-2"><i class="fas fa-envelope text-white/70"></i>{{ $user->email }}</p>
            <div class="flex flex-wrap gap-2 mt-4 text-xs">
                <span class="px-2 py-1 bg-white/15 rounded-md">Employee: {{ $user->employee_id ?? 'N/A' }}</span>
                @if($user->department)
                    <span class="px-2 py-1 bg-white/15 rounded-md">{{ $user->department }}</span>
                @endif
                @if($user->specialization)
                    <span class="px-2 py-1 bg-white/15 rounded-md">{{ $user->specialization }}</span>
                @endif
            </div>
        </div>
    </div>

    <form id="instructor-profile-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-10">
        @csrf
        @method('PUT')
        <!-- Professional & Contact Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
            <div class="flex items-center gap-2">
                <div class="h-8 w-8 rounded bg-purple-100 text-purple-600 flex items-center justify-center"><i class="fas fa-id-badge text-sm"></i></div>
                <h2 class="text-lg font-semibold text-gray-800">Professional Details</h2>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="form-label" for="title">Title</label>
                    <select name="title" id="title" class="form-input">
                        <option value="">--</option>
                        @foreach(['Dr.','Prof.','Assoc. Prof.','Asst. Prof.','Mr.','Ms.','Mrs.'] as $t)
                            <option value="{{ $t }}" @selected(old('title',$user->title)===$t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="phone">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone',$user->phone) }}" class="form-input"/>
                </div>
                <div>
                    <label class="form-label" for="department">Department</label>
                    <input type="text" name="department" id="department" value="{{ old('department',$user->department) }}" class="form-input"/>
                </div>
                <div>
                    <label class="form-label" for="specialization">Specialization</label>
                    <input type="text" name="specialization" id="specialization" value="{{ old('specialization',$user->specialization) }}" class="form-input"/>
                </div>
                <div>
                    <label class="form-label" for="office_location">Office Location</label>
                    <input type="text" name="office_location" id="office_location" value="{{ old('office_location',$user->office_location) }}" class="form-input"/>
                </div>
                <div>
                    <label class="form-label" for="office_hours">Office Hours</label>
                    <input type="text" name="office_hours" id="office_hours" value="{{ old('office_hours',$user->office_hours) }}" class="form-input"/>
                </div>
                <div>
                    <label class="form-label" for="website">Website</label>
                    <input type="url" name="website" id="website" value="{{ old('website',$user->website) }}" class="form-input"/>
                </div>
                <div>
                    <label class="form-label" for="linkedin">LinkedIn</label>
                    <input type="url" name="linkedin" id="linkedin" value="{{ old('linkedin',$user->linkedin) }}" class="form-input"/>
                </div>
                <div>
                    <label class="form-label" for="date_of_birth">Date of Birth</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth',$user->date_of_birth) }}" class="form-input"/>
                </div>
                <div>
                    <label class="form-label" for="gender">Gender</label>
                    <select name="gender" id="gender" class="form-input">
                        <option value="">--</option>
                        @foreach(['male'=>'Male','female'=>'Female','other'=>'Other'] as $val=>$label)
                            <option value="{{ $val }}" @selected(old('gender',$user->gender)===$val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Academic Profile -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-2 mb-6">
                <div class="h-8 w-8 rounded bg-purple-100 text-purple-600 flex items-center justify-center"><i class="fas fa-graduation-cap text-sm"></i></div>
                <h2 class="text-lg font-semibold text-gray-800">Academic Profile</h2>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-5">
                    <div>
                        <label class="form-label" for="education">Education</label>
                        <textarea name="education" id="education" rows="4" class="form-input resize-none" placeholder="PhD ...">{{ old('education',$user->education) }}</textarea>
                    </div>
                    <div>
                        <label class="form-label" for="research_interests">Research Interests</label>
                        <textarea name="research_interests" id="research_interests" rows="4" class="form-input resize-none" placeholder="AI, NLP ...">{{ old('research_interests',$user->research_interests) }}</textarea>
                    </div>
                </div>
                <div>
                    <label class="form-label" for="bio">Bio <span id="bio-count" class="ml-1 text-xs text-gray-400"></span></label>
                    <textarea name="bio" id="bio" rows="9" maxlength="500" class="form-input resize-none" placeholder="Short bio...">{{ old('bio',$user->bio) }}</textarea>
                    <p class="mt-2 text-xs text-gray-400">Maximum 500 characters.</n></p>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 rounded bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium shadow-sm">Save Changes</button>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div class="flex items-center gap-2">
            <div class="h-8 w-8 rounded bg-purple-100 text-purple-600 flex items-center justify-center"><i class="fas fa-lock text-sm"></i></div>
            <h2 class="text-lg font-semibold text-gray-800">Password</h2>
        </div>
        <form action="{{ route('profile.password') }}" method="POST" class="grid md:grid-cols-3 gap-4 items-end">
            @csrf
            @method('PUT')
            <div>
                <label class="form-label" for="current_password">Current</label>
                <input required type="password" name="current_password" id="current_password" class="form-input"/>
            </div>
            <div>
                <label class="form-label" for="password">New</label>
                <input required type="password" name="password" id="password" class="form-input"/>
            </div>
            <div>
                <label class="form-label" for="password_confirmation">Confirm</label>
                <input required type="password" name="password_confirmation" id="password_confirmation" class="form-input"/>
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button class="px-6 py-2 rounded bg-gray-800 hover:bg-black text-white text-sm font-medium shadow-sm">Update Password</button>
            </div>
        </form>
    </div>

    @section('scripts')
        @vite('resources/js/pages/profile-instructor-settings.js')
    @endsection
</div>
@endsection