<?php

namespace Wncms\Translatable\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Builder;

trait HasTranslations
{
    /**
     * Boot the trait and add global scope for translations.
     *
     * @return void
     */
    public static function bootHasTranslations()
    {
        static::addGlobalScope('translations', function (Builder $builder) {
            $builder->with('translations'); // Automatically load translations with the model
        });

        // Handle translation saving logic before the model is saved
        static::saving(function (Model $model) {
            if (function_exists('wncms') && !wncms()->isDefaultLocale() && $model->exists) {
                $model->handleTranslationsBeforeSave(); // Handle translation logic
                return false; // Prevent saving the model until translations are handled
            }
        });
    }

    /**
     * Define the relationship to the translations.
     *
     * @return MorphMany
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(\Wncms\Translatable\Models\Translation::class, 'translatable');
    }

    /**
     * Override the getAttribute method to handle translations.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            // Check if the attribute is translatable
            if (in_array($key, $this->translatable)) {
                $value = $this->getTranslation($key); // Get the translated value
            } else {
                $value = parent::getAttribute($key); // Fallback to the default value
            }
            return $value;
        } else {
            return parent::getAttribute($key); // Fallback to parent method if key doesn't exist
        }
    }

    /**
     * Get the translation for a specific field and locale.
     *
     * @param string $field
     * @param string|null $locale
     * @return mixed
     */
    public function getTranslation(string $field, ?string $locale = null)
    {
        $locale = $locale ?? App::getLocale(); // Use the provided locale or the current app locale
        $translation = $this->translations()->where('field', $field)->where('locale', $locale)->first();
        return $translation ? $translation->value : parent::getAttribute($field); // Return translated value or fallback
    }

    /**
     * Set the translation for a specific field and locale.
     *
     * @param string $field
     * @param string $locale
     * @param mixed $value
     * @return void
     */
    public function setTranslation(string $field, string $locale, $value)
    {
        $translation = $this->translations()->where('field', $field)->where('locale', $locale)->first();

        // Update existing translation or create a new one
        if ($translation) {
            $translation->update(['value' => $value]);
        } else {
            $this->translations()->create([
                'field'  => $field,
                'locale' => $locale,
                'value'  => $value,
            ]);
        }
    }

    /**
     * Get the list of translatable fields.
     *
     * @return array
     */
    public function getTranslatable()
    {
        return $this->translatable;
    }

    /**
     * Handle translation logic before saving the model.
     *
     * @return void
     */
    protected function handleTranslationsBeforeSave()
    {
        foreach ($this->translatable as $field) {
            // Set translation for each translatable field if it has a value
            if (!is_null($this->getAttribute($field))) {
                $this->setTranslation($field, App::getLocale(), $this->getAttribute($field));
            }
        }
    }
}
