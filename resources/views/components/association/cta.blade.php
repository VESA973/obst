{{-- Bandeau final d'engagement : textes et liens existants du site (repris de l'accueil). --}}
<section {{ $attributes->class('assoc-cta') }} aria-labelledby="assoc-cta-title">
    <x-home.leaf-pattern variant="palm" class="assoc-cta-leaf" />
    <div class="home-container assoc-cta-inner">
        <div>
            <p class="home-eyebrow assoc-cta-eyebrow">Nous soutenir</p>
            <h2 id="assoc-cta-title">Agir avec l'association</h2>
            <p class="assoc-cta-text">Adhésion, don, partenariat, bénévolat ou simple prise de contact : chaque engagement renforce les actions d'information et de prévention.</p>
        </div>
        <div class="assoc-cta-actions">
            <a class="btn primary" href="{{ route('join') }}">Adhérer</a>
            <a class="btn secondary" href="{{ route('join') }}#don">Faire un don</a>
            <a class="btn secondary" href="{{ route('contact') }}">Nous contacter</a>
        </div>
    </div>
</section>
