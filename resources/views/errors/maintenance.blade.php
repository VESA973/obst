<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance | La Quinzaine Obstétricale</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="maintenance-page">
        <section class="maintenance-card">
            <img src="{{ asset('images/quinzaine-logo.jpeg') }}" alt="La Quinzaine Obstétricale">
            <p class="eyebrow">Maintenance</p>
            <h1>Le site revient bientot</h1>
            <p>{{ $message }}</p>
        </section>
    </main>
</body>
</html>
