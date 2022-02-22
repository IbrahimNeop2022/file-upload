<?php

namespace App\Models;

use App\Services\FileUpload\FileUploadMediaService;
use App\Services\FileUpload\FileUploadService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use function Spatie\MediaLibrary\MediaCollections\usingName;

class Post extends Model  implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $guarded = [];


    public function setImageAttribute($image)
    {
        $img = (new FileUploadService($image))->resize(100,100);
        $this->attributes['image'] = $img->store('posts');


        (new FileUploadMediaService($image))
            ->resize(150,100)
            ->crop(50, 50, 10, 10)
            ->setModel($this)
            ->usingName('ImageName')
            ->store();
    }

    public function getImgAttribute()
    {
        if($this->getFirstMediaUrl('posts')) {
            return $this->getFirstMediaUrl('posts');
        }
       return $this->image ? asset('storage/'. $this->image) : asset('images/post.jpg');
    }
}
