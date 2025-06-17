<?php

namespace App\Http\Controllers;

use App\Events\AcceptedRescheduleEvent;
use App\Events\RejectedRescheduleEvent;
use App\Events\RequestRescheduleEvent;
use App\Http\Resources\RescheduleRequestResource;
use App\Models\ListBooking;
use App\Models\RequestReschedule;
use App\Http\Requests\StoreRequestRescheduleRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RequestRescheduleController extends Controller
{
    /**
     * Get All of Reschedule Request
     */
    public function index()
    {
        // Gunakan query yang sudah diperbaiki
        $rescheduleRequests = RequestReschedule::orWhereHas('oldListBooking', function ($query) {
            $query->where('date', '>=', now())
                ->where('status_request', 'Reschedule Request');
        })->latest()->get();

        if ($rescheduleRequests->isEmpty()) {
            return response([
                'message' => 'Tidak Ada Request Reschedule',
                'data' => []
            ]);
        }
        return response([
            'message' => 'List Request Reschedule',
            'data' => RescheduleRequestResource::collection($rescheduleRequests)
        ]);
    }

    /**
     * Add Request for Reschedule
     */
    public function addRequest(ListBooking $listBooking, StoreRequestRescheduleRequest $request)
    {
        $data = $request->validated();
        $booking = $listBooking->booking->id;
        // Checking not double request
        if ($listBooking->status_request != "Cancel Rejected" && $listBooking->status_request != "Reschedule Rejected" && $listBooking->status_request != null) {
            return response([
                'message' => "Bad Request",
                "error" => "Tidak bisa mengajukan jadwal ulang, permintaan sudah diajukan"
            ], 400);
        }

        // Checking if schedule not less than 3 days from now
        $dayDifference = now()->diffInDays($listBooking->date, false);  // hasil negatif jika sudah lewat
        if ($dayDifference < 3) {
            return response([
                'message' => "Bad Request",
                "error" => "Tidak bisa mengajukan jadwal ulang, batas waktu pengajuan pembatalan adalah 3 hari sebelum tanggal booking"
            ], 400);
        }

        // Checking if price of new schedule not higher than old schedule
        if ($data['new_schedule_price'] > $listBooking->price){
            return response([
                'message' => 'Bad Request',
                'errors' => 'Tidak bisa reschedule untuk jadwal yang memiliki harga lebih tinggi'
            ], 400);
        }

        // Checking Schedules in Database (Can't Booking if Other User Booked same schedule)
        $new_schedules = ListBooking::where('date', $data['new_schedule_date'])
            ->where('session', $data['new_schedule_time'])
            ->where(function ($query) {
                $query->whereNull('status_request')
                    ->orWhere('status_request', 'Canceled');
            })
            ->first();
        if ($new_schedules){
            return response([
                'message' => 'Bad Request',
                'errors' => 'Jadwal sudah dibooking oleh orang lain'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $user = auth()->user();
            $new_schedules = ListBooking::create([
                'date' => Carbon::parse($data['new_schedule_date']),
                'session' => $data['new_schedule_time'],
                'price' => $data['new_schedule_price'],
                'field_id' => $listBooking->field_id,
                'booking_id' => $booking,
                'status_request' => 'Reschedule Request'
            ]);
            RequestReschedule::create([
                'old_list_booking_id' => $listBooking->id,
                'user_id' => $user->id,
                'new_list_booking_id' => $new_schedules->id,
            ]);

            $listBooking->update([
                'status_request' => 'Reschedule Request',
            ]);

            DB::commit();
            // Send Notification to Admin
            event(new RequestRescheduleEvent($listBooking));

            return response([
                'message' => "Berhasil Mengajukan Reschedule, Silahkan Menunggu Persetujuan Admin",
            ]);
        } catch (\Exception $e){
            DB::rollBack();
            dump($e);
            return response([
                'message' => 'Internal Server Error',
                'errors' => 'Terjadi Kesalahan Di Server'
            ], 500);
        }
    }

    /**
     * Accept Request for Rescheduling
     */
    public function acceptRequest(RequestReschedule $requestReschedule)
    {
        DB::beginTransaction();
        try {
            $newListBooking = $requestReschedule->newListBooking;
            $oldListBooking = $requestReschedule->oldListBooking;

            if (!$newListBooking || !$oldListBooking) {
                return response([
                    'message' => 'Bad Request',
                    'errors' => 'Related ListBooking records not found'
                ], 400);
            }

            if ($newListBooking->price < $oldListBooking->price){
                $user = $requestReschedule->user;
                $user->recentTransactions()->create([
                    'user_id' => $user->id,
                    'wallet_id' => $user->wallet->id,
                    'transaction_type' => 'booking',
                    'amount' => $oldListBooking->price - $newListBooking->price,
                    'bank_ewallet' => null,
                    'number' => null,
                ]);
                $user->wallet()->update([
                    'balance' => $user->wallet->balance + ($oldListBooking->price - $newListBooking->price)
                ]);
            }

            $newListBooking->update([
                'status_request' => 'Reschedule'
            ]);
            $oldListBooking->update([
                'status_request' => 'Canceled'
            ]);
            DB::commit();
            event(new AcceptedRescheduleEvent($newListBooking));
            return response([
                'message' => 'Request Reschedule Accepted',
                'data' => new RescheduleRequestResource($requestReschedule)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => 'Internal Server Error',
                'errors' => 'Terjadi Kesalahan Di Server'
            ], 500);
        }
    }

    /**
     * Reject Request for Rescheduling
     */
    public function rejectRequest(RequestReschedule $requestReschedule)
    {
        DB::beginTransaction();
        try {
            // Handle related models before deleting the main model
            $newListBooking = $requestReschedule->newListBooking;
            $oldListBooking = $requestReschedule->oldListBooking;

            if ($newListBooking) {
                $newListBooking->delete();
            }

            if ($oldListBooking) {
                $oldListBooking->update([
                    'status_request' => 'Reschedule Rejected'
                ]);
            }

            // Delete the main request reschedule record
            $requestReschedule->delete();

            DB::commit();
            // Send Notification to User
            event(new RejectedRescheduleEvent($oldListBooking));
            return response([
                'message' => 'Request Reschedule Rejected',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => 'Internal Server Error',
                'errors' => 'Terjadi Kesalahan Di Server'
            ], 500);
        }
    }

}
