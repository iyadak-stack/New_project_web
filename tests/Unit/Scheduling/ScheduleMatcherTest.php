<?php

namespace Tests\Unit\Scheduling;

use App\Domains\Scheduling\Services\ScheduleMatcher;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class ScheduleMatcherTest extends TestCase
{
    public function test_it_returns_the_shared_time_clipped_to_the_requested_range(): void
    {
        $matcher = new ScheduleMatcher;

        $times = $matcher->match(
            [$this->availability('2026-09-30 14:00', '2026-09-30 17:00')],
            [$this->availability('2026-09-30 15:00', '2026-09-30 18:00')],
            [],
            Carbon::parse('2026-09-30 15:30'),
            Carbon::parse('2026-09-30 16:30'),
        );

        $this->assertSame([
            ['start' => '2026-09-30 15:30', 'end' => '2026-09-30 16:30'],
        ], $times);
    }

    public function test_it_returns_no_time_when_student_and_tutor_are_not_free_together(): void
    {
        $matcher = new ScheduleMatcher;

        $times = $matcher->match(
            [$this->availability('2026-09-30 14:00', '2026-09-30 15:00')],
            [$this->availability('2026-09-30 15:00', '2026-09-30 16:00')],
            [],
            Carbon::parse('2026-09-30 14:00'),
            Carbon::parse('2026-09-30 16:00'),
        );

        $this->assertSame([], $times);
    }

    public function test_it_removes_the_actual_booked_period_from_shared_time(): void
    {
        $matcher = new ScheduleMatcher;

        $times = $matcher->match(
            [$this->availability('2026-09-30 15:00', '2026-09-30 18:00')],
            [$this->availability('2026-09-30 15:00', '2026-09-30 18:00')],
            [$this->appointment('2026-09-30 16:00', '2026-09-30 16:30')],
            Carbon::parse('2026-09-30 15:00'),
            Carbon::parse('2026-09-30 18:00'),
        );

        $this->assertSame([
            ['start' => '2026-09-30 15:00', 'end' => '2026-09-30 16:00'],
            ['start' => '2026-09-30 16:30', 'end' => '2026-09-30 18:00'],
        ], $times);
    }

    public function test_it_splits_free_time_around_multiple_appointments(): void
    {
        $matcher = new ScheduleMatcher;

        $times = $matcher->match(
            [$this->availability('2026-09-30 15:00', '2026-09-30 20:00')],
            [$this->availability('2026-09-30 15:00', '2026-09-30 20:00')],
            [
                $this->appointment('2026-09-30 15:30', '2026-09-30 16:00'),
                $this->appointment('2026-09-30 18:00', '2026-09-30 19:00'),
            ],
            Carbon::parse('2026-09-30 15:00'),
            Carbon::parse('2026-09-30 20:00'),
        );

        $this->assertSame([
            ['start' => '2026-09-30 15:00', 'end' => '2026-09-30 15:30'],
            ['start' => '2026-09-30 16:00', 'end' => '2026-09-30 18:00'],
            ['start' => '2026-09-30 19:00', 'end' => '2026-09-30 20:00'],
        ], $times);
    }

    private function availability(string $start, string $end): object
    {
        return (object) [
            'start_datetime' => Carbon::parse($start),
            'end_datetime' => Carbon::parse($end),
        ];
    }

    private function appointment(string $start, string $end): object
    {
        return (object) [
            'start_datetime' => Carbon::parse($start),
            'end_datetime' => Carbon::parse($end),
        ];
    }
}
