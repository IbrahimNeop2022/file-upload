<?php


namespace App\Services\FileUpload;

use App\Services\FileUpload\Trait\CreateDirectory;
use App\Services\FileUpload\Trait\ResizeImage;
use Exception;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image as ImageFacade;
use Intervention\Image\Image;

class FileUploadService implements FileUploadInterface
{

    use ResizeImage, CreateDirectory;

    /**
     * @var
     */
    private
        $file,
        $fileName,
        $filePath,
        $quality,
        $image;

/*
 * FileService($file)->store()
 * */

    public function __construct($file)
    {
        $this->file = is_string($file) ? new File($file) : $file;

        if (Str::contains($file->getMimeType(), 'image')) {
            $this->image = ImageFacade::make($file);
        }

        $this->quality = config('file-upload.quality');
    }

    /**
     * @param null $path   # Not real path just folder name
     * @param null $disk   # ['local', 'public', 's3', ...]
     * @return string      # file name
     */
    public function store($path = '', $disk = null)
    {
        if ($this->image) {

            $this->resizeImage($this->image);
            
            $this->storeAsImage($path, $disk);

            return $this->fileName;
        }

        $this->fileName = $this->file->store($path, $disk);

        // Create folder if not exists, or abort uploading
        if (!$this->createDirectoryIfNotExists($path, $disk)) {
            return false;
        }

        $this->filePath = Storage::disk($this->getDisk($disk))->path($this->fileName);

        return $this->fileName;
    }

    public function storeAsImage ($path, $disk)
    {
        $this->fileName = $this->file->hashName($path);

        // Create folder if not exists, or abort uploading
        if (!$this->createDirectoryIfNotExists($path, $disk)) {
            return false;
        }

        $this->filePath = Storage::disk($this->getDisk($disk))->path($this->fileName);

        $this->image->save($this->filePath, $this->quality);
    }

    /**
     * @param $oldFile
     * @param null $disk
     * @return FileUploadService
     */
    public function delete($oldFile, $disk = null)
    {
        if ($oldFile && Storage::disk($this->getDisk($disk))->exists($oldFile)) {
            Storage::disk($this->getDisk($disk))->delete($oldFile);
        }

        return $this;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function getDisk($disk = null)
    {
        return $disk ?: 'public';
    }

    /**
     *  magic method to call all methods in Intervention\Image package 
     * @return FileUploadService
     */
    public function __call($name, $arguments)
    {
        try{
            $this->image->$name(...$arguments);
        }catch(Exception $e){

        }

        return $this;
    }

}
