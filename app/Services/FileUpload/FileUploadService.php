<?php


namespace App\Services\FileUpload;


use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image as ImageFacade;
use Intervention\Image\Image;

class FileUploadService
{

    /**
     * @var
     */
    private
        $file,
        $fileName,
        $size,
        $maxWidth,
        $maxHeight,
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

        $this->size = $file->getSize();

        $this->maxWidth = 1024;

        $this->maxHeight = 768;

        $this->quality = 60;
    }

    public function store($path = null, $disk = null)
    {
        if ($this->image) {
            $this->resizeImage($this->image);
            $this->storeAsImage($path, $disk);
            return $this->fileName;
        }

        $this->setFileName($this->file->store($path, $disk));

        return $this->fileName;
    }

    public function storeAsImage ($path, $disk)
    {
        $this->setFileName($this->file->hashName($path));

        $imagePath = Storage::disk($this->getDisk($disk))->path($this->fileName);

        $this->image->save($imagePath, $this->quality);

    }

    public function resizeImage(Image $image)
    {
        if ($image->width() > $this->maxWidth) {
            $image->resize($this->maxWidth, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        if ($image->height() > $this->maxHeight) {
            $image->resize(null, $this->maxHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
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

    /**
     * @param $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getDisk($disk = null)
    {
        return $disk ?: 'public';
    }

    public function __call($name, $arguments)
    {
        $this->image->$name(...$arguments);
        return $this;
    }

}
