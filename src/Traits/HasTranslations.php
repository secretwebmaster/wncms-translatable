<?php

namespace Wncms\Translatable\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait HasTranslations
 * 
 * This trait adds functionality to make Eloquent models translatable. 
 * It allows storing translations for model fields in a separate table and 
 * provides methods to retrieve and set translations for specific locales.
 * 
 * @package Wncms\Translatable\Traits
 */
trait HasTranslations
{
    /**
     * Boot the HasTranslations trait and set up model events and scopes.
     * 
     * Adds a global scope to automatically load translations with the model.
     * Handles translations before saving the model to the database.
     */
    public static function bootHasTranslations()
    {
        static::addGlobalScope('translations', function (Builder $builder) {
            $builder->with('translations');
        });

        static::saving(function (Model $model) {
            // Handle translations before saving the model
            if (App::getLocale() !== config('app.locale') || config('translatable.create_translation_for_default_locale')) {
                $model->handleTranslationsBeforeSave();
                return false; // Prevent model save until translations are handled
            }
        });
    }

    /**
     * Define a polymorphic one-to-many relationship to the Translation model.
     * 
     * @return MorphMany
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(\Wncms\Translatable\Models\Translation::class, 'translatable');
    }

    /**
     * Override getAttribute method to return translations for translatable fields.
     * 
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            if (in_array($key, $this->translatable)) {
                $value = $this->getTranslation($key);
            } else {
                $value = parent::getAttribute($key);
            }
            return $value;
        } else {
            return parent::getAttribute($key);
        }
    }

    /**
     * Retrieve the translation for a given field and locale.
     * 
     * @param string $field
     * @param string|null $locale
     * @return mixed
     */
    public function getTranslation(string $field, ?string $locale = null)
    {
        $locale = $locale ?? App::getLocale();
        $translation = $this->translations()->where('field', $field)->where('locale', $locale)->first();
        return $translation ? $translation->value : parent::getAttribute($field);
    }

    /**
     * Set or update the translation for a given field and locale.
     * 
     * @param string $field
     * @param string $locale
     * @param mixed $value
     * @return mixed
     */
    public function setTranslation(string $field, string $locale, $value)
    {
        return $this->translations()->updateOrCreate(
            ['field' => $field, 'locale' => $locale],
            ['value' => $value]
        );
    }

    /**
     * Get the list of translatable fields for the model.
     * 
     * @return array
     */
    public function getTranslatable()
    {
        return $this->translatable;
    }

    /**
     * Handle the translations before saving the model.
     * 
     * Saves the translation for each translatable field.
     * 
     * @return void
     */
    protected function handleTranslationsBeforeSave()
    {
        foreach ($this->translatable as $field) {
            if (!is_null($this->getAttribute($field))) {
                $this->setTranslation($field, App::getLocale(), $this->getAttribute($field));
            }
        }
    }
}
