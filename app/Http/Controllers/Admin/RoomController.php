<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoomRequest;
use App\Models\Area;
use App\Models\Room;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function __construct(protected AuditLogger $audit)
    {
        Gate::authorize('location.manage');
    }

    public function indexAll(Request $request): View
    {
        $rooms = Room::query()
            ->with('area')
            ->when($request->area, fn ($q, $a) => $q->where('area_id', $a))
            ->when($request->search, fn ($q, $s) => $q->where('room_number', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%"))
            ->orderBy('area_id')
            ->orderBy('room_number')
            ->paginate(25)
            ->withQueryString();

        $areas = Area::orderBy('name')->get();

        return view('admin.rooms.index', compact('rooms', 'areas'));
    }

    public function create(): View
    {
        $areas = Area::orderBy('name')->get();

        return view('admin.rooms.create', compact('areas'));
    }

    public function store(RoomRequest $request): RedirectResponse
    {
        $room = Room::create($request->validated());

        $this->audit->log('create', 'location', 'room', $room->id, [], $room->toArray());

        return redirect()->route('admin.rooms.index')->with('success', 'Room berhasil dibuat.');
    }

    public function edit(Room $room): View
    {
        $areas = Area::orderBy('name')->get();

        return view('admin.rooms.edit', compact('room', 'areas'));
    }

    public function update(RoomRequest $request, Room $room): RedirectResponse
    {
        $old = $room->toArray();

        $room->update($request->validated());

        $this->audit->log('update', 'location', 'room', $room->id, $old, $room->fresh()->toArray());

        return redirect()->route('admin.rooms.index')->with('success', 'Room berhasil diperbarui.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $room->delete();

        $this->audit->log('delete', 'location', 'room', $room->id, [], []);

        return redirect()->route('admin.rooms.index')->with('success', 'Room berhasil dihapus.');
    }
}
