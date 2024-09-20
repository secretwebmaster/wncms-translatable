<?php

namespace Wncms\Translatable\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Wncms\Translatable\Traits\HasTranslations;

class TestPost extends Model
{
    use HasTranslations;

    protected $fillable = ['title', 'content'];
    protected $translatable = ['title', 'content'];
}
