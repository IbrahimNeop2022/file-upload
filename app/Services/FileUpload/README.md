# File Upload Service

#### Installation

```bach
// install intervention image package
composer require intervention/image

//open your Laravel config file config/app.php and add the following lines.

//In the $providers array add the service providers for this package.

Intervention\Image\ImageServiceProvider::class

//Add the facade of this package to the $aliases array.

'Image' => Intervention\Image\Facades\Image::class


// install media library package
composer require "spatie/laravel-medialibrary:^10.0.0"

php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"

php artisan migrate
```

1. [Intervention Image documentation](https://image.intervention.io/v2).
2. [Laravel-media library documentation](https://spatie.be/docs/laravel-medialibrary).

### 1- FileUploadService class

_this class store file and return file name to store it in your model_.

-   it provide all methods in intervention image package.
-   it provide config file to set max width, max height and quality.

Here are a few short examples of what you can do:

```php
$post = new Post();
//...
$post->$image = (new FileUploadService(request('image')))->store();
$post->save();
```

You can add path and disk, by default disk is public.

```php
$post = new Post();
//...
$post->$image = (new FileUploadService(request('image')))->store('posts', 's3');
$post->save();
```

You can use **_all methods in intervention image package_**.

```php
$post = new Post();
//...
$post->$image = (new FileUploadService(request('image')))
                    ->resize(400, 400)
                    ->crop(100, 100, 25, 25)
                    ->store('posts', 's3');
$post->save();
```

You can use **_delete old file_**, for example in update.

```php
$post = Post::find(1);
//...
$post->$image = (new FileUploadService(request('image')))
                    ->delete($post->image)
                    ->store('posts');
$post->save();
```

You can get path.

```php
$post = new Post();
//...
$fileUpload = new FileUploadService(request('image'));
$post->$image = $fileUpload->store('posts', 's3');
$filePath = $fileUpload->getFilePath();

$post->save();
```

\***\*Note:\*\*** you can clean code by Accessors & Mutators

```php
$post = Post::create([
        //...
]);

//In Post Model
public function setImageAttribute($image)
{
    $this->attributes['image'] = (new FileUploadService($image))->store('posts');
}
```

To get image.

```php
//In Post Model
public function getImgAttribute()
{
    return $this->image ? asset('storage/'. $this->image) : asset('images/post.jpg');
}
```

**_Dont forget_** run this command.

```bach
php artisan storage:link
```

---

### 2- FileUploadMediaService class

_this class use media-library package to store media files for your model_.

-   it provide all methods in intervention image package.
-   it provide all methods in media-library package.
-   it provide config file to set max width, max height and quality.

Here are a few short examples of what you can do:

```php
$post = new Post();
//...
$post->save();

(new FileUploadMediaService(request('image')))->setModel($post)->store();

```

To associate media with a model, the model must implement the following interface and trait:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class YourModel extends Model implements HasMedia
{
    use InteractsWithMedia;
}
```

You can add collection and disk, by default disk is public.

```php
$post = new Post();
//...
$post->save();

(new FileUploadMediaService(request('image')))->setModel($post)->store('images', 's3');

```

You can use **_all methods in intervention image package_**.

```php
$post = new Post();
//...
$post->save();

(new FileUploadMediaService(request('image')))
    ->resize(500, 200)
    ->crop(100, 100, 25, 25)
    ->setModel($post)
    ->store('images');
```

You can use **_all methods in media library package_**.

```php
$post = new Post();
//...
$post->save();

(new FileUploadMediaService(request('image')))
    ->resize(500, 200)
    ->setModel($post)
    ->usingName('my-image-name')
    ->withCustomProperties([
        'primaryColor' => 'red',
        'image-code'  => '12458558',
    ])
    ->store('image', 's3');
```

To retrieve files you can use the getMedia-method:

```php
    $mediaItems = $yourModel->getMedia();
```

the getFirstMedia and getFirstMediaUrl convenience-methods are also provided:

```php
    $media = $yourModel->getFirstMedia();

    $url = $yourModel->getFirstMediaUrl();
```

If you want to remove all associated media in a specific collection you can use the clearMediaCollection method. It also accepts the collection name as an optional parameter:

```php
    $yourModel->clearMediaCollection(); // all media will be deleted

    $yourModel->clearMediaCollection('images'); // all media in the images collection will be deleted
```

**_recommend:_** read [Laravel-media library documentation](https://spatie.be/docs/laravel-medialibrary).
