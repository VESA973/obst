<?php

namespace App\Support;

use App\Models\Event;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Lecture « front » d'un événement : type et commune déduits, créneaux horaires,
 * dates de début et de fin, export iCalendar et Google Agenda. Aucune écriture en base.
 */
class AgendaEvent
{
    public const TIMEZONE = 'America/Cayenne';

    public function __construct(public readonly Event $event) {}

    /**
     * @return array{key: string, label: string, color: string}
     */
    public function type(): array
    {
        $haystack = Str::of($this->event->title)->ascii()->lower()->toString();

        foreach (config('agenda.types') as $key => $type) {
            foreach ($type['keywords'] as $keyword) {
                if (str_contains($haystack, $keyword)) {
                    return ['key' => $key, 'label' => $type['label'], 'color' => $type['color']];
                }
            }
        }

        return ['key' => 'autre', ...config('agenda.default_type')];
    }

    /**
     * Commune de Guyane citée dans le lieu (la plus longue correspondance l'emporte).
     */
    public function commune(): ?string
    {
        if (blank($this->event->location)) {
            return null;
        }

        $location = Str::of($this->event->location)->ascii()->lower()->toString();

        return collect(config('agenda.communes'))->keys()
            ->sortByDesc(fn (string $name) => mb_strlen($name))
            ->first(fn (string $name) => str_contains($location, Str::of($name)->ascii()->lower()->toString()));
    }

    /**
     * @return array{0: float, 1: float}|null Position approximative de la commune.
     */
    public function coordinates(): ?array
    {
        return ($commune = $this->commune()) ? config("agenda.communes.{$commune}") : null;
    }

    /**
     * Créneaux horaires triés : [['label', 'date' (Carbon), 'start', 'end'], …].
     *
     * @return Collection<int, array{label: ?string, date: Carbon, start: ?string, end: ?string}>
     */
    public function slots(): Collection
    {
        $slots = collect($this->event->schedule_items ?? [])
            ->filter(fn (array $slot) => filled($slot['date'] ?? null))
            ->map(fn (array $slot) => [
                'label' => $slot['label'] ?? null,
                'date' => Carbon::parse($slot['date'], self::TIMEZONE),
                'start' => $slot['start_time'] ?? null,
                'end' => $slot['end_time'] ?? null,
            ])
            ->sortBy(fn (array $slot) => $slot['date']->format('Y-m-d').($slot['start'] ?? ''))
            ->values();

        if ($slots->isEmpty() && $this->event->event_date) {
            $slots->push(['label' => null, 'date' => Carbon::parse($this->event->event_date->format('Y-m-d'), self::TIMEZONE), 'start' => null, 'end' => null]);
        }

        return $slots;
    }

    /**
     * Début de l'événement (premier créneau), dans le fuseau de Cayenne.
     */
    public function startsAt(): ?Carbon
    {
        $first = $this->slots()->first();

        return $first ? $first['date']->copy()->setTimeFromTimeString($first['start'] ?? config('agenda.default_start_time')) : null;
    }

    /**
     * Fin de l'événement (dernier créneau ; 2 h après le début si aucune heure de fin).
     */
    public function endsAt(): ?Carbon
    {
        $last = $this->slots()->last();

        if (! $last) {
            return null;
        }

        return filled($last['end'])
            ? $last['date']->copy()->setTimeFromTimeString($last['end'])
            : $last['date']->copy()->setTimeFromTimeString($last['start'] ?? config('agenda.default_start_time'))->addHours(2);
    }

    public function isPast(): bool
    {
        return ($end = $this->endsAt()) !== null && $end->isPast();
    }

    /**
     * Heure au format français courant : « 9 h », « 18 h 30 ».
     */
    public static function formatTime(?string $time): ?string
    {
        if (blank($time)) {
            return null;
        }

        [$hours, $minutes] = array_map('intval', explode(':', $time) + [1 => 0]);

        return $hours.' h'.($minutes ? ' '.str_pad((string) $minutes, 2, '0', STR_PAD_LEFT) : '');
    }

    /**
     * Plage horaire d'un créneau (« 9 h – 12 h »), ou null sans heure.
     *
     * @param  array{start: ?string, end: ?string}|null  $slot  Premier créneau par défaut.
     */
    public function timeRange(?array $slot = null): ?string
    {
        $slot ??= $this->slots()->first();

        if (! $slot || blank($slot['start'])) {
            return null;
        }

        return self::formatTime($slot['start']).(filled($slot['end']) ? ' – '.self::formatTime($slot['end']) : '');
    }

    /**
     * Places restantes (null si pas de capacité).
     */
    public function remainingPlaces(): ?int
    {
        if (! $this->event->registration_capacity) {
            return null;
        }

        return max(0, $this->event->registration_capacity - ($this->event->registrations_count ?? $this->event->registrations()->count()));
    }

    public function excerpt(int $limit = 160): string
    {
        return Str::limit(Str::squish((string) $this->event->description), $limit);
    }

    /**
     * Paragraphes de la description (séparés par une ligne vide).
     *
     * @return list<string>
     */
    public function paragraphs(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R\s*\R/', (string) $this->event->description) ?: [])));
    }

    public function imageUrl(): ?string
    {
        return $this->event->image_path && $this->event->flyer_is_image
            ? Storage::disk('public')->url($this->event->image_path)
            : null;
    }

    /**
     * Contenu iCalendar (.ics) de l'événement, en heure de Cayenne convertie en UTC.
     */
    public function ics(): string
    {
        $utc = fn (Carbon $date) => $date->copy()->utc()->format('Ymd\THis\Z');
        $escape = fn (?string $text) => str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\;', '\,', '\n', '\n'], (string) $text);

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//La Quinzaine Obstetricale//Agenda//FR',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:event-'.$this->event->id.'@'.parse_url(config('app.url'), PHP_URL_HOST),
            'DTSTAMP:'.$utc(now()),
            'DTSTART:'.$utc($this->startsAt()),
            'DTEND:'.$utc($this->endsAt()),
            'SUMMARY:'.$escape($this->event->title),
            'DESCRIPTION:'.$escape($this->excerpt(500)."\n\n".route('agenda.show', $this->event)),
            'LOCATION:'.$escape($this->event->location),
            'URL:'.route('agenda.show', $this->event),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        // Lignes de 75 octets maximum (RFC 5545) sans couper un caractère UTF-8, séparées par CRLF.
        return collect($lines)->map(function (string $line): string {
            $chunks = [''];
            foreach (mb_str_split($line) as $char) {
                // 74 octets : la ligne de continuation commence par une espace.
                if (strlen(end($chunks).$char) > 74) {
                    $chunks[] = '';
                }
                $chunks[array_key_last($chunks)] .= $char;
            }

            return implode("\r\n ", $chunks);
        })->implode("\r\n")."\r\n";
    }

    public function googleCalendarUrl(): string
    {
        $utc = fn (Carbon $date) => $date->copy()->utc()->format('Ymd\THis\Z');

        return 'https://calendar.google.com/calendar/render?'.http_build_query([
            'action' => 'TEMPLATE',
            'text' => $this->event->title,
            'dates' => $utc($this->startsAt()).'/'.$utc($this->endsAt()),
            'details' => $this->excerpt(300)."\n".route('agenda.show', $this->event),
            'location' => $this->event->location,
            'ctz' => self::TIMEZONE,
        ]);
    }
}
