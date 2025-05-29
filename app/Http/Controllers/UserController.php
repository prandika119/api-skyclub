<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Get user data
     *
     * @return JsonResponse
     */
    public function get (): JsonResponse{
        /* @var User $user */
        $user = auth()->user();
        // get count notification
        $notif = $user->unreadNotifications->count();
        return response()->json([
            "message" => "Success Get This User",
            "data" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "no_telp" => $user->no_telp,
                "team" => $user->team,
                "address" => $user->address,
                "role" => $user->role,
                "date_of_birth" => $user->date_of_birth,
                "profile_photo" => $user->profile_photo,
                "wallet" => $user->wallet->balance,
                "notif" => $notif
            ]
        ]);
    }

    /**
     * Update user's data
     *
     * Update the authenticated user's data
     *
     * @param UpdateUserRequest $request
     * @return Response
     */
    public function update(UpdateUserRequest $request): Response
    {
        Log::info('Files:', $request->allFiles());
        Log::info('Has File:', [$request->hasFile('profile_photo') ? 'Ya' : 'Tidak']);
        Log::info('All request:', $request->all());
        $data = $request->validated();
        Log::info($data);
        $user = auth()->user();
        if ($user->profile_photo){
            Storage::disk('public')->delete('profile_photos/'.$user->profile_photo);
        }
        if (isset($data['profile_photo'])){
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $data['profile_photo'] = $path;
            Log::info('Uploaded file path: ' . $path);
        }
        $user->update($data);
        return response([
            "message" => "Success Update This User",
        ], 200);
    }

    /**
     * Get all notifications
     *
     * @return JsonResponse
     */
    public function getAllNotifications()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->paginate(10);
        return response()->json([
            'message' => 'Success get all notifications',
            'data' => $notifications
        ]);
    }

    /**
     * Mark a notification as read
     *
     * @param string $id
     * @return JsonResponse
     */
    public function readNotification(string $id)
    {
        $user = auth()->user();
        $notification = $user->notifications()->find($id);
        if (!$notification) {
            return response()->json([
                'message' => 'Notification not found',
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
        ], 200);
    }
}
