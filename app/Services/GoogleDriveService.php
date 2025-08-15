<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class GoogleDriveService
{
    private $client;
    private $service;

    public function __construct()
    {
        $this->initializeClient();
    }

    private function initializeClient()
    {
        try {
            $this->client = new Client();
            $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
            $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
            $this->client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
            $this->client->addScope([
                'https://www.googleapis.com/auth/drive.file',
                'https://www.googleapis.com/auth/drive'
            ]);

            // Set refresh token
            $refreshToken = env('GOOGLE_REFRESH_TOKEN');
            if ($refreshToken) {
                $accessToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                
                if (isset($accessToken['error'])) {
                    Log::error('Failed to refresh Google Drive token: ' . json_encode($accessToken));
                    throw new \Exception('Token refresh failed');
                }
            }

            $this->service = new Drive($this->client);
            
        } catch (\Exception $e) {
            Log::error('Google Drive service initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createCourseFolder(string $courseTitle, string $courseCode)
    {
        try {
            // Create folder name: "Course Code - Course Title"
            $folderName = $courseCode . ' - ' . $courseTitle;
            
            // Parent folder ID (your main InsightEdu folder)
            $parentFolderId = '10syW83z7cJ7IOR4StJideirlQBb9NXXZ';
            
            // Create folder metadata
            $folderMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $folderName,
                'parents' => [$parentFolderId],
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);

            // Create the folder
            $folder = $this->service->files->create($folderMetadata);

            Log::info('Google Drive course folder created successfully', [
                'folder_id' => $folder->id,
                'folder_name' => $folderName
            ]);

            return $folder->id;

        } catch (\Exception $e) {
            Log::error('Google Drive folder creation failed: ' . $e->getMessage());
            return false;
        }
    }

    public function uploadFile(UploadedFile $file, string $fileName, string $folderId)
    {
        try {
            // Create file metadata
            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $fileName,
                'parents' => [$folderId]
            ]);

            // Upload file
            $content = file_get_contents($file->getRealPath());
            $driveFile = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $file->getMimeType(),
                'uploadType' => 'multipart'
            ]);

            // Make file publicly accessible
            $permission = new \Google\Service\Drive\Permission([
                'role' => 'reader',
                'type' => 'anyone'
            ]);
            
            $this->service->permissions->create($driveFile->id, $permission);

            return [
                'id' => $driveFile->id,
                'url' => "https://drive.google.com/file/d/{$driveFile->id}/view",
                'webViewLink' => "https://drive.google.com/file/d/{$driveFile->id}/view"
            ];

        } catch (\Exception $e) {
            Log::error('Google Drive upload failed: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteFile(string $fileId)
    {
        try {
            $this->service->files->delete($fileId);
            return true;
        } catch (\Exception $e) {
            Log::error('Google Drive delete failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Find a folder by name within a parent folder
     */
    public function findFolderByName(string $folderName, string $parentFolderId)
    {
        try {
            $query = "name='{$folderName}' and '{$parentFolderId}' in parents and mimeType='application/vnd.google-apps.folder' and trashed=false";
            
            $results = $this->service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name)'
            ]);

            $folders = $results->getFiles();
            if (!empty($folders)) {
                return [
                    'id' => $folders[0]->getId(),
                    'name' => $folders[0]->getName()
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to find Google Drive folder: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create or get assignment folder inside submissions folder
     */
    public function createAssignmentFolder(string $assignmentTitle)
    {
        try {
            // Assignment submissions folder ID
            $assignmentsFolderId = '1Ru7kmdp87ljBEHvXKgjFZje_soOHm4za';
            
            // Clean assignment title for folder name
            $folderName = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $assignmentTitle);
            $folderName = trim($folderName);
            
            // Check if assignment folder already exists
            $existingFolder = $this->findFolderByName($folderName, $assignmentsFolderId);
            
            if ($existingFolder) {
                return $existingFolder['id'];
            }
            
            // Create new assignment folder
            $folderMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $folderName,
                'parents' => [$assignmentsFolderId],
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);

            $folder = $this->service->files->create($folderMetadata);

            Log::info('Assignment folder created successfully', [
                'folder_id' => $folder->id,
                'folder_name' => $folderName,
                'assignment_title' => $assignmentTitle
            ]);

            return $folder->id;

        } catch (\Exception $e) {
            Log::error('Failed to create assignment folder: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Upload assignment submission file with original name
     */
    public function uploadAssignmentSubmission(UploadedFile $file, string $studentName, string $assignmentTitle, string $existingFileId = null)
    {
        try {
            // Get or create assignment-specific folder
            $assignmentFolderId = $this->createAssignmentFolder($assignmentTitle);
            if (!$assignmentFolderId) {
                throw new \Exception('Could not create assignment folder');
            }

            // Use original file name with student name prefix
            $originalName = $file->getClientOriginalName();
            $fileName = $studentName . ' - ' . $originalName;

            // If there's an existing file, delete it first (for resubmission)
            if ($existingFileId) {
                $this->deleteFile($existingFileId);
                Log::info('Deleted existing assignment submission', ['file_id' => $existingFileId]);
            }

            // Create file metadata
            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $fileName,
                'parents' => [$assignmentFolderId]
            ]);

            // Upload file
            $content = file_get_contents($file->getRealPath());
            $driveFile = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $file->getMimeType(),
                'uploadType' => 'multipart'
            ]);

            // Make file accessible to instructor
            $permission = new \Google\Service\Drive\Permission([
                'role' => 'reader',
                'type' => 'anyone'
            ]);
            
            $this->service->permissions->create($driveFile->id, $permission);

            Log::info('Assignment submission uploaded to Google Drive', [
                'file_id' => $driveFile->id,
                'file_name' => $fileName,
                'original_name' => $originalName,
                'student' => $studentName,
                'assignment' => $assignmentTitle,
                'folder_id' => $assignmentFolderId
            ]);

            return [
                'id' => $driveFile->id,
                'url' => "https://drive.google.com/file/d/{$driveFile->id}/view",
                'file_name' => $fileName
            ];

        } catch (\Exception $e) {
            Log::error('Assignment submission upload failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create exam material folder in Google Drive
     */
    public function createExamMaterialFolder(string $courseTitle, string $examTitle)
    {
        try {
            // Main exams folder ID
            $examsFolderId = '12doPoLXsgun9AuGbxxwePNiK2muZfM8i';
            
            // Create course folder if it doesn't exist
            $courseFolderName = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $courseTitle);
            $courseFolderName = trim($courseFolderName);
            
            $existingCourseFolder = $this->findFolderByName($courseFolderName, $examsFolderId);
            
            if ($existingCourseFolder) {
                $courseFolderId = $existingCourseFolder['id'];
            } else {
                $courseFolderMetadata = new \Google\Service\Drive\DriveFile([
                    'name' => $courseFolderName,
                    'parents' => [$examsFolderId],
                    'mimeType' => 'application/vnd.google-apps.folder'
                ]);
                $courseFolder = $this->service->files->create($courseFolderMetadata);
                $courseFolderId = $courseFolder->id;
            }
            
            // Create exam folder
            $examFolderName = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $examTitle);
            $examFolderName = trim($examFolderName);
            
            $existingExamFolder = $this->findFolderByName($examFolderName, $courseFolderId);
            
            if ($existingExamFolder) {
                $examFolderId = $existingExamFolder['id'];
            } else {
                $examFolderMetadata = new \Google\Service\Drive\DriveFile([
                    'name' => $examFolderName,
                    'parents' => [$courseFolderId],
                    'mimeType' => 'application/vnd.google-apps.folder'
                ]);
                $examFolder = $this->service->files->create($examFolderMetadata);
                $examFolderId = $examFolder->id;
            }

            Log::info('Exam material folder created successfully', [
                'course' => $courseTitle,
                'exam' => $examTitle,
                'folder_id' => $examFolderId
            ]);

            return $examFolderId;

        } catch (\Exception $e) {
            Log::error('Exam material folder creation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Upload exam material file to Google Drive
     */
    public function uploadExamMaterial(UploadedFile $file, string $courseTitle, string $examTitle)
    {
        try {
            $examFolderId = $this->createExamMaterialFolder($courseTitle, $examTitle);
            
            if (!$examFolderId) {
                throw new \Exception('Failed to create exam material folder');
            }

            $fileName = $file->getClientOriginalName();

            $driveFile = new \Google\Service\Drive\DriveFile();
            $driveFile->setName($fileName);
            $driveFile->setParents([$examFolderId]);

            $result = $this->service->files->create($driveFile, [
                'data' => file_get_contents($file->getRealPath()),
                'mimeType' => $file->getMimeType(),
                'uploadType' => 'multipart'
            ]);

            Log::info('Exam material uploaded successfully', [
                'course' => $courseTitle,
                'exam' => $examTitle,
                'file' => $fileName,
                'file_id' => $result->id
            ]);

            return [
                'id' => $result->id,
                'url' => "https://drive.google.com/file/d/{$result->id}/view",
                'file_name' => $fileName
            ];

        } catch (\Exception $e) {
            Log::error('Exam material upload failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create exam submission folder in Google Drive
     */
    public function createExamSubmissionFolder(string $courseTitle, string $examTitle)
    {
        try {
            // Main submissions folder ID
            $submissionsFolderId = '1lqiwv5_LFIoLCenzDk3wW5WkRZU7BxKs';
            
            // Create course folder if it doesn't exist
            $courseFolderName = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $courseTitle);
            $courseFolderName = trim($courseFolderName);
            
            $existingCourseFolder = $this->findFolderByName($courseFolderName, $submissionsFolderId);
            
            if ($existingCourseFolder) {
                $courseFolderId = $existingCourseFolder['id'];
            } else {
                $courseFolderMetadata = new \Google\Service\Drive\DriveFile([
                    'name' => $courseFolderName,
                    'parents' => [$submissionsFolderId],
                    'mimeType' => 'application/vnd.google-apps.folder'
                ]);
                $courseFolder = $this->service->files->create($courseFolderMetadata);
                $courseFolderId = $courseFolder->id;
            }
            
            // Create exam folder
            $examFolderName = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $examTitle);
            $examFolderName = trim($examFolderName);
            
            $existingExamFolder = $this->findFolderByName($examFolderName, $courseFolderId);
            
            if ($existingExamFolder) {
                $examFolderId = $existingExamFolder['id'];
            } else {
                $examFolderMetadata = new \Google\Service\Drive\DriveFile([
                    'name' => $examFolderName,
                    'parents' => [$courseFolderId],
                    'mimeType' => 'application/vnd.google-apps.folder'
                ]);
                $examFolder = $this->service->files->create($examFolderMetadata);
                $examFolderId = $examFolder->id;
            }

            Log::info('Exam submission folder created successfully', [
                'course' => $courseTitle,
                'exam' => $examTitle,
                'folder_id' => $examFolderId
            ]);

            return $examFolderId;

        } catch (\Exception $e) {
            Log::error('Exam submission folder creation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Upload exam submission file to Google Drive
     */
    public function uploadExamSubmission(UploadedFile $file, string $courseTitle, string $examTitle, string $studentName)
    {
        try {
            $examFolderId = $this->createExamSubmissionFolder($courseTitle, $examTitle);
            
            if (!$examFolderId) {
                throw new \Exception('Failed to create exam submission folder');
            }

            $originalFileName = $file->getClientOriginalName();
            $fileName = $studentName . '_' . $originalFileName;

            $driveFile = new \Google\Service\Drive\DriveFile();
            $driveFile->setName($fileName);
            $driveFile->setParents([$examFolderId]);

            $result = $this->service->files->create($driveFile, [
                'data' => file_get_contents($file->getRealPath()),
                'mimeType' => $file->getMimeType(),
                'uploadType' => 'multipart'
            ]);

            Log::info('Exam submission uploaded successfully', [
                'course' => $courseTitle,
                'exam' => $examTitle,
                'student' => $studentName,
                'file' => $fileName,
                'file_id' => $result->id
            ]);

            return [
                'id' => $result->id,
                'url' => "https://drive.google.com/file/d/{$result->id}/view",
                'file_name' => $fileName
            ];

        } catch (\Exception $e) {
            Log::error('Exam submission upload failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Upload profile photo: create or find user folder and upload the file
     */
    public function uploadProfilePhoto(\Illuminate\Http\UploadedFile $file, string $userName)
    {
        $parentFolderId = env('GOOGLE_PROFILE_FOLDER_ID');
        // find existing user folder
        $existing = $this->findFolderByName($userName, $parentFolderId);
        if ($existing) {
            $folderId = $existing['id'];
        } else {
            // create user folder
            $folderMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $userName,
                'parents' => [$parentFolderId],
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);
            $newFolder = $this->service->files->create($folderMetadata);
            $folderId = $newFolder->id;
        }
        // use original file name
        $fileName = $file->getClientOriginalName();
        return $this->uploadFile($file, $fileName, $folderId);
    }
}
