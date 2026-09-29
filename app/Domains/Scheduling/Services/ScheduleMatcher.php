<?php

namespace App\Domains\Scheduling\Services;

use Illuminate\Support\Carbon;

class ScheduleMatcher
{
    public function match(array $studentTimes, array $tutorTimes, array $bookedTimes, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $sharedTimes = [];

        foreach ($studentTimes as $studentTime) {
            foreach ($tutorTimes as $tutorTime) {
                $start = $studentTime->start_datetime->greaterThan($tutorTime->start_datetime)
                    ? $studentTime->start_datetime->copy()
                    : $tutorTime->start_datetime->copy();
                $end = $studentTime->end_datetime->lessThan($tutorTime->end_datetime)
                    ? $studentTime->end_datetime->copy()
                    : $tutorTime->end_datetime->copy();
                $start = $start->greaterThan($rangeStart) ? $start : $rangeStart->copy();
                $end = $end->lessThan($rangeEnd) ? $end : $rangeEnd->copy();

                if ($start->lessThan($end)) {
                    $sharedTimes[] = [
                        'start' => $start,
                        'end' => $end,
                    ];
                }
            }
        }

        foreach ($bookedTimes as $appointment) {
            $bookedStart = $appointment->appointment_datetime;
            $bookedEnd = $bookedStart->copy()->addHour();
            $remainingTimes = [];

            foreach ($sharedTimes as $time) {
                if ($bookedStart->greaterThanOrEqualTo($time['end']) || $bookedEnd->lessThanOrEqualTo($time['start'])) {
                    $remainingTimes[] = $time;

                    continue;
                }

                if ($bookedStart->greaterThan($time['start'])) {
                    $remainingTimes[] = [
                        'start' => $time['start'],
                        'end' => $bookedStart->copy(),
                    ];
                }

                if ($bookedEnd->lessThan($time['end'])) {
                    $remainingTimes[] = [
                        'start' => $bookedEnd->copy(),
                        'end' => $time['end'],
                    ];
                }
            }

            $sharedTimes = $remainingTimes;
        }

        return array_map(fn (array $time): array => [
            'start' => $time['start']->format('Y-m-d H:i'),
            'end' => $time['end']->format('Y-m-d H:i'),
        ], $sharedTimes);
    }
}
