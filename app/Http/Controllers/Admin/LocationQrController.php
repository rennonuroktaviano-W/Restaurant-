<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Models\Room;
use App\Services\LocationTokenService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LocationQrController extends Controller
{
    public function __construct(protected LocationTokenService $tokens) {}

    public function table(DiningTable $table): View
    {
        Gate::authorize('location.manage');

        $url = $this->qrUrl(LocationTokenService::TYPE_TABLE, $table->id);

        return view('admin.locations.table-qr', compact('table', 'url') + [
            'qrDataUri' => $this->qrDataUri($url),
        ]);
    }

    public function room(Room $room): View
    {
        Gate::authorize('location.manage');

        $url = $this->qrUrl(LocationTokenService::TYPE_ROOM, $room->id);

        return view('admin.locations.room-qr', compact('room', 'url') + [
            'qrDataUri' => $this->qrDataUri($url),
        ]);
    }

    private function qrUrl(string $type, int $id): string
    {
        return route('menu.index', ['location_token' => $this->tokens->issue($type, $id)]);
    }

    private function qrDataUri(string $url): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle(320), new SvgImageBackEnd));

        return 'data:image/svg+xml;base64,'.base64_encode($writer->writeString($url));
    }
}
