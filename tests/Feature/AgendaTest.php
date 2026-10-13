<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Support\AgendaEvent;
use Database\Seeders\AgendaExampleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Agenda : liste, filtres (données), fiche, ajout à l'agenda, SEO et exemples.
 */
class AgendaTest extends TestCase
{
    use RefreshDatabase;

    private function event(array $attributes = [], string $date = '+10 days', ?string $start = '09:00', ?string $end = '12:00'): Event
    {
        $day = now('America/Cayenne')->modify($date)->format('Y-m-d');

        return Event::create([
            'title' => 'Atelier contraception',
            'location' => 'Salle municipale, Kourou',
            'description' => "Premier paragraphe.\n\nSecond paragraphe.",
            'event_date' => $day,
            'schedule_items' => [['label' => null, 'date' => $day, 'start_time' => $start, 'end_time' => $end]],
            'is_published' => true,
            ...$attributes,
        ]);
    }

    public function test_type_commune_and_times_are_derived_on_the_front(): void
    {
        $item = new AgendaEvent($this->event(['title' => 'Conférence-débat sur l\'endométriose', 'location' => 'Amphithéâtre, Saint-Laurent-du-Maroni']));

        $this->assertSame('conference', $item->type()['key']);
        $this->assertSame('Saint-Laurent-du-Maroni', $item->commune());
        $this->assertSame('9 h – 12 h', $item->timeRange());
        $this->assertSame('09:00', $item->startsAt()->format('H:i'));
        $this->assertSame('America/Cayenne', $item->startsAt()->timezoneName);
        $this->assertSame('autre', (new AgendaEvent($this->event(['title' => 'Assemblée générale'])))->type()['key']);
    }

    public function test_list_shows_upcoming_grouped_by_month_and_past_events_apart(): void
    {
        $next = $this->event(['title' => 'Atelier à venir']);
        $this->event(['title' => 'Permanence passée'], '-20 days');
        $this->event(['title' => 'Brouillon', 'is_published' => false]);

        $this->get(route('agenda'))
            ->assertOk()
            ->assertSee('Prochain événement')
            ->assertSee('data-countdown', false)
            // Ancres des QR codes imprimés conservées.
            ->assertSee('id="event-'.$next->id.'"', false)
            ->assertSee(route('agenda.show', $next))
            ->assertSee('data-type="atelier"', false)
            ->assertSee('data-commune="Kourou"', false)
            ->assertSeeInOrder(['Les prochains événements', 'Atelier à venir', 'Événements passés', 'Permanence passée'])
            ->assertSee('data-agenda-calendar', false)
            ->assertDontSee('Brouillon');
    }

    public function test_empty_agenda_shows_a_friendly_message(): void
    {
        $this->get(route('agenda'))
            ->assertOk()
            ->assertSee('Aucun événement n\'est programmé pour le moment.', false);
    }

    public function test_detail_page_has_practical_info_registration_calendar_share_map_and_seo(): void
    {
        $event = $this->event(['registration_capacity' => 20]);
        $other = $this->event(['title' => 'Journée dengue', 'location' => 'Place du bourg, Macouria'], '+30 days');

        $this->get(route('agenda.show', $event))
            ->assertOk()
            ->assertSeeInOrder(['Accueil', 'Agenda', 'Atelier contraception'])
            ->assertSee('Informations pratiques')
            ->assertSee('Gratuite')
            ->assertSee('20 places restantes')
            ->assertSee('Premier paragraphe.')
            ->assertSee('Second paragraphe.')
            ->assertSee('action="'.route('events.register', $event).'"', false)
            ->assertSee(route('agenda.ics', $event))
            ->assertSee('https://calendar.google.com/calendar/render?', false)
            ->assertSeeInOrder(['WhatsApp', 'Facebook', 'Copier le lien'])
            ->assertSee('data-lat="5.16"', false)
            ->assertSee('Position approximative de la commune de Kourou')
            ->assertSee('"@type":"Event"', false)
            ->assertSee('"addressLocality":"Kourou"', false)
            ->assertSee('Autres événements à venir')
            ->assertSee(route('agenda.show', $other));
    }

    public function test_registration_still_works_from_the_detail_page(): void
    {
        $event = $this->event();

        $this->from(route('agenda.show', $event))
            ->post(route('events.register', $event), ['name' => 'Marie', 'email' => 'marie@example.com'])
            ->assertRedirect(route('agenda.show', $event));

        $this->assertDatabaseHas('event_registrations', ['event_id' => $event->id, 'email' => 'marie@example.com']);
    }

    public function test_free_entry_event_hides_registration_and_refuses_sign_ups(): void
    {
        $event = $this->event(['is_free_entry' => true, 'registration_capacity' => 20, 'image_path' => 'events/flyers/a.jpg', 'banner_position' => '50% 0%']);

        $this->get(route('agenda.show', $event))
            ->assertOk()
            ->assertSee('Entrée libre')
            ->assertSee('object-position: 50% 0%', false)
            ->assertDontSee('Je m\'inscris', false);

        $this->post(route('events.register', $event), ['name' => 'Marie', 'email' => 'marie@example.com'])->assertStatus(422);
        $this->assertDatabaseCount('event_registrations', 0);
    }

    public function test_past_event_hides_registration_and_calendar_links(): void
    {
        $event = $this->event([], '-5 days');

        $this->get(route('agenda.show', $event))
            ->assertOk()
            ->assertSee('Événement passé')
            ->assertDontSee('Je m\'inscris', false)
            ->assertDontSee(route('agenda.ics', $event));
    }

    public function test_unpublished_event_is_not_reachable(): void
    {
        $event = $this->event(['is_published' => false]);

        $this->get(route('agenda.show', $event))->assertNotFound();
        $this->get(route('agenda.ics', $event))->assertNotFound();
    }

    public function test_ics_file_uses_utc_and_folds_long_lines(): void
    {
        $event = $this->event(['title' => 'Atelier avec un titre très long pour vérifier le découpage des lignes du fichier iCalendar']);
        $day = $event->event_date->format('Ymd');

        $response = $this->get(route('agenda.ics', $event))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/calendar; charset=UTF-8');

        $ics = $response->getContent();
        // 9 h à Cayenne (UTC−3) = 12 h UTC.
        $this->assertStringContainsString("DTSTART:{$day}T120000Z", $ics);
        $this->assertStringContainsString("DTEND:{$day}T150000Z", $ics);
        $this->assertStringContainsString('LOCATION:Salle municipale\, Kourou', $ics);

        foreach (explode("\r\n", $ics) as $line) {
            $this->assertLessThanOrEqual(75, strlen($line));
        }
    }

    public function test_example_seeder_is_idempotent_and_marks_events(): void
    {
        Http::fake(['picsum.photos/*' => Http::response('', 404)]);

        $this->seed(AgendaExampleSeeder::class);
        $count = Event::count();
        $this->seed(AgendaExampleSeeder::class);

        $this->assertSame($count, Event::count());
        $this->assertGreaterThanOrEqual(12, $count);
        $this->assertSame($count, Event::where('title', 'like', '[Exemple]%')->count());
        $this->assertGreaterThanOrEqual(2, Event::all()->filter(fn (Event $event) => (new AgendaEvent($event))->isPast())->count());
    }
}
