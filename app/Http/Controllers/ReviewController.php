<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\ListBooking;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Get All of Reviews
     *
     * Get All of Reviews
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reviews = Review::with(['user'])->get();
        return response([
            'message' => 'Success',
            'data' => ReviewResource::collection($reviews)
        ], 200);
    }

    /**
     * Add Review
     *
     * Add Review
     * @param Review $review
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreReviewRequest $request)
    {
        $data = $request->validated();

        $user = auth()->user();

        // chek if review already review
        if (Review::where('booking_id', $data['booking_id'])->exists()) {
            return response()->json([
                'message' => 'You have already reviewed this booking'
            ], 400);
        }

        Review::create([
            'rating' => $data['rating'],
            'field_id' => $data['field_id'],
            'comment' => $data['comment'],
            'user_id' => $user->id,
            'booking_id' => $data['booking_id'],
        ]);
        return response()->json([
            'message' => 'Review created successfully'
        ], 201);
    }
}
