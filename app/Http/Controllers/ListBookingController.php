<?php

namespace App\Http\Controllers;

use App\Http\Requests\MyBookingQuery;
use App\Http\Resources\ListBookingResource;
use App\Http\Resources\ListBookingSparingResource;
use App\Models\ListBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ListBookingController extends Controller
{
    /**
     * Get My Booking
     *
     * Get History My Booking
     * @param MyBookingQuery $request
     * @return \Illuminate\Http\Response
     */
    public function index(MyBookingQuery $request)
    {
        $data = $request->validated();
        $user = auth()->user();
        if (!isset($data['sparing'])){
            $data['sparing'] = false;
        }
        if (!isset($data['past'])){
            $data['past'] = false;
        }
        if ($data['past']){
            $bookings = ListBooking::whereDate('date', '<', now())
                ->whereRelation('booking', 'rented_by', $user->id)
                ->get();
            return response([
                'message' => 'Booking List Past',
                'data' => [
                    'bookings' => ListBookingResource::collection($bookings),
                ]
            ]);
        }
        if (!$data['sparing']){
            $bookings = ListBooking::whereDate('date', '>=', now())
                ->whereDoesntHave('sparing')
                ->whereRelation('booking', 'rented_by', $user->id)
                ->get();
            Log::info('Bookings:', $bookings->toArray());
            return response([
                'message' => 'Booking List',
                'data' => [
                    'bookings' => ListBookingResource::collection($bookings),
                ]
            ]);
        }
        $bookings = ListBooking::whereDate('date', '>=', now())->whereHas('sparing', function ($query) use ($user){
            $query->where('created_by', $user->id)
                ->orWhereHas('sparingRequest', function ($query) use ($user){
                    $query->where('user_id', $user->id);
                });
        })->get();


        return response([
            'message' => 'Booking List',
            'data' => [
                'bookings' => ListBookingSparingResource::collection($bookings),
            ]
        ]);
    }


}
