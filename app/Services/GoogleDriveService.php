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
}
