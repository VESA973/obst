{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>Actualités — La Quinzaine Obstétricale</title>
        <link>{{ route('news') }}</link>
        <description>Tribunes, interviews et publications sur la santé des femmes et la périnatalité en Guyane.</description>
        <language>fr-FR</language>
        <atom:link href="{{ route('news.feed') }}" rel="self" type="application/rss+xml" />
        @if ($articles->isNotEmpty())
            <lastBuildDate>{{ $articles->first()->published_at->toRssString() }}</lastBuildDate>
        @endif
        @foreach ($articles as $article)
            <item>
                <title>{{ $article->title }}</title>
                <link>{{ $article->publicUrl() }}</link>
                <guid isPermaLink="true">{{ $article->publicUrl() }}</guid>
                <pubDate>{{ $article->published_at->toRssString() }}</pubDate>
                <category>{{ $article->category->name }}</category>
                <description>{{ $article->summary(300) }}</description>
            </item>
        @endforeach
    </channel>
</rss>
