<?php

namespace App\Http\Controllers;

use App\Http\Requests\PhotoArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::with('author:id,name,profile_photo')->latest()->paginate(10)->withQueryString();

        return response()->json([
            'message' => 'success',
            'data' => ArticleResource::collection($articles),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreArticleRequest $request)
    {
        $user = auth()->user();
        $validated = $request->validated();

        $article = Article::create([
            'title' => $validated['title'],
            'content' => json_encode($validated['content']),
            'author_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'success',
            'data' => new ArticleResource($article),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        // Check if the article is published
        if (!$article) {
            return response()->json([
                'message' => 'error',
                'errors' => 'Article not found',
            ], 404);
        }

        // Load the author relationship
        $article->load('author:id,name,profile_photo');

        return response()->json([
            'status' => 'success',
            'data' => new ArticleResource($article),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        // Check if the article is found
        if (!$article) {
            return response()->json([
                'message' => 'error',
                'errors' => 'Article not found',
            ], 404);
        }

        $data = $request->validated();
        $article->update([
            'title' => $data['title'],
            'content' => json_encode($data['content']),
        ]);
        return response()->json([
            'message' => 'success',
            'data' => new ArticleResource($article),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        // Check if the article is found
        if (!$article) {
            return response()->json([
                'message' => 'error',
                'errors' => 'Article not found',
            ], 404);
        }

        $article->delete();
        return response()->json([
            'message' => 'success',
            'data' => null,
        ]);
    }

    /**
     * Add a photo to the article.
     */
    public function addArticlePhoto(PhotoArticleRequest $request)
    {
        if ($request->hasFile('photos')){
            $data = $request->validated();

            $path = $data['photos']->store('articles', 'public');

            return response()->json([
                'message' => 'success',
                'data' => [
                    'url' => asset('storage/' . $path),
                ],
            ]);
        }
        return response()->json([
            'message' => 'error',
            'errors' => 'No file uploaded',
        ], 422);
    }
}
