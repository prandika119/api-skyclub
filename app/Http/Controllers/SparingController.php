<?php

namespace App\Http\Controllers;

use App\Events\AcceptedSparingEvent;
use App\Events\RejectedSparingEvent;
use App\Events\RequestSparingEvent;
use App\Http\Resources\SparingResource;
use App\Models\Sparing;
use App\Http\Requests\StoreSparingRequest;
use App\Http\Requests\UpdateSparingRequest;
use App\Models\SparingRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SparingController extends Controller
{
    /**
     * Get All of Sparing
     *
     * Get All of Sparing in Sparing Page
     */
    public function index()
    {
        $sparings = Sparing::with('createdBy')->latest()->where('status', '!=', 'done')->get();
        if ($sparings->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada sparing yang tersedia',
                'data' => []
            ], 200);
        }
        return response()->json([
            'message' => 'Sukses',
            'data' => SparingResource::collection($sparings)
        ], 200);
    }

    /**
     * Create Sparing
     *
     * Store a sparing
     */
    public function store(StoreSparingRequest $request)
    {
        /**
         * @var User $user
         */
        $user = auth()->user();
        // Check if the user has a team

        if (!$user->team){
            return response()->json([
                'message' => 'Bad Request',
                'errors' => 'Silahkan isi team terlebih dahulu'
            ], 400);
        }

        $data = $request->validated();
        $sparing = Sparing::create([
            'created_by' => $user->id,
            'description' => $data['description'],
            'list_booking_id' => $request['list_booking_id'],
            'status_sparing' => 'waiting'
        ]);
        return response()->json([
            'message' => 'Sparing Berhasil Dibuat',
        ], 201);

    }

    /**
     * Add a sparing request
     */
    public function addSparingRequest(Sparing $sparing){
        /**
         * @var User $user
         */
        $user = auth()->user();
        if (!$user->team){
            return response()->json([
                'message' => 'Bad Request',
                'errors' => 'Silahkan isi team terlebih dahulu'
            ], 400);
        }

        // Check if the sparing is already done
        if ($sparing->status == 'done') {
            return response()->json([
                'message' => 'Bad Request',
                'errors' => 'Sparing sudah ditutup'
            ], 400);
        }

        // Check if user is not the owner of the sparing
        if ($sparing->created_by == $user->id) {
            return response()->json([
                'message' => 'Bad Request',
                'errors' => 'Anda tidak bisa meminta sparing milik sendiri'
            ], 400);
        }

        // Check if the sparing is already requested
        $sparingRequest = SparingRequest::where('user_id', $user->id)->where('sparing_id', $sparing->id)->first();
        if ($sparingRequest) {
            return response()->json([
                'message' => 'Bad Request',
                'errors' => 'Sparing sudah diminta'
            ], 400);
        }

//        $sparingReq = DB::table('sparing_requests')->insert([
//            'user_id' => $user->id,
//            'sparing_id' => $sparing->id,
//            'status' => 'waiting'
//        ]);
        $sparingReq = SparingRequest::create([
            'user_id' => $user->id,
            'sparing_id' => $sparing->id,
            'status' => 'waiting'
        ]);
        event(new RequestSparingEvent($sparingReq));
        return response()->json([
            'message' => 'Sukses',
        ], 200);
    }

    /**
     * Accept a sparing request
     */
    public function acceptSparingRequest(SparingRequest $sparingRequest)
    {
        /**
         * @var User $user
         */
        $user = auth()->user();
        $sparing = Sparing::find($sparingRequest->sparing_id);
        // Logic to accept a sparing request
        // Check if the user is the owner of the sparing
        if ($user->id != $sparing->created_by){
            return response()->json([
                'message' => 'Bad Request',
                'errors' => 'Anda tidak bisa menerima sparing milik orang lain'
            ], 400);
        }

        // Check if the sparing request exists
        if (!$sparingRequest) {
            return response()->json([
                'message' => 'Bad Request',
                'errors' => 'Sparing request tidak ditemukan'
            ], 400);
        }
        // Check if the sparing is already done
        if ($sparing->status == 'done') {
            return response()->json([
                'message' => 'Bad Request',
                'errors' => 'Sparing sudah ditutup'
            ], 400);
        }

        // Update the sparing status to 'done'
        $sparingRequest->update(['status' => 'accepted']);

        // Update the all request sparing to 'rejected'
        $allRequests = SparingRequest::where('sparing_id', $sparing->id)->where('status', 'waiting')->get();
        foreach ($allRequests as $request) {
            $request->update(['status' => 'rejected']);
            event(new RejectedSparingEvent($request));
        }
        $sparing->update(['status' => 'done']);

        // Notify the user who requested the sparing
        event(new AcceptedSparingEvent($sparingRequest));

        // Return a success response
        return response()->json([
            'message' => 'Sukses'
        ], 200);
    }

    /**
     * Reject a sparing request
     */
    public function rejectSparingRequest(SparingRequest $sparingRequest)
    {
        /**
         * @var User $user
         */
        $user = auth()->user();
        $sparing = Sparing::find($sparingRequest->sparing_id);
        // Logic to reject a sparing request
        // Check if the user is the owner of the sparing
        if ($user->id != $sparing->created_by){
            return response()->json([
                'message' => 'Bad Request',
                'errors' => 'Anda tidak bisa menolak sparing milik orang lain'
            ], 400);
        }

        // Check if the sparing request exists
        if (!$sparingRequest) {
            return response()->json([
                'message' => 'Bad Request',
                'errors' => 'Sparing request tidak ditemukan'
            ], 400);
        }

        // Check if the sparing is already done
        if ($sparing->status == 'done') {
            return response()->json([
                'message' => 'Bad Request',
                'errors' => 'Sparing sudah ditutup'
            ], 400);
        }

        // Update the sparing status to 'done'
        $sparingRequest->update(['status' => 'rejected']);
        event(new RejectedSparingEvent($sparingRequest));
        // Return a success response
        return response()->json([
            'message' => 'Sukses'
        ], 200);
    }

}
