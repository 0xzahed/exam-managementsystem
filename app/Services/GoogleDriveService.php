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
                'url' => "https://drive.google.com/file/d/{$driveFile->id}/view"
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
}
