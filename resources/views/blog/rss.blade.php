@php echo '<?xml version="1.0" encoding="UTF-8"@endphp'; ?>
<rss version="2.0">
    <channel>
        <title>NasLabs Journal</title>
        <link>{{ url('/') }}</link>
        <description>Personal engineering journal</description>
        @foreach ($posts as $post)
            <item>
                <title>{{ $post->title }}</title>
                <link>{{ route('articles.show', $post) }}</link>
                <pubDate>{{ $post->published_at->toRssString() }}</pubDate>
                <description>{{ $post->excerpt }}</description>
            </item>
        @endforeach
    </channel>
</rss>
