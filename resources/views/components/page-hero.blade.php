@props(['pageKey', 'class' => 'compact'])

@php
    $pageHero = \App\Models\PageSetting::forKey($pageKey);
    $heroClasses = trim('page-hero '.$class.' title-'.$pageHero['title_size'].($pageHero['hero_image_path'] ?? false ? ' has-hero-image' : ''));
    $heroImageUrl = ! empty($pageHero['hero_image_path'])
        ? (str_starts_with($pageHero['hero_image_path'], 'images/') ? asset($pageHero['hero_image_path']) : Storage::url($pageHero['hero_image_path']))
        : null;
@endphp

<section class="{{ $heroClasses }}" @if ($heroImageUrl) style="--page-hero-image: url('{{ $heroImageUrl }}')" @endif>
    <p class="eyebrow">{{ $pageHero['eyebrow'] ?? $pageHero['menu_label'] ?? '' }}</p>
    <h1>{{ $pageHero['title'] ?? '' }}</h1>
    @if (! empty($pageHero['description']))
        <p>{{ $pageHero['description'] }}</p>
    @endif
</section>
