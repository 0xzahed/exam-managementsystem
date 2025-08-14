@extends('layouts.dashboard')

@section('title', 'Help Center - InsightEdu')

@section('content')
<div class="px-4 py-8 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Help Center</h1>
        <p class="text-gray-600 mt-2">Find answers to your questions and get support</p>
    </div>

    <!-- Search Bar -->
    <div class="bg-white rounded-lg shadow card-hover p-6 mb-8">
        <div class="max-w-2xl mx-auto">
            <h3 class="text-xl font-semibold text-gray-900 mb-4 text-center">How can we help you?</h3>
            <div class="flex">
                <input type="text" placeholder="Search for help articles..."
                    class="flex-1 px-4 py-3 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <button class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-r-lg transition-all duration-300">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Help Categories -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow card-hover p-6">
            <div class="text-center">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-graduation-cap text-2xl text-blue-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Getting Started</h3>
                <p class="text-gray-600 text-sm mb-4">Learn the basics of using InsightEdu</p>
                <button class="text-blue-600 hover:text-blue-800 font-medium">Learn More →</button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover p-6">
            <div class="text-center">
                <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-book text-2xl text-green-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Course Management</h3>
                <p class="text-gray-600 text-sm mb-4">Managing courses and materials</p>
                <button class="text-green-600 hover:text-green-800 font-medium">Learn More →</button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover p-6">
            <div class="text-center">
                <div class="bg-yellow-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-tasks text-2xl text-yellow-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Assignments & Exams</h3>
                <p class="text-gray-600 text-sm mb-4">Creating and managing assessments</p>
                <button class="text-yellow-600 hover:text-yellow-800 font-medium">Learn More →</button>
            </div>
        </div>
    </div>

    <!-- Frequently Asked Questions -->
    <div class="bg-white rounded-lg shadow card-hover p-6 mb-8">
        <h3 class="text-xl font-semibold text-gray-900 mb-6">Frequently Asked Questions</h3>
        <div class="space-y-4">
            <div class="border-b border-gray-200 pb-4">
                <button class="flex items-center justify-between w-full text-left">
                    <span class="font-medium text-gray-900">How do I enroll in a course?</span>
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </button>
                <div class="mt-2 text-gray-600 text-sm hidden">
                    To enroll in a course, go to the Courses page and click the "Enroll" button next to the course you want to join.
                </div>
            </div>
            <div class="border-b border-gray-200 pb-4">
                <button class="flex items-center justify-between w-full text-left">
                    <span class="font-medium text-gray-900">How do I submit an assignment?</span>
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </button>
            </div>
            <div class="border-b border-gray-200 pb-4">
                <button class="flex items-center justify-between w-full text-left">
                    <span class="font-medium text-gray-900">How do I check my grades?</span>
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Contact Support -->
    <div class="bg-white rounded-lg shadow card-hover p-6">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Need more help?</h3>
        <p class="text-gray-600 mb-6">Can't find what you're looking for? Contact our support team.</p>
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="mailto:support@insightedu.com" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium text-center transition-all duration-300">
                <i class="fas fa-envelope mr-2"></i>Email Support
            </a>
            <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium transition-all duration-300">
                <i class="fas fa-comments mr-2"></i>Live Chat
            </button>
        </div>
    </div>
</div>
@endsection