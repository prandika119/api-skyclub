<?php

namespace App\Http\Controllers;

use App\Http\Resources\FieldResource;
use App\Models\Field;
use App\Http\Requests\StoreFieldRequest;
use App\Http\Requests\UpdateFieldRequest;
use App\Models\Schedule;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Response;

class FieldController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFieldRequest $request): Response
    {
        $data = $request->validated();
        Field::create($data);
        return response([
            'message' => 'Field created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Field $field): Response
    {
        return response([
            'message' => 'Success',
            'data' => new FieldResource($field)
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFieldRequest $request, Field $field): Response
    {
        $data = $request->validated();
        $field->update($data);
        return response([
            'message' => 'Field updated successfully'
        ], 200);
    }

    public function getSchedules(Field $field): Response
    {
        $schedules = Schedule::generateSchedule($field);
        return response([
            'message' => 'Success',
            'data' => $schedules
        ]);
    }
}
