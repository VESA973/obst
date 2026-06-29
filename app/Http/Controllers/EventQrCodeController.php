<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Response;

class EventQrCodeController extends Controller
{
    public function __invoke(Event $event): Response
    {
        abort_unless($event->is_published, 404);

        $targetUrl = $event->is_paid && $event->registration_url
            ? $event->registration_url
            : route('agenda').'#event-'.$event->id;

        $result = (new Builder(
            writer: new SvgWriter(),
            data: $targetUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 260,
            margin: 12,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        ))->build();

        return response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
