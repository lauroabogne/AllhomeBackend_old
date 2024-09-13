<?php 

namespace App\Utility;

use Cake\Filesystem\Folder;
use Cake\Filesystem\Filesystem;
use Laminas\Diactoros\UploadedFile;
use Cake\Log\Log;

class FileUtilities
{
 
    /**
     * Creates a directory at the specified path if it doesn't already exist.
     *
     * @param string $path The path to the directory.
     * @return bool True if the directory exists or was created successfully, false otherwise.
     */
    public static function createDirectory(string $path): bool
    {
        try {
           
            $filesystem = new Filesystem();
            if (!is_dir($path)) {
                $filesystem->mkdir($path, 0755);
            }
            return true;
        } catch (\Exception $e) {
            // Handle the exception appropriately
            // (e.g., log the error, throw a custom exception, etc.)
            debug($e->getMessage());
            Log::error('An error occurred: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validates if the provided file is a valid image.
     *
     * @param mixed $file The file to check.
     * @return bool True if the file is a valid image, false otherwise.
     */
    public static function isValidImage($file): bool
    {
        if (!($file instanceof UploadedFile)) {
            return false;
        }

        // Check if the file is of a valid MIME type
        $validMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getClientMediaType(), $validMimeTypes)) {
            return false;
        }

        return true;
    }
     /**
     * Returns the size of the image in megabytes.
     *
     * @param UploadedFile $file The file whose size is to be determined.
     * @return float The size of the file in megabytes.
     */
    public static function getImageSizeInMb(UploadedFile $file): float
    {
        // Get the size of the file in bytes
        $sizeInBytes = $file->getSize();

        // Convert bytes to megabytes
        $sizeInMb = $sizeInBytes / (1024 * 1024);

        return round($sizeInMb, 2); // Rounding to 2 decimal places
    }
    /**
     * Save an uploaded file to the specified directory.
     *
     * @param UploadedFile $file The uploaded file instance.
     * @param string $uploadDir The directory where the file should be saved.
     * @param string $fileName The filename without extension.
     * @return string|false The path of the saved file on success, or false on failure.
     */
    public static function saveImage(UploadedFile $file, string $uploadDir, string $fileName): bool
    {
       
        $targetPath = $uploadDir . DS . $fileName.'.'.pathinfo($file->getClientFilename(),PATHINFO_EXTENSION);

        try {
            // Move the uploaded file to the target path
            $file->moveTo($targetPath);
            return true;
        } catch (\Exception $e) {
            // Log the error or handle it appropriately
            // Log::error('File upload error: ' . $e->getMessage());
            debug($e->getMessage());
            Log::error('An error occurred: ' . $e->getMessage());
            return false;
        }
    }
}