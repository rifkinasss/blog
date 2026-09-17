@php echo '<?xml version="1.0" encoding="UTF-8"@endphp'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
    </url>
    <url>
        <loc>{{ route('articles.index') }}</loc>
    </url>
    @foreach ($posts as $post)
        <url>
            <loc>{{ route('articles.show', $post) }}</loc>
            <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
        </url>
    @endforeach
</urlset>
