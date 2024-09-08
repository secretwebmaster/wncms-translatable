<?php

namespace Wncms\Translatable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Wncms\Translatable\Translatable
 *
 * @method static void fallback(?string $fallbackLocale = null, ?bool $fallbackAny = false, $missingKeyCallback = null)
 */
class Translatable extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'translatable';
    }
}
