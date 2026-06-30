@props([
    'key',
    'errorBag' => 'default',
])

@php
    $challenge = \App\Support\AntiBotChallenge::make($key);
    $fieldId = 'antibot-'.\Illuminate\Support\Str::slug($key).'-'.$challenge['token'];
    $bag = $errors->{$errorBag};
@endphp

<input name="website" type="text" tabindex="-1" autocomplete="off" class="hp-field" aria-hidden="true">
<input name="form_started_at" type="hidden" value="{{ $challenge['started_at'] }}">
<input name="antibot_token" type="hidden" value="{{ $challenge['token'] }}">

<label class="captcha-field" for="{{ $fieldId }}">
    <span>Question rapide : {{ $challenge['question'] }}</span>
    <input id="{{ $fieldId }}" name="antibot_answer" type="text" inputmode="numeric" autocomplete="off" required>
</label>

@if ($bag->has('antibot_answer') || $bag->has('form_started_at') || $bag->has('website'))
    <p class="form-error">{{ $bag->first('antibot_answer') ?: $bag->first('form_started_at') ?: $bag->first('website') }}</p>
@endif
