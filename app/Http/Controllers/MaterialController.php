<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\User;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MaterialController extends Controller
{
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
    }

    public function index(Course $course)
    {
        // Check if the user is the instructor of this course
        if ($course->instructor_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this course.');
        }

        $materials = CourseMaterial::where('course_id', $course->id)->orderBy('created_at', 'desc')->get();
        
        // Get stored sections from course
        $storedSections = json_decode($course->sections ?? '[]', true);

        return view('courses.materials', compact('course', 'materials', 'storedSections'));
    }

    public function store(Request $request, Course $course)
    {
        // Debug log
        Log::info('Material store request:', [
            'course_id' => $course->id,
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
            'has_file' => $request->hasFile('file'),
            'material_type' => $request->material_type
        ]);

        // Check if user is instructor of this course
        if (Auth::user()->role !== 'instructor' || $course->instructor_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate basic fields
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'section_name' => 'nullable|string|max:255',
            'material_type' => 'required|string|in:text,file',
            'is_private' => 'nullable|boolean'
        ]);

        // Additional validation based on type
        if ($request->material_type === 'file') {
            if (!$request->hasFile('file')) {
                return back()->withErrors(['file' => 'Please select a file to upload.'])->withInput();
            }
            
            $request->validate([
                'file' => 'required|file|mimes:pdf,ppt,pptx,doc,docx,jpg,jpeg,png,gif|max:10240'
            ]);
        } else if ($request->material_type === 'text') {
            if (empty($request->content)) {
                return back()->withErrors(['content' => 'Content is required for text materials.'])->withInput();
            }
        }

        try {
            DB::beginTransaction();

            $materialData = [
                'course_id' => $course->id,
                'title' => $request->title,
                'description' => $request->description,
                'content' => $request->content,
                'section' => $request->section_name,
                'type' => $request->material_type,
                'uploaded_by' => Auth::id(),
                'is_private' => $request->has('is_private') ? (bool)$request->is_private : false
            ];

            // Handle file upload if provided
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                // Create or get course-specific folder in Google Drive
                $courseFolderId = $course->google_drive_folder_id;
                
                // If course doesn't have a folder yet, create one
                if (!$courseFolderId) {
                    $courseFolderId = $this->googleDriveService->createCourseFolder(
                        $course->title,
                        $course->code
                    );
                    
                    // Save folder ID to course
                    if ($courseFolderId) {
                        $course->update(['google_drive_folder_id' => $courseFolderId]);
                    }
                }
                
                // Use original filename for Google Drive
                $fileName = $file->getClientOriginalName();
                
                $googleUploadSuccess = false;
                
                try {
                    if ($courseFolderId) {
                        // Try Google Drive upload to course-specific folder
                        $uploadResult = $this->googleDriveService->uploadFile(
                            $file,
                            $fileName,
                            $courseFolderId
                        );
                        
                        if ($uploadResult && isset($uploadResult['id'])) {
                            $materialData['file_path'] = $uploadResult['id']; // Store Google Drive file ID
                            $materialData['file_name'] = $fileName;
                            $materialData['file_size'] = $file->getSize();
                            $materialData['file_type'] = $file->getClientOriginalExtension();
                            $materialData['google_drive_url'] = 'https://drive.google.com/file/d/' . $uploadResult['id'] . '/view';
                            $googleUploadSuccess = true;
                            
                            Log::info('File uploaded to Google Drive course folder successfully', [
                                'file_id' => $uploadResult['id'],
                                'file_name' => $fileName,
                                'course_folder_id' => $courseFolderId,
                                'course' => $course->code . ' - ' . $course->title
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Google Drive upload failed, falling back to local storage: ' . $e->getMessage());
                }
                
                // Fallback to local storage if Google Drive failed
                if (!$googleUploadSuccess) {
                    $filePath = $file->storeAs('course_materials/' . $course->id, $fileName, 'public');
                    
                    $materialData['file_path'] = $filePath;
                    $materialData['file_name'] = $fileName;
                    $materialData['file_size'] = $file->getSize();
                    $materialData['file_type'] = $file->getClientOriginalExtension();
                    
                    Log::info('File uploaded to local storage as fallback', [
                        'file_path' => $filePath,
                        'file_name' => $fileName
                    ]);
                }
            }

            $material = CourseMaterial::create($materialData);

            Log::info('Material created successfully:', ['material_id' => $material->id]);

            DB::commit();

            // Check if it's an AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Material added successfully!',
                    'material' => $material
                ]);
            }

            // Traditional form submission - redirect back with success message
            return redirect()->route('courses.materials', $course)
                ->with('success', 'Material added successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating material: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Failed to create material. Please try again.'
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Failed to create material. Please try again.'])->withInput();
        }
    }

    public function destroy(Course $course, CourseMaterial $material)
    {
        // Check if user is instructor of this course
        if (Auth::user()->role !== 'instructor' || $course->instructor_id !== Auth::id()) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'Unauthorized');
        }

        try {
            Log::info('Deleting material', [
                'material_id' => $material->id,
                'file_path' => $material->file_path,
                'google_drive_url' => $material->google_drive_url
            ]);

            // Delete from Google Drive if it's a Google Drive file
            if ($material->google_drive_url && $material->file_path) {
                // If file_path contains Google Drive ID, try to delete from Google Drive
                $deleteResult = $this->googleDriveService->deleteFile($material->file_path);
                if (!$deleteResult) {
                    Log::warning('Failed to delete file from Google Drive', [
                        'file_id' => $material->file_path
                    ]);
                }
            }
            
            // Delete local file if it exists
            if ($material->file_path && !$material->google_drive_url && Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }
            
            // Delete from database
            $material->delete();

            Log::info('Material deleted successfully', ['material_id' => $material->id]);

            // Check if it's an AJAX request
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Material deleted successfully!'
                ]);
            }

            // For traditional form submission, redirect with success message
            return redirect()->route('courses.materials', $course)
                ->with('success', 'Material deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Error deleting material:', ['error' => $e->getMessage()]);
            
            if (request()->ajax()) {
                return response()->json(['error' => 'Failed to delete material: ' . $e->getMessage()], 500);
            }

            return redirect()->route('courses.materials', $course)
                ->with('error', 'Failed to delete material. Please try again.');
        }
    }

    public function download(Course $course, CourseMaterial $material)
    {
        // Debug log
        Log::info('Download attempt:', [
            'user_id' => Auth::id(),
            'user_role' => Auth::user()->role,
            'course_id' => $course->id,
            'material_id' => $material->id,
            'file_path' => $material->file_path,
            'file_name' => $material->file_name
        ]);

        // Check if user has access to this course
        $user = Auth::user();
        if ($user->role === 'instructor' && $course->instructor_id !== $user->id) {
            Log::warning('Instructor access denied for different course');
            abort(403, 'Unauthorized');
        }
        
        // For now, allow all students to download (remove enrollment check temporarily)
        // if ($user->role === 'student' && !$course->students()->where('user_id', $user->id)->exists()) {
        //     Log::warning('Student not enrolled in course', [
        //         'student_id' => $user->id,
        //         'course_id' => $course->id
        //     ]);
        //     abort(403, 'You are not enrolled in this course');
        // }

        // Check if file exists (Google Drive or local storage)
        if ($material->google_drive_url) {
            // Google Drive file - redirect to Google Drive view URL
            Log::info('Redirecting to Google Drive file');
            return redirect($material->google_drive_url);
        } else if ($material->file_path) {
            // Local storage file
            if (!Storage::disk('public')->exists($material->file_path)) {
                Log::error('Local file not found in storage', [
                    'file_path' => $material->file_path,
                    'storage_exists' => Storage::disk('public')->exists($material->file_path ?? '')
                ]);
                abort(404, 'File not found');
            }

            $filePath = storage_path('app/public/' . $material->file_path);
            
            if (!file_exists($filePath)) {
                Log::error('Physical file not found', ['file_path' => $filePath]);
                return response()->json(['error' => 'File not found'], 404);
            }
            
            Log::info('Local file download successful');
            return response()->download($filePath, $material->file_name);
        } else {
            Log::error('No file path found for material');
            abort(404, 'File not found');
        }
    }

    public function createSection(Request $request, Course $course)
    {
        // Check if user is instructor of this course
        if (Auth::user()->role !== 'instructor' || $course->instructor_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'section_name' => 'required|string|max:255'
        ]);

        $sectionName = trim($request->section_name);

        // Check if section already exists (either as a stored section or from materials)
        $existingSections = $course->courseMaterials->pluck('section')->unique()->filter()->toArray();
        $existingStoredSections = json_decode($course->sections ?? '[]', true);
        
        if (in_array($sectionName, $existingSections) || in_array($sectionName, $existingStoredSections)) {
            return response()->json(['error' => 'Section already exists'], 400);
        }

        // Add to stored sections
        $storedSections = $existingStoredSections;
        $storedSections[] = $sectionName;
        
        $course->update(['sections' => json_encode($storedSections)]);

        return response()->json([
            'success' => true,
            'message' => 'Section created successfully!',
            'section_name' => $sectionName
        ]);
    }

    public function storeSection(Request $request, Course $course)
    {
        // Check if user is instructor of this course
        if (Auth::user()->role !== 'instructor' || $course->instructor_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'section_name' => 'required|string|max:255'
        ]);

        $sectionName = trim($request->section_name);

        // Check if section already exists (either as a stored section or from materials)
        $existingSections = $course->courseMaterials->pluck('section')->unique()->filter()->toArray();
        $existingStoredSections = json_decode($course->sections ?? '[]', true);
        
        if (in_array($sectionName, $existingSections) || in_array($sectionName, $existingStoredSections)) {
            return back()->withErrors(['section_name' => 'Section already exists'])->withInput();
        }

        // Add to stored sections
        $storedSections = $existingStoredSections;
        $storedSections[] = $sectionName;
        
        $course->update(['sections' => json_encode($storedSections)]);

        return redirect()->route('courses.materials', $course)
                       ->with('success', 'Section created successfully!');
    }

    public function edit(Course $course, CourseMaterial $material)
    {
        // Check if user is instructor of this course
        if (Auth::user()->role !== 'instructor' || $course->instructor_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return response()->json([
            'material' => $material,
            'course' => $course
        ]);
    }

    public function update(Request $request, Course $course, CourseMaterial $material)
    {
        // Check if user is instructor of this course
        if (Auth::user()->role !== 'instructor' || $course->instructor_id !== Auth::id()) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'section_name' => 'nullable|string|max:255',
            'is_private' => 'required|boolean',
            'content' => 'nullable|string',
            'file' => 'nullable|file|max:10240' // 10MB max
        ]);

        try {
            DB::beginTransaction();

            // Update basic fields
            $material->update([
                'title' => $request->title,
                'description' => $request->description,
                'section' => $request->section_name,
                'is_private' => $request->is_private,
                'content' => $request->content
            ]);

            // Handle file upload if provided
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                // Delete old file if exists
                if ($material->file_path) {
                    if ($material->google_drive_url) {
                        $this->googleDriveService->deleteFile($material->file_path);
                    } else {
                        Storage::disk('public')->delete($material->file_path);
                    }
                }

                // Upload new file to Google Drive
                $uploadResult = $this->googleDriveService->uploadFile($file, $file->getClientOriginalName(), $course->google_drive_folder_id);
                
                if ($uploadResult['success']) {
                    $material->update([
                        'file_path' => $uploadResult['file_id'],
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getClientOriginalExtension(),
                        'google_drive_url' => $uploadResult['web_view_link']
                    ]);
                } else {
                    throw new \Exception('Failed to upload file to Google Drive');
                }
            }

            DB::commit();

            Log::info('Material updated successfully', [
                'material_id' => $material->id,
                'course_id' => $course->id,
                'user_id' => Auth::id()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Material updated successfully!'
                ]);
            }

            return redirect()->route('courses.materials', $course)
                ->with('success', 'Material updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating material:', ['error' => $e->getMessage()]);
            
            if (request()->ajax()) {
                return response()->json(['error' => 'Failed to update material: ' . $e->getMessage()], 500);
            }

            return redirect()->route('courses.materials', $course)
                ->with('error', 'Failed to update material. Please try again.');
        }
    }
}
