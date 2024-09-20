<?php

namespace Wncms\Translatable\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Wncms\Translatable\Tests\TestCase;
use Wncms\Translatable\Tests\Models\TestPost;

class HasTranslationsTest extends TestCase
{
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
}
