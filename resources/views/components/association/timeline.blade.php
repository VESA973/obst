{{--
    Frise chronologique : verticale sur mobile, alternée gauche / droite sur ordinateur.
    $steps : [['year' => …, 'title' => …, 'text' => …], …] (config/association.php).
--}}
@props(['steps'])

@if (count($steps))
    <ol {{ $attributes->class('assoc-timeline') }}>
        @foreach ($steps as $step)
            <li class="assoc-timeline-step">
                <span class="assoc-timeline-dot" aria-hidden="true"></span>
                <div class="assoc-timeline-card">
                    <p class="assoc-timeline-year">{{ $step['year'] }}</p>
                    <h3>{{ $step['title'] }}</h3>
                    @if (! empty($step['text']))
                        <p>{{ $step['text'] }}</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
@endif
