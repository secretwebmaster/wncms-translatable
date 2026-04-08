<?php

namespace Wncms\Translatable\Tests\Feature;

use Illuminate\Support\Facades\App;
use PHPUnit\Framework\Attributes\Test;
use Wncms\Translatable\Models\Translation;
use Wncms\Translatable\Tests\Models\TestPost;
use Wncms\Translatable\Tests\TestCase;

class HasTranslationsTest extends TestCase
{
    #[Test]
    public function it_loads_translatable_config()
    {
        // Access the configuration value
        $create_translation_for_default_locale = config('translatable.create_translation_for_default_locale');
        $this->assertTrue($create_translation_for_default_locale);
    }

    #[Test]
    public function it_can_save_and_retrieve_translations()
    {
        $post = TestPost::create([
            'title' => 'Original Title',
            'content' => 'Original Content'
        ]);
        
        $post->setTranslation('title', 'es', 'Título en Español');
        $post->setTranslation('content', 'es', 'Contenido en Español');

        $this->assertEquals('Original Title', $post->getTranslation('title', 'en'));
        $this->assertEquals('Original Content', $post->getTranslation('content', 'en'));
        $this->assertEquals('Título en Español', $post->getTranslation('title', 'es'));
        $this->assertEquals('Contenido en Español', $post->getTranslation('content', 'es'));
    }

    #[Test]
    public function it_returns_default_value_when_no_translation_exists()
    {
        $post = TestPost::create(['title' => 'Original Title', 'content' => 'Original Content']);

        $this->assertEquals('Original Title', $post->getTranslation('title', 'fr'));
        $this->assertEquals('Original Content', $post->getTranslation('content', 'fr'));
    }

    #[Test]
    public function it_updates_existing_translations()
    {
        $post = TestPost::create(['title' => 'Original Title', 'content' => 'Original Content']);
        $post->setTranslation('title', 'es', 'Título en Español');
        
        // Update translation
        $post->setTranslation('title', 'es', 'Título Modificado');

        $this->assertEquals('Título Modificado', $post->getTranslation('title', 'es'));
    }


    #[Test]
    public function it_loads_translations_with_global_scope()
    {
        // Create a new post with a translation
        $post = TestPost::create(['title' => 'Original Title']);
        $post->setTranslation('title', 'es', 'Título en Español');

        // Retrieve the post and assert that translations are loaded
        $retrievedPost = TestPost::first();
        $this->assertEquals('Título en Español', $retrievedPost->getTranslation('title', 'es'));
    }

    #[Test]
    public function it_loads_translations_with_eager_loading()
    {
        // Create a new post with a translation
        $post = TestPost::create(['title' => 'Original Title']);
        $post->setTranslation('title', 'es', 'Título en Español');

        // Retrieve the post with translations
        $retrievedPost = TestPost::with('translations')->first();
        $this->assertEquals('Título en Español', $retrievedPost->getTranslation('title', 'es'));
    }

    #[Test]
    public function it_can_skip_translations_for_default_locale()
    {
        // Set the default locale to Spanish
        app()->setLocale('es');

        // Create a new post with a translation
        $post = TestPost::create(['title' => 'Título en Español']);
        $post->setTranslation('title', 'en', 'Title in English');

        // Retrieve the post and assert that translations are loaded
        $retrievedPost = TestPost::first();
        $this->assertEquals('Título en Español', $retrievedPost->getTranslation('title', 'es'));
        $this->assertEquals('Title in English', $retrievedPost->getTranslation('title', 'en'));
    }

    #[Test]
    public function it_does_not_block_model_persistence_when_default_locale_translations_are_disabled()
    {
        config()->set('app.locale', 'en');
        config()->set('app.fallback_locale', 'en');
        config()->set('translatable.create_translation_for_default_locale', false);

        $post = TestPost::create([
            'title' => 'Primary Title',
            'content' => 'Primary Content',
        ]);

        $this->assertNotNull($post->getKey());
        $this->assertDatabaseCount('translations', 0);

        $post->update(['title' => 'Updated Title']);

        $this->assertSame('Updated Title', $post->fresh()->title);
        $this->assertDatabaseCount('translations', 0);
    }

    #[Test]
    public function it_deletes_related_translations_when_the_model_is_deleted()
    {
        $post = TestPost::create([
            'title' => 'Original Title',
            'content' => 'Original Content',
        ]);

        $post->setTranslation('title', 'es', 'Título en Español');

        $translationId = Translation::query()->value('id');

        $this->assertNotNull($translationId);

        $post->delete();

        $this->assertDatabaseMissing('translations', ['id' => $translationId]);
    }

    #[Test]
    public function it_uses_the_current_locale_when_accessing_translated_attributes_and_arrays()
    {
        $post = TestPost::create([
            'title' => 'English Title',
            'content' => 'English Content',
        ]);

        $post->setTranslation('title', 'zh_TW', '中文標題');

        App::setLocale('zh_TW');

        $freshPost = TestPost::query()->first();

        $this->assertSame('中文標題', $freshPost->title);
        $this->assertSame('中文標題', $freshPost->toArray()['title']);
    }
}
