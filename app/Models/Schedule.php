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
        self::$endDate = self::$now->copy()->addMonths(1);
        self::$endOfWeek = self::$endDate->copy()->endOfWeek();
        self::$daysCount = self::$startOfWeek->diffInDays(self::$endOfWeek);
    }
    //Generate Schedule 2 Months a go
    public static function generateSchedule(Field $field): Collection
    {
        // looping for create schedule 2 months a go
        self::initialize();
        for ($i = 0; $i < self::$daysCount+1; $i++) {
            $date = self::$startOfWeek->copy()->addDays($i);
            self::$schedules[$i] = new Schedule($date->format('d-m-Y'), $field);

            // check if date is weekend and then change price
            if ($date->isWeekend()) {
                self::$schedules[$i]->price = $field->weekend_price;
            }

            // format time slots
            for ($j = 0; $j < 24; $j++) {
                self::$schedules[$i]->time_slots[$j] = [
                    'time' => $j . ':00 - ' . ($j + 1) . ':00',
                    'is_available' => true
                ];
            }
        }
        return collect(self::$schedules)->chunk(7);
    }
}
