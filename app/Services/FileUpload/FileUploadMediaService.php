<?php


namespace App\Services\FileUpload;

use App\Services\FileUpload\Trait\CreateDirectory;
use App\Services\FileUpload\Trait\ResizeImage;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image as ImageFacade;
use Intervention\Image\Image;

class FileUploadMediaService implements FileUploadInterface
{

    use ResizeImage;
    /**
     * @var
     */
    private
        $model,
        $media,
        $file,
        $fileName,
        $image;

/*
 * FileService($file)->store()
 * */

    public function __construct($file)
    {
        $this->image = new FileUploadService($file); 
    }

    public function setModel(Model $model)
    {
        $this->fileName = $this->image->store();

        $this->media = $model->addMedia($this->image->getFilePath());
        
        return $this;
    }

    public function store($collection = null, $disk = '')
    {
        $this->media->toMediaCollection($collection, $disk);
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function __call($name, $arguments)
    {
        try{
            $this->media?->$name(...$arguments);
        }catch(Exception $e){

        }

        try{
            $this->image->$name(...$arguments);
        }catch(Exception $e){

        }
        
        return $this;
    }

}
