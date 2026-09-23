<?php

namespace App\Filament\Exports;

use App\Models\EventRegistration;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Export CSV des inscriptions (même format que l'ancienne administration).
 */
class EventRegistrationsCsv
{
    public static function download(?Builder $query = null, string $filename = 'inscriptions-evenements.csv'): StreamedResponse
    {
        $registrations = ($query ?? EventRegistration::query())->with('event')->latest()->get();

        return response()->streamDownload(function () use ($registrations): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Événement', 'Date', 'Nom', 'Email', 'Téléphone', 'Inscrit le']);

            foreach ($registrations as $registration) {
                fputcsv($output, [
                    $registration->event?->title,
                    $registration->event?->event_date?->format('Y-m-d'),
                    $registration->name,
                    $registration->email,
                    $registration->phone,
                    $registration->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
