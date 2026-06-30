<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Confirmation inscription</title>
</head>
<body style="margin:0;background:#f7f2f7;color:#2b1c34;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f7f2f7;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border-radius:22px;overflow:hidden;border:1px solid #ead9f4;">
                    <tr>
                        <td style="padding:28px;background:#103f3d;color:#ffffff;">
                            <div style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;font-weight:700;">Inscription confirmée</div>
                            <h1 style="margin:10px 0 0;font-size:30px;line-height:1.15;">{{ $registration->event->title }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:26px;">
                            <p style="margin:0 0 18px;font-size:16px;line-height:1.55;">Bonjour {{ $registration->name }}, votre inscription est bien confirmée. Vous trouverez le récapitulatif ci-dessous.</p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #ead9f4;border-radius:16px;overflow:hidden;margin:0 0 22px;">
                                <tr>
                                    <td style="padding:14px 16px;background:#f7f2f7;font-weight:700;">Date</td>
                                    <td style="padding:14px 16px;">{{ $registration->event->event_date ? $registration->event->event_date->translatedFormat('d F Y') : 'À venir' }}</td>
                                </tr>
                                @if ($registration->event->schedule_items)
                                    <tr>
                                        <td style="padding:14px 16px;background:#f7f2f7;font-weight:700;">Horaires</td>
                                        <td style="padding:14px 16px;">
                                            @foreach ($registration->event->schedule_items as $slot)
                                                <div>
                                                    {{ ! empty($slot['label']) ? $slot['label'].' - ' : '' }}
                                                    {{ \Illuminate\Support\Carbon::parse($slot['date'])->translatedFormat('d F Y') }}
                                                    @if (! empty($slot['start_time'])) de {{ $slot['start_time'] }} @endif
                                                    @if (! empty($slot['end_time'])) à {{ $slot['end_time'] }} @endif
                                                </div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endif
                                @if ($registration->event->location)
                                    <tr>
                                        <td style="padding:14px 16px;background:#f7f2f7;font-weight:700;">Lieu</td>
                                        <td style="padding:14px 16px;">{{ $registration->event->location }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:14px 16px;background:#f7f2f7;font-weight:700;">Participant</td>
                                    <td style="padding:14px 16px;">{{ $registration->name }}<br>{{ $registration->email }}</td>
                                </tr>
                            </table>

                            @if ($registration->event->description)
                                <p style="margin:0 0 22px;font-size:15px;line-height:1.55;">{{ $registration->event->description }}</p>
                            @endif

                            @if ($registration->event->image_path)
                                <p style="margin:0 0 22px;font-size:14px;line-height:1.55;">Le flyer de l'événement est joint à cet email. Vous pouvez aussi le consulter en ligne.</p>
                                <p style="margin:0 0 22px;">
                                    <a href="{{ url(Storage::url($registration->event->image_path)) }}" style="display:inline-block;padding:12px 18px;border-radius:999px;background:#f18f7e;color:#ffffff;text-decoration:none;font-weight:700;">Ouvrir le flyer</a>
                                </p>
                            @endif

                            <p style="margin:0;color:#6f6176;font-size:13px;line-height:1.5;">Merci de conserver cet email comme confirmation d'inscription.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
