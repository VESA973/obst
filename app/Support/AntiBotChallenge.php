<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AntiBotChallenge
{
    public static function make(string $key): array
    {
        $left = random_int(1, 5);
        $right = random_int(1, 4);
        $token = Str::random(40);

        session()->put(self::sessionKey($key, $token), [
            'answer' => (string) ($left + $right),
            'created_at' => now()->timestamp,
        ]);

        return [
            'token' => $token,
            'question' => sprintf('%d + %d = ?', $left, $right),
            'started_at' => now()->timestamp,
        ];
    }

    public static function verify(Request $request, string $key, string $errorBag = 'default'): void
    {
        $errors = [];

        if ($request->filled('website')) {
            $errors['website'] = 'Le formulaire est invalide.';
        }

        $token = (string) $request->input('antibot_token', '');
        $answer = trim((string) $request->input('antibot_answer', ''));
        $challenge = $token !== '' ? $request->session()->pull(self::sessionKey($key, $token)) : null;

        if (! is_array($challenge) || $answer === '' || $answer !== (string) $challenge['answer']) {
            $errors['antibot_answer'] = 'Réponse incorrecte. Rechargez la page puis réessayez.';
        }

        $startedAt = (int) $request->input('form_started_at', 0);

        if ($startedAt <= 0 || now()->timestamp - $startedAt < 2) {
            $errors['form_started_at'] = 'Merci de patienter quelques secondes avant de valider le formulaire.';
        }

        if ($errors !== []) {
            $exception = ValidationException::withMessages($errors);
            $exception->errorBag = $errorBag;

            throw $exception;
        }
    }

    private static function sessionKey(string $key, string $token): string
    {
        return sprintf('antibot.%s.%s', $key, $token);
    }
}
