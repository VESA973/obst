{{-- Notification interne : nouveau message du formulaire de contact. --}}
<p>Un nouveau message a été envoyé depuis la page Contact du site.</p>

<table cellpadding="6" style="border-collapse: collapse;">
    <tr><td><strong>Nom</strong></td><td>{{ $contactMessage->name }}</td></tr>
    <tr><td><strong>E-mail</strong></td><td><a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></td></tr>
    @if ($contactMessage->phone)
        <tr><td><strong>Téléphone</strong></td><td>{{ $contactMessage->phone }}</td></tr>
    @endif
    <tr><td><strong>Motif</strong></td><td>{{ $contactMessage->topicLabel() }}</td></tr>
    <tr><td><strong>Sujet</strong></td><td>{{ $contactMessage->subject }}</td></tr>
</table>

<p style="white-space: pre-line; padding: 12px; background: #fbf4ff; border-radius: 8px;">{{ $contactMessage->message }}</p>

<p>Pour répondre, utilisez simplement « Répondre » : le message partira vers {{ $contactMessage->email }}.<br>
Retrouvez tous les messages dans l'administration : Communauté &gt; Messages.</p>
