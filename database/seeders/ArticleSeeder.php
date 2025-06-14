<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        Article::create([
            'title' => 'Article 1',
            'content' => json_encode([
                'text' => 'This is the content of article 1.',
                'images' => [
                    'articles/image1.jpg',
                ],
            ]),
            'author_id' => $admin->id,
        ]);
        Article::create([
            'title' => 'Article 2',
            'content' => json_encode([
                'text' => 'This is the content of article 2.',
                'images' => [
                    'http://localhost:8000/storage/articles/image2.jpg',
                ],
            ]),
            'author_id' => $admin->id,
        ]);
    }
}
