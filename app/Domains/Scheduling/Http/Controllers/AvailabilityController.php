<?php

namespace App\Domains\Scheduling\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Scheduling\Http\Requests\StoreAvailabilityRequest;
use App\Domains\Scheduling\Http\Requests\UpdateAvailabilityRequest;
use App\Domains\Scheduling\Models\Availability;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
        $availability->delete();

        return redirect()->route('availabilities.index')->with('success', 'ลบเวลาว่างแล้ว');
    }

    private function hasOverlap(string $userId, string $start, string $end, ?string $exceptId = null): bool
    {
        return Availability::query()
            ->where('user_id', $userId)
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->when($exceptId, fn ($query) => $query->where('availability_id', '!=', $exceptId))
            ->exists();
    }

    private function ensureOwner(Availability $availability): void
    {
        abort_unless((string) $availability->user_id === (string) Auth::id(), 403);
    }
}