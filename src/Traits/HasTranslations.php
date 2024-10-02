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
 * It allows storing translations fpor model fields in a separate table and 
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
            // if current locale is the default locale and the config is set to not create translation for default locale
            if (config(config('translatable.default_locale_key', 'app.locale')) == config('app.locale') && !config('translatable.create_translation_for_default_locale')) {
                return false;
            }
        });

        static::saved(function (Model $model) {
            // Handle translations after saving the model
            $model->handleTranslationsAfterSave();
        });

        static::updating(function (Model $model) {
            // Handle translations before updating the model
            // if current locale is the default locale and the config is set to not create translation for default locale
            if (config(config('translatable.default_locale_key', 'app.locale')) == config('app.locale') && !config('translatable.create_translation_for_default_locale')) {
                return false;
            }
        });

        static::updated(function (Model $model) {
            // Handle translations after updating the model
            $model->handleTranslationsAfterSave();
        });

        static::deleting(function (Model $model) {
            // Delete translations related to the model
            $model->translations()->delete();
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
        // Check if the key is translatable
        if (in_array($key, $this->translatable)) {
            return $this->getTranslation($key);
        }

        // Default behavior for non-translatable attributes
        return parent::getAttribute($key);
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

        if ($translation) {
            $value = $translation->value;

            // Handle specific cast types
            if ($this->hasCast($field, 'array') || $this->hasCast($field, 'json')) {
                return json_decode($value, true);
            }

            if ($this->hasCast($field, 'datetime')) {
                return \Illuminate\Support\Carbon::parse($value);
            }

            if ($this->hasCast($field, 'boolean')) {
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }

            if ($this->hasCast($field, 'integer')) {
                return (int) $value;
            }

            if ($this->hasCast($field, 'float') || $this->hasCast($field, 'double')) {
                return (float) $value;
            }

            if ($this->hasCast($field, 'object')) {
                return json_decode($value);
            }

            if ($this->hasCast($field, 'collection')) {
                return collect(json_decode($value, true));
            }

            if ($this->hasCast($field, 'encrypted')) {
                return decrypt($value);
            } 

            return $value; // Return the raw value if no special casting is needed
        }

        // If no translation found, return the parent's attribute
        return parent::getAttribute($field);
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
        // Handle specific cast types
        if ($this->hasCast($field, 'array') || $this->hasCast($field, 'json')) {
            $value = json_encode($value);
        }

        if ($this->hasCast($field, 'datetime')) {
            // If the value is not already a Carbon instance, convert it
            if (!($value instanceof \Illuminate\Support\Carbon)) {
                $value = \Illuminate\Support\Carbon::parse($value);
            }
        }

        if ($this->hasCast($field, 'boolean')) {
            $value = $value ? 1 : 0; // Convert boolean to integer
        }

        if ($this->hasCast($field, 'integer')) {
            $value = (int) $value;
        }

        if ($this->hasCast($field, 'float') || $this->hasCast($field, 'double')) {
            $value = (float) $value;
        }

        if ($this->hasCast($field, 'object')) {
            $value = json_encode($value);
        }

        if ($this->hasCast($field, 'collection')) {
            $value = json_encode($value->toArray());
        }

        if ($this->hasCast($field, 'encrypted')) {
            $value = encrypt($value);
        }

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
     * Handle the translations after saving the model.
     * 
     * Saves the translation for each translatable field.
     * 
     * @return void
     */
    protected function handleTranslationsAfterSave()
    {
        foreach ($this->translatable as $field) {
            if (!is_null(request($field))) {
                $this->setTranslation($field, App::getLocale(), parent::getAttribute($field));
            }
        }
    }
    
    public function toArray()
    {
        $attributes = parent::toArray();

        foreach ($this->translatable as $field) {
            if (array_key_exists($field, $attributes)) {
                $attributes[$field] = $this->getTranslation($field);
            }
        }

        return $attributes;
    }
}
