# MediaBundle (Media File Manager for Symfony)

MediaBundle is a media file manager bundle for Symfony with a REST API and an admin interface (React). It provides a clean and user-friendly way to upload, edit and delete files. It supports multiple formats. You can upload images, videos, audios, documents, zip files, etc.

With MediaBundle, you can easily compress your media files to reduce their size without sacrificing quality. Additionally, MediaBundle offers the ability to resize your media files to fit specific dimensions (breakpoints), making it easy to ensure that your images are the correct size for your website.

MediaBundle also integrates with your database to store and manage your media files. This means that you can easily keep track of all your media assets in one place, similar to how WordPress manages media files.

Table of Contents 
==================
* [Features](#features)
* [Video](#video)
* [Requirements](#requirements)
  * [Imagick extension](#imagick-extension)
  * [Compression Tools](#compression-tools)
* [Installation](#installation)
* [Configuration](#configuration)
  * [Full configuration](#full-configuration-with-default-values)
  * [Configuration Explanation](#configuration-explanation)
* [Usage](#usage)
  * [Security](#security) 
  * [Media File Manager](#media-file-manager)
  * [Form Types](#form-types)
  * [Retrieve media files in Twig](#retrieve-media-files-in-twig)
  * [Responsive images with Twig macro](#responsive-images-with-twig-macro)
  * [Standalone button to open the Media File Manager in selection mode](#standalone-button-to-open-the-media-file-manager-in-selection-mode)
  * [Events](#events)
* [Caveats](#caveats)
* [Extra](#extra)
* [To Do](#to-do)
* [License](#license)
* [Donate](#donate)

## Video

https://user-images.githubusercontent.com/2461400/208309129-280d4fdb-d3f5-4cb7-bd32-175db6bb6f70.mp4

## Features

* User-friendly interface:
  * Offers a wide range of configurable options, allowing you to customize its behavior to fit your needs
  * Single or multiple upload
  * List or grid view
  * Edit alt, title and name
  * Single and bulk delete
  * Search filters
  * Select single or multiple media with different form types
  * Sort by date. Soon I will add more sort options
  * Prevention of duplicate names. If an image already exists in the database, it automatically adds a suffix with the built-in [time](https://www.php.net/manual/en/function.time.php) function, but you can rename the image later without any problem.
* Resize images on upload, supported by:
  * Imagick extension 
  * GD extension
* Compression of images, supported by: 
  * Jpegoptim
  * Optipng
  * Pngquant
  * Gifsicle
  * Svgo
  * Cwebp
* Generate thumbnails with 4 types of breakpoint with the following default sizes: 
  * **Large:** [1024], I only put the width because that way the height is automatically calculated by saving the aspect ratio. Although you can change it, you can see it in [configuration](#configuration)
  * **Medium:** [768]
  * **Small:** [576]
  * **Xsmall:** [130, 130]

## Requirements

* PHP 8.1 or higher
* Symfony 5.4 or higher
* Doctrine ORM
* Imagick or GD extension: **Recommended Imagick** extension because it supports more formats than the GD extension
* Optional compression tools:
  * Jpegoptim
  * Optipng
  * Pngquant
  * Gifsicle
  * Svgo
  * Cwebp

### Imagick extension

#### Requirements
Imagick extension requires the `imagemagick >= 6.2.4` library installed in your system. You can install it with the following command:
```bash
sudo apt-get install imagemagick
```
#### Install Imagick extension

##### Case Debian/Ubuntu:
```sh
apt-get install php-imagick
```
##### Case Pecl
```sh
pecl install imagick
```
##### Case Docker
[Dockerfile](tools/docker/php-fpm/Dockerfile)

#### Enable the extension if it is not done automatically 👀
```ini
; php.ini
extension=imagick.so
```
#### Verify Installation
```bash
php -m | grep imagick
php -r 'phpinfo();' | grep imagick
php -r 'phpinfo();' | grep ImageMagick
```


### Compression Tools
None of these tools is mandatory; it already depends on the need to optimize the images or not.
```sh 
sudo apt-get install jpegoptim
sudo apt-get install optipng
sudo apt-get install pngquant
sudo apt-get install gifsicle
sudo apt-get install webp
```
[Dockerfile](tools/docker/php-fpm/Dockerfile)

## Installation

```sh
composer require ranky/media-bundle
```

If you use Symfony Flex, the config/packages/ranky_media.php config file is created automatically. Otherwise:

#### Step 1: Create the following file and configure it for your application. 

The minimum required configuration is provided, in the [configuration](#configuration) you will see all the options. 

YAML
```yaml 
# config/packages/ranky_media.yaml
ranky_media:
  user_entity: App\Entity\User
  api_prefix: /admin
```
PHP
```php
# config/packages/ranky_media.php
  return static function (RankyMediaConfig $rankyMediaConfig) {
    $rankyMediaConfig
        ->userEntity(User::class)
        ->apiPrefix('/admin') // Optional: The same prefix you use when importing the routes must be the same here
        ;
};
```
#### Step 2: Enable the bundle in the kernel:

```php
# config/bundles.php
return [
    // ...
    Ranky\MediaBundle\RankyMediaBundle::class => ['all' => true],
];
```

#### Step 3: Import the routes:

YAML

```yaml 
# config/routes/ranky_media.yaml
ranky_media:
  resource: "@RankyMediaBundle/config/routes.php"
  prefix: /admin # Optional: The same prefix you use when importing the routes must be the "api_prefix" in the bundle options.
```

PHP

```php 
# config/routes/ranky_media.php
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes) {
    $routes
        ->import('@RankyMediaBundle/config/routes.php', 'annotation')
        ->prefix('/admin')
        ;
};
```

## Configuration

### Full configuration with default values
Except `userEntity`, all the other options are optional. 

```php
# config/packages/ranky_media.php
return static function (RankyMediaConfig $rankyMediaConfig) {

    $rankyMediaConfig
        ->userEntity(User::class)
        ->userIdentifierProperty('username')
        ->apiPrefix(null)
        ->uploadDirectory('%kernel.project_dir%/public/uploads')
        ->uploadUrl('/uploads')
        ->paginationLimit(30)
        ->dateTimeFormat('Y-m-d H:i')
        ->mimeTypes([])
        ->maxFileSize(7340032)
        ->disableCompression(false)
        ->compressOnlyOriginal(false)
    ;

    $rankyMediaConfig->image()
        ->resizeDriver(ImageResizeDriver::IMAGICK->value)
        ->resizeGifDriver(GifResizeDriver::NONE->value)
        ->quality(80)
        ->originalMaxWidth(1920)
        ->breakpoints()
            ->large([1024])
            ->medium([768])
            ->small([576])
            ->xsmall([130, 130])
    ;
};
```

### Configuration Explanation

**user_entity** (string, `required`)

This is the fully qualified class name (FQCN) of the user entity class. This is required in order to get the username in case you are using a different UserIdentifier and in that way to be able to filter media by user.

**Example:** `User::class`

**user_identifier_property** (string, default: `username`)

This is the property of the user entity that contains the user identifier. This is required if it is different from the `username`.

**date_time_format** (string, default: `Y-m-d H:i`)

This is the format in which the creation and update date of a media are displayed in the list view and in the media modal.

**api_prefix** (string, default: `null`)

This is mandatory if you want to import bundle routes with a prefix like "/admin", to follow some kinds of convention in your admin or panel.

See more in the [security](#security) section

This configuration will also create a global twig variable (`ranky_media_api_prefix`) that you can use later in your templates.

**Example:** `/admin`

**upload_directory** (string, default: `%kernel.project_dir%/public/uploads`)

This is the directory where uploaded files will be stored.

**Example:** %kernel.project_dir%/public/uploads

**upload_url** (string, default: `/uploads`)

This is the URL where uploaded files will be accessible.

**Example:** `/uploads` or `https://mydomain.test/uploads`

**mime_types** (array, default: `[]`)

This is an array of allowed MIME types. An empty array means that all MIME types are allowed.

**Examples:** 
 * []
 * ['image/jpeg', 'image/png', 'application/pdf'] 
 * ['image/*']
 * ['.jpg', '.pdf']

**disable_compression** (boolean, default: `false`)

This allows you to disable compression in order to avoid the small overhead that it produces after resizing.

**compress_only_original** (boolean, default: `false`)

This will compress only the original image and will ignore thumbnails. This can be a good option as the thumbnails are already quite small, and sometimes it may not be necessary to compress them.

**max_file_size** (integer, default: `7340032`)

This is the maximum allowed file size in `bytes`. The default value is 7340032 (7 MB).

**pagination_limit** (integer, default: `30`)

This is the number of items that will be shown per page in the Media File Manager. The default value is 30.

**image**

This is the configuration for the image settings.

**resize_driver** (enum, default: `ImageResizeDriver::IMAGICK->value`)

This is the driver that will be used for image resizing. The available drivers are:
 * imagick `ImageResizeDriver::IMAGICK->value`
 * gd `ImageResizeDriver::GD->value`

[Caveats](#caveats)

**resize_gif_driver** (enum, default: `GifResizeDriver::NONE->value`)

This is the driver that will be used for GIF image resizing. The available drivers are:
* none `GifResizeDriver::NONE->value`
* ffmpeg `GifResizeDriver::FFMPEG->value`
* gifsicle `GifResizeDriver::GIFSICLE->value`
* imagick `GifResizeDriver::IMAGICK->value`

**quality** (integer, default: `80`)

This is the quality of the compression images. The default value is 80.

**original_max_width** (integer, default: `1920`)

This is the maximum width for the original file. This will prevent the original file from being stored with a large number of megabytes. 
A null value will not resize the image.

**breakpoints** (array, default: `['large' => [1024], 'medium' => [768], 'small' => [576], 'xsmall' => [130, 130]]`)

This is an array of breakpoints that will be used to generate thumbnails with different sizes. The available breakpoints are: 
* large `Breakpoint::LARGE->dimensions()` [1024]
* medium `Breakpoint::MEDIUM->dimensions()` [768]
* small `Breakpoint::SMALL->dimensions()` [576]
* xsmall `Breakpoint::XSMALL->dimensions()` [130, 130]

Each breakpoint has its own default dimensions, but these can be overridden by specifying custom dimensions in the configuration.

## Usage

### Security
All routes in this bundle start with `/ranky/media` without any kind of security.
But we all know that in Symfony it's very easy to protect a route with certain roles, like, for example:

```yaml
# config/packages/security.yaml
  # Note: Only the *first* access control that matches will be used
  access_control:
    # additional security lives in the controllers
    - { path: ^/ranky/media, roles: ROLE_USER }
    - { path: ^/admin,     roles: ROLE_USER }
    - { path: ^/admin/users, roles: ROLE_SUPER_ADMIN }
    - { path: ^/login$,    roles: PUBLIC_ACCESS }
    - { path: /, roles: PUBLIC_ACCESS }

```
With this configuration you have already secured the Media File Manager with the role `ROLE_USER`.

It is also possible as we have seen previously that you want to import the routes of this bundle with the prefix of `/admin`, then all you have to do is to put the prefix in the security path:

```yaml
# Note: Only the *first* access control that matches will be used
  access_control:
    - { path: ^/admin/ranky/media, roles: ROLE_USER }
    - { path: ^/admin,     roles: ROLE_USER }
    - { path: ^/admin/users, roles: ROLE_SUPER_ADMIN }
```
Don't forget to set the [route prefix](#step-1--create-the-following-file-and-configure-it-for-your-application) in the bundle configuration.

### Media File Manager

You can do the same as me and create a page and put the following code.
Note that it is not mandatory to use blocks, you can import the assets as you prefer.

```html
{% block stylesheets %}
    {{- parent() -}}
    {{ encore_entry_link_tags('ranky_media', null, 'ranky_media') }}
{% endblock %}

{% block javascript %}
    {{- parent() -}}
    {{ encore_entry_script_tags('ranky_media', null, 'ranky_media', attributes={
        defer: true
    }) }}
{% endblock %}

{% block body %}
    <div class="ranky-media" data-api-prefix="{{ ranky_media_api_prefix }}"></div>
{% endblock %}
```

### Form types

Only one type of form, but with 4 ways to store the data in the database:

#### 1. Single selection. Store the mediaId (Ulid) without association

```php
#[ORM\Column(name: 'media_id', type: Types::INTEGER, nullable: true)]
private ?MediaId $mediaId;
```

```php
use Ranky\MediaBundle\Presentation\Form\RankyMediaFileManagerType;

class MyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mediaId', RankyMediaFileManagerType::class, [
                'label' => 'Media Id',
                'modal_title' => 'Featured image',
            ])
        ;
    }
}
```

#### 2. Multiple selection. Store the array of mediaId (json) without association

```php
#[ORM\Column(name: 'gallery', type: Types::JSON, nullable: true)]
private ?array $gallery;
```

```php
use Ranky\MediaBundle\Presentation\Form\RankyMediaFileManagerType;

class MyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gallery', RankyMediaFileManagerType::class, [
                'label' => 'Array of Ids',
                'multiple' => true,
                'modal_title' => 'Gallery',
            ])
        ;
    }
}
```

#### 3. Single selection. Store the mediaId (Ulid) with ManyToOne association

```php
#[ORM\ManyToOne(targetEntity: Media::class)]
#[ORM\JoinColumn(name: 'media', referencedColumnName: 'id', nullable: true)]
private ?Media $media;
```

```php
use Ranky\MediaBundle\Presentation\Form\RankyMediaFileManagerType;

class MyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('media', RankyMediaFileManagerType::class, [
              'label'              => 'Media ManyToOne',
              'mapping'            => true,
              'modal_title'        => 'Featured image',
            ])
        ;
    }
}
```

#### 4. Multiple selection. Store the media collection with ManyToMany association

```php
#[ORM\JoinTable(name: 'pages_medias')]
#[ORM\JoinColumn(name: 'page_id', referencedColumnName: 'id')]
#[ORM\InverseJoinColumn(name: 'media_id', referencedColumnName: 'id')]
#[ORM\ManyToMany(targetEntity: Media::class)]
private ?Collection $medias;

public function __construct()
{
    $this->medias = new ArrayCollection();
}
```

```php
use Ranky\MediaBundle\Presentation\Form\RankyMediaFileManagerType;

class MyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('medias', RankyMediaFileManagerType::class, [
                'label'              => 'Media Collection',
                'mapping'            => true,
                'modal_title'        => 'Gallery',
                'multiple_selection' => true,
            ])
        ;
    }
}
```

### Retrieve media files in Twig
To retrieve the media files, you have a service will help you. Let's see the examples in the same order as we have looked in the types of forms.

#### 1. ...
```twig
{# @var media \Ranky\MediaBundle\Application\DataTransformer\Response\MediaResponse #}
{% set media = ranky_media.findById(page.mediaId) %}
{{ dump(media) }}
{% if media %}
  <p>
      <img src="{{ media.file.url }}"
           alt="{{ media.description.alt }}"
           title="{{ media.description.title }}"
      />
  </p>
  <p>{{ ranky_media_url(media.file.url,true) }}</p>
  <p>Thumbnails</p>
  {% for thumbnail in media.thumbnails %}
      {# @var thumbnail \Ranky\MediaBundle\Application\DataTransformer\Response\ThumbnailResponse #}
      <p>Thumbnail
          breakpoint: {{ thumbnail.breakpoint }} {{ thumbnail.dimension.asString }}</p>
      <img src="{{ thumbnail.url }}"
           alt="{{ media.description.alt }}"
           title="{{ media.description.title }}"
      />
      <p>{{ ranky_media_url(thumbnail.url,true) }}</p>
      <p>{{ ranky_media_thumbnail_url(thumbnail.url,thumbnail.breakpoint, true) }}</p>
  {% endfor %}
{% endif %}
```
#### 2. ...

```twig
{% set medias = ranky_media.findByIds(page.gallery) %}
{% for media in medias %}
  {# @var media \Ranky\MediaBundle\Application\DataTransformer\Response\MediaResponse #}
  {# ... #}
{% endfor %}
```
#### 3. ...

```twig
{% set media = ranky_media.mediaToResponse(page.media) %}
{# ... #}
```
#### 4. ...

```twig
{% set medias = ranky_media.mediaCollectionToArrayResponse(page.medias) %}
{# ... #}
```

### Responsive images with Twig macro

```twig
{% import '@RankyMedia/macros.html.twig' as ranky_media %}
{% set media = ranky_media.findById(page.mediaId) %}
{{ rankyMedia.reponsive_image(media) }}
```

### Standalone button to open the Media File Manager in selection mode

You can use the same options as in the [form](templates/form.html.twig).
Remember to import the assets first
```twig
{{ encore_entry_script_tags('ranky_media', null, 'ranky_media', attributes={
defer: true }) }}
{{ encore_entry_link_tags('ranky_media', null, 'ranky_media') }}
```
The `ranky-media-open-modal` class is required, In order not to conflict with the form types.
```html
<button class="ranky-media-open-modal" data-api-prefix="{{ranky_media_api_prefix}}" data-modal-title="Media File Manager" data-multiple-selection="true">
  Media File Manager
</button>
```
```js
 document.addEventListener('DOMContentLoaded', () => {
  [...document.querySelectorAll('.ranky-media-open-modal')].forEach((element) => {
    element.addEventListener('ranky-media:selected-media',(event) => {
      console.log(event.detail);
    })
  });
});
```
### Events

* PreCreateEvent::NAME
* PostCreateEvent::NAME
* PreUpdateEvent::NAME
* PostUpdateEvent::NAME
* DeleteEvent::PRE_DELETE
* DeleteEvent::POST_DELETE

```php
use Ranky\MediaBundle\Infrastructure\Event\PreCreateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MyEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            PreCreateEvent::NAME => 'onPreCreate',
        ];
    }

    public function onPreCreate(PreCreateEvent $event)
    {
        // do something
    }
}
```

## Caveats

* Currently, [Uppy](https://github.com/transloadit/uppy) is used to support file uploads through the Media File Manager, and it requires [SSL certificate](#install-local-certificates) or be available via localhost.
  See more https://github.com/transloadit/uppy/issues/4133
* If you are using React, you will have a problem because this bundle adds React, and you can have two versions of React on one page. This will be fixed as soon as I registered a package in NPM.
* Currently, Doctrine ORM is required.

## Extra

### Demo

In a few days there will be a complete demo. For now, you can watch the [video](#video)

### Install local certificates

```sh
mkcert -install
mkcert "*.example.test"
```
More info https://github.com/FiloSottile/mkcert

### Docker
You can see how to install PHP extensions and compression tools through Docker in the [Dockerfile](tools/docker/php-fpm/Dockerfile) I used it for testing.



## To Do
* Image Editor
* Create NPM package, so you can use/import and not have multiple versions of React 
* Add sorting filters
* PDF compression with Ghostscript
* Video compression and resizing with FFmpeg
* Audio compression
* Create, view and edit EXIF data
* Creation and organization of directories
* Adapters for [file storage](https://github.com/thephpleague/flysystem-bundle): S3, Azure, Google Cloud, etc.
* Adapters for database: Dbal, MongoDB, etc.
* Add more tests

## License

MediaBundle is licensed under the MIT License – see the [LICENSE](LICENSE) file for details

## Donate

