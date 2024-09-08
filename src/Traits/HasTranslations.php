<?php

namespace Wncms\Translatable\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;

trait HasTranslations
{
    // Define the property for translatable fields
    public array $translatable = [];

    /**
     * Get the model's translations.
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(\Wncms\Translatable\Models\Translation::class, 'translatable');
    }

    /**
     * Retrieve the translation for a specific field.
     */
    public function getTranslation(string $field, ?string $locale = null)
    {
        $locale = $locale ?? App::getLocale();

        $translation = $this->translations()
            ->where('field', $field)
            ->where('locale', $locale)
            ->first();

        return $translation ? $translation->value : $this->getOriginal($field);
    }

    /**
     * Set a translation for a specific field.
     */
    public function setTranslation(string $field, string $locale, $value)
    {
        $translation = $this->translations()
            ->where('field', $field)
            ->where('locale', $locale)
            ->first();

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
     * Override the __get magic method to return translations.
     */
    public function __get($key)
    {
        if (in_array($key, $this->translatable)) {
            return $this->getTranslation($key);
        }

        return parent::__get($key);
    }

    /**
     * Override the __set magic method to set translations.
     */
    public function __set($key, $value)
    {
        if (is_array($value) && in_array($key, $this->translatable)) {
            foreach ($value as $locale => $translation) {
                $this->setTranslation($key, $locale, $translation);
            }
        } else {
            parent::__set($key, $value);
        }
    }
}
