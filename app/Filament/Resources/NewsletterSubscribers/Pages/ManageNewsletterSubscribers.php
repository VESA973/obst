<?php

namespace App\Filament\Resources\NewsletterSubscribers\Pages;

use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Models\NewsletterSubscriber;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageNewsletterSubscribers extends ManageRecords
{
    protected static string $resource = NewsletterSubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exporter en CSV')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn () => response()->streamDownload(function (): void {
                    $output = fopen('php://output', 'w');
                    fputcsv($output, ['Email', 'Inscrit le']);

                    NewsletterSubscriber::query()->orderBy('created_at')->each(
                        fn (NewsletterSubscriber $subscriber) => fputcsv($output, [$subscriber->email, $subscriber->created_at?->format('Y-m-d H:i:s')])
                    );

                    fclose($output);
                }, 'newsletter-abonnes.csv', ['Content-Type' => 'text/csv; charset=UTF-8'])),
        ];
    }
}
