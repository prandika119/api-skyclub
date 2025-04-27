<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with(['user'])->get();
        return response([
            'message' => 'Success',
            'data' => ReviewResource::collection($reviews)
        ], 200);
    }
    public function store(StoreReviewRequest $request)
    {
        $data = $request->validated();
        if ($data)
        $user = auth()->user();
        Review::create([
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'user_id' => $user->id,
            'booking_id' => $data['booking_id'],
        ]);
        return response()->json([
            'message' => 'Review created successfully'
        ], 201);
    }
}
