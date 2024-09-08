<?php

namespace Wncms\Translatable\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = ['translatable_type', 'translatable_id', 'field', 'locale', 'value'];

    /**
     * Get the owning translatable model.
     */
    public function translatable()
    {
        return $this->morphTo();
    }
}
