<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;


class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cart = Session::get('cart', []);
        return response([
            'message' => 'Success',
            'data' => $cart
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCartRequest $request)
    {
        $data = $request->validated();
        $cart = Session::get('cart', []);
        $schedules_cart = $cart['schedules'] ?? [];

        // Cek duplikat berdasarkan kombinasi field_id, schedule_date, dan schedule_time
        $duplicate = collect($schedules_cart)->contains(function ($item) use ($data) {
            return $item['field_id'] == $data['field_id'] &&
                $item['schedule_date'] == $data['schedule_date'] &&
                $item['schedule_time'] == $data['schedule_time'];
        });

        if ($duplicate) {
            return response([
                'message' => 'Bad Request',
                'errors' => 'Jadwal tersebut sudah ada dalam keranjang'
            ], 400);
        }

        $cart['schedules'][] = $data;
        $cart['total_price'] = array_sum(array_column($cart['schedules'], 'price'));
        Session::put('cart', $cart);
        return response([
            'message' => 'Cart added successfully',
            'data' => $cart
        ], 201);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cart = Session::get('cart', []);
        if (!isset($cart['schedules'][$id])) {
            return response(['message' => 'Bad Request', 'errors' => 'Item not found'], 404);
        }
        unset($cart['schedules'][$id]);
        $cart['total_price'] = array_sum(array_column($cart['schedules'], 'price'));
        Session::put('cart', $cart);
        return response([
            'message' => 'Cart deleted successfully',
            'data' => $cart
        ], 200);
    }
}
