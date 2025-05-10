<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Get data of the authenticated user
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
                "date_of_birth" => $user->date_of_birth,
                "profile_photo" => $user->profile_photo,
                "wallet" => $user->wallet->balance,
                "notif" => $notif
            ]
        ]);
    }

    /**
     * Update the authenticated user's data
     *
     * @param UpdateUserRequest $request
     * @return Response
     */
    public function update(UpdateUserRequest $request): Response
    {
        $data = $request->validated();
        $user = auth()->user();
        if ($user->profile_photo){
            Storage::disk('public')->delete('profile_photos/'.$user->profile_photo);
        }
        if (isset($data['profile_photo'])){
            $path = $data['profile_photo']->store('profile_photos', 'public');
            $data['profile_photo'] = $path;
            dump($data['profile_photo']);
        }
        $user->update($data);
        return response([
            "message" => "Success Update This User",
        ], 200);
    }

    public function getAllNotifications()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->paginate(10);
        return response()->json([
            'message' => 'Success get all notifications',
            'data' => $notifications
        ]);
    }

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
