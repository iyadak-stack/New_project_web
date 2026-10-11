<?php

namespace App\Domains\Scheduling\Http\Controllers;

use App\Domains\Booking\Models\Appointment;
use App\Domains\Scheduling\Http\Requests\StoreAvailabilityRequest;
use App\Domains\Scheduling\Http\Requests\UpdateAvailabilityRequest;
use App\Domains\Scheduling\Models\Availability;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AvailabilityController extends Controller
{
    public function index(): View
    {
        $availabilities = Availability::query()
            ->where('user_id', (string) Auth::id())
            ->orderBy('start_datetime')
            ->get();

        return view('domains.scheduling.availabilities.index', compact('availabilities'));
    }

    public function history(): View
    {
        $availabilities = Availability::query()
            ->where('user_id', (string) Auth::id())
            ->orderByDesc('updated_at')
            ->get();

        return view('domains.scheduling.availabilities.history', compact('availabilities'));
    }

    public function store(StoreAvailabilityRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $userId = (string) Auth::id();

        if ($this->hasOverlap($userId, $data['start_datetime'], $data['end_datetime'])) {
            return back()->withInput()->withErrors([
                'start_datetime' => 'ช่วงเวลานี้ทับกับเวลาว่างรายการอื่น',
            ]);
        }

        do {
            $availabilityId = Str::upper(Str::random(10));
        } while (Availability::query()->whereKey($availabilityId)->exists());

        Availability::create([
            'availability_id' => $availabilityId,
            'user_id' => $userId,
            'start_datetime' => $data['start_datetime'],
            'end_datetime' => $data['end_datetime'],
        ]);

        return redirect()->route('availabilities.index')->with('success', 'เพิ่มเวลาว่างแล้ว');
    }

    public function edit(Availability $availability): View
    {
        $this->ensureOwner($availability);

        return view('domains.scheduling.availabilities.edit', compact('availability'));
    }

    public function update(UpdateAvailabilityRequest $request, Availability $availability): RedirectResponse
    {
        $this->ensureOwner($availability);
        $this->ensureUnbooked($availability);
        $data = $request->validated();

        if ($this->hasOverlap((string) Auth::id(), $data['start_datetime'], $data['end_datetime'], $availability->availability_id)) {
            return back()->withInput()->withErrors([
                'start_datetime' => 'ช่วงเวลานี้ทับกับเวลาว่างรายการอื่น',
            ]);
        }

        $availability->update([
            'start_datetime' => $data['start_datetime'],
            'end_datetime' => $data['end_datetime'],
        ]);

        return redirect()->route('availabilities.index')->with('success', 'แก้ไขเวลาว่างแล้ว');
    }

    public function destroy(Availability $availability): RedirectResponse
    {
        $this->ensureOwner($availability);
        $this->ensureUnbooked($availability);
        $availability->delete();

        return redirect()->route('availabilities.index')->with('success', 'ลบเวลาว่างแล้ว');
    }

    // เวลาทับกันเมื่อเวลาเริ่มอยู่ก่อนเวลาจบของอีกช่วง และเวลาจบอยู่หลังเวลาเริ่ม
    private function hasOverlap(string $userId, string $start, string $end, ?string $exceptId = null): bool
    {
        $start = Carbon::parse($start)->format('Y-m-d H:i:s');
        $end = Carbon::parse($end)->format('Y-m-d H:i:s');
        $bookingOverlap = Appointment::whereHas('tutorProfile', fn ($query) => $query->where('Users_user_id', $userId))
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->exists();

        return $bookingOverlap || Availability::query()
            ->where('user_id', $userId)
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->when($exceptId, fn ($query) => $query->where('availability_id', '!=', $exceptId))
            ->exists();
    }

    // ห้ามแก้หรือลบเวลาที่นักเรียนจองแล้ว เพราะนัดหมายยังใช้เวลานี้อยู่
    private function ensureUnbooked(Availability $availability): void
    {
        $booked = Appointment::whereHas('tutorProfile', fn ($query) => $query->where('Users_user_id', $availability->user_id))
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_datetime', '<', $availability->end_datetime)
            ->where('end_datetime', '>', $availability->start_datetime)
            ->exists();
        if ($booked) {
            throw ValidationException::withMessages(['start_datetime' => 'ช่วงเวลานี้มีนัดหมายแล้ว กรุณาจัดการนัดหมายก่อนแก้ไขหรือลบ']);
        }
    }

    private function ensureOwner(Availability $availability): void
    {
        abort_unless((string) $availability->user_id === (string) Auth::id(), 403);
    }
}
