# Wncms Translatable

A Laravel package to add translation capabilities to any Eloquent model by storing translations in a separate table with a polymorphic relationship. Easily manage translations for specified fields in multiple locales.

## Features
- Translates specified model fields
- Stores translations in a separate `translations` table
- Polymorphic relationship to support multiple models
- Easy to use with a simple trait

## Installation

1. Install the package via Composer:

```bash
composer require wncms/laravel-translatable
```

2. Publish the migration file:
```bash
php artisan vendor:publish --tag=translatable-migrations
```

3. Run the migration to create the `translations` table:
```bash
php artisan migrate
```

## Usage

1. Add the `HasTranslations` trait to your model
To make any model translatable, simply add the `HasTranslations` trait to your Eloquent model and define the `$translatable` property with the list of fields that should be translatable.
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Wncms\Translatable\Traits\HasTranslations;

class Post extends Model
{
    use HasTranslations;

    // Define the fields that can be translated
    public $translatable = ['title', 'description'];

    protected $fillable = ['title', 'description'];
}

```

2. Setting Translations
You can set translations for translatable fields by passing an array with locale keys:
```php
$post = Post::find(1);

// Set translations for 'title' and 'description'
$post->title = ['en' => 'Hello World', 'fr' => 'Bonjour le monde'];
$post->description = ['en' => 'Description in English', 'fr' => 'Description en Français'];
$post->save();
```

3. Getting Translations
When retrieving translatable fields, the package automatically returns the value based on the current locale of the application:
```php
// Get the 'title' based on the app's current locale
echo $post->title; // Outputs 'Hello World' or 'Bonjour le monde' depending on the locale
```

You can also retrieve translations for a specific locale:
```php
// Get the 'title' in French
echo $post->getTranslation('title', 'fr'); // Outputs 'Bonjour le monde'

// Get the 'description' in English
echo $post->getTranslation('description', 'en'); // Outputs 'Description in English'
```

4. Setting Translations for a Specific Locale
If you want to set a translation for a specific locale without using an array, you can use the `setTranslation` method:
```php
$post->setTranslation('title', 'es', 'Hola Mundo');
$post->save();
```

5. Table Structure
The package will create a `translations` table with the following structure:

| Field             | Type         | Description                                  |
|-------------------|--------------|----------------------------------------------|
| `id`              | bigint       | Primary key                                  |
| `translatable_type`| string       | Model class name (e.g., `App\Models\Post`)    |
| `translatable_id`  | bigint       | The ID of the related model instance         |
| `field`           | string       | The name of the field being translated       |
| `locale`          | string       | Locale for the translation (e.g., `en`, `fr`)|
| `value`           | text         | The translated value                         |
| `created_at`      | timestamp    | Timestamp for when the translation was created|
| `updated_at`      | timestamp    | Timestamp for when the translation was updated|

6. Custom Locale
You can override the current locale by passing the desired locale to the `getTranslation` method:
```php
$post->getTranslation('title', 'fr'); // Output: 'Bonjour le monde'
```

Contributing
Contributions are welcome! Please feel free to submit a Pull Request.

License
This package is open-sourced software licensed under the MIT license.