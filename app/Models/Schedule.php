<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class Schedule
{
    public string $date;
    public array $time_slots;
    public int $field;
    public int $price;
    public static Carbon $now;
    public static Carbon $startOfWeek;
    public static Carbon $endDate;
    public static Carbon $endOfWeek;
    public static int $daysCount;
    public static array $schedules = [];

    public function __construct(string $date, Field $field)
    {
        $this->date = $date;
        $this->time_slots = array_fill(0, 24, true);;
        $this->field = $field->id;
        $this->price = $field->weekday_price;
    }

    public static function initialize(): void
    {
        self::$now = Carbon::now('Asia/Jakarta');
        self::$startOfWeek = self::$now->copy()->startOfWeek();
        self::$endOfWeek = self::$now->copy()->endOfWeek();
        //self::$daysCount = self::$startOfWeek->diffInDays(self::$endOfWeek);
    }
    //Generate Schedule 2 Months a go
    public static function generateSchedule(Field $field, ?string $startDate = null, ?string $endDate = null): Collection
    {
        //dump('Start Date: ' . $startDate);
        // Inisialisasi default hanya jika start/end kosong
        if (!$startDate || !$endDate) {
            self::initialize();
            $start = $startDate ?: self::$now->copy()->startOfWeek();
            $end = $endDate ?: self::$now->copy()->endOfWeek();
        } else {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
        }

        self::$schedules = []; // Reset
        $daysCount = $start->diffInDays($end);

        $schedules_booked = ListBooking::whereBetween('date', [
            $start->toDateString(), $end->toDateString()
        ])
            ->where('field_id', $field->id)
            ->where(function ($subQuery) {
                $subQuery->where('status', '!=', 'canceled')

                    ->orWhere('status', null);
            })
            ->whereHas('booking', function ($query) {
                $query->where('status', '!=', 'canceled');
            })
            ->get();

        for ($i = 0; $i <= $daysCount; $i++) {
            $date = $start->copy()->addDays($i);
            self::$schedules[$i] = new Schedule($date->format('d-m-Y'), $field);

            // Cek harga weekend
            if ($date->isWeekend()) {
                self::$schedules[$i]->price = $field->weekend_price;
            }

            // Isi slot waktu
            for ($j = 0; $j < 24; $j++) {
                $timeLabel = $j . ':00 - ' . ($j + 1) . ':00';
                $booked = $schedules_booked->where('date', $date->format('Y-m-d'))
                    ->where('session', $timeLabel)
                    ->first();

                self::$schedules[$i]->time_slots[$j] = [
                    'time' => $timeLabel,
                    'is_available' => !$booked
                ];
            }
        }
        return collect(self::$schedules);

    }
}
