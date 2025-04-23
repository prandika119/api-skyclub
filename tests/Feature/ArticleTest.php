<?php

namespace Tests\Feature;

use App\Models\Article;
use Database\Seeders\ArticleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    public function test_index_returns_paginated_articles()
    {
        $this->AuthAdmin();
        $this->seed(ArticleSeeder::class);
        $response = $this->get('/api/articles');

        $response->assertStatus(200);
        dump($response->getContent());
        $response->assertJsonStructure([
                'message',
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_store_creates_new_article()
    {
        $user = $this->AuthAdmin();
        $this->seed(ArticleSeeder::class);

        $data = [
            'title' => 'Test Article',
            'content' => ['paragraph' => 'This is a test article.'],
        ];

        $response = $this->post('/api/articles', $data);
        dump($response->getContent());
        $response->assertStatus(201);
        $response->assertJsonPath('message', 'success');
        $response->assertJsonStructure(['data' => ['id', 'title', 'content', 'author']]);
    }

    public function test_show_returns_specific_article()
    {
        $this->AuthAdmin();
        $this->seed(ArticleSeeder::class);
        $article = Article::first();

        $response = $this->get("/api/articles/{$article->id}");
        dump($response->getContent());
        $response->assertStatus(200)->assertJsonStructure(['status', 'data' => ['id', 'title', 'content', 'author']]);
    }

    public function test_update_modifies_existing_article()
    {
        $this->AuthAdmin();
        $this->seed(ArticleSeeder::class);

        $article = Article::first();
        $data = [
            'title' => 'Updated Title',
            'content' => ['paragraph' => 'Updated content.'],
        ];

        $response = $this->put("/api/articles/{$article->id}", $data);
        dump($response->getContent());
        $response->assertStatus(200)
            ->assertJsonPath('message', 'success')
            ->assertJsonPath('data.title', 'Updated Title');
    }

    public function test_destroy_deletes_article()
    {
        $this->AuthAdmin();
        $this->seed(ArticleSeeder::class);
        $article = Article::first();

        $response = $this->delete("/api/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'success');
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    public function test_add_article_photo_uploads_file()
    {
        $user = $this->AuthAdmin();
        $this->seed(ArticleSeeder::class);

        Storage::fake('public');

        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->postJson('/api/articles/photos', ['photos' => $file]);
        dump($response->getContent());
        $response->assertStatus(200)
            ->assertJsonPath('message', 'success')
            ->assertJsonStructure(['data' => ['url']]);

        Storage::disk('public')->assertExists('articles/' . $file->hashName());
    }

}
