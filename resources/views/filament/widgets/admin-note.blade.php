<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-pencil-square" heading="Note interne">
        @if ($maintenance)
            <p style="color: var(--warning-600); font-weight: 600; margin-bottom: .5rem;">
                Le mode maintenance est activé : les visiteurs voient la page de maintenance.
            </p>
        @endif

        @if (filled($note))
            <p style="white-space: pre-line;">{{ $note }}</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
