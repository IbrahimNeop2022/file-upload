<?php


namespace App\Services\FileUpload;


use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image as ImageFacade;
use Intervention\Image\Image;

class FileUploadMediaService
{

    /**
     * @var
     */
    private
        $model,
        $media,
        $file,
        $size,
        $maxWidth,
        $maxHeight,
        $quality,
        $image;

/*
 * FileService($file)->store()
 * */

    public function __construct($model, $file)
    {
        $this->file = is_string($file) ? new File($file) : $file;

        if (Str::contains($file->getMimeType(), 'image')) {
            $this->image = ImageFacade::make($file);
        }

        $this->model = $model;

        $this->media = $this->model->addMedia($this->file);

        $this->size = $file->getSize();

        $this->maxWidth = 1024;

        $this->maxHeight = 768;

        $this->quality = 60;
    }

    public function store($collection = null, $disk = '')
    {
        if ($this->image) {
            $this->resizeImage($this->image);
        }

        $this->media->toMediaCollection($collection, $disk);
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

    public function __call($name, $arguments)
    {
        $this->media->$name(...$arguments);
        return $this;
    }

}
