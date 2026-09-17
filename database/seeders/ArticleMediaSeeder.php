<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleMediaSeeder extends Seeder
{
    public function run(): void
    {
        $covers = [
            'Membangun Personal Engineering Journal dengan Laravel' => 'covers/engineering-journal.png',
            'Menyiapkan Homelab Kecil yang Bisa Dipantau' => 'covers/homelab.png',
            'PostgreSQL: Mulai dari Schema yang Sederhana' => 'covers/postgresql.png',
            'Markdown sebagai Format Konten yang Tahan Lama' => 'covers/engineering-journal.png',
            'Eksperimen: Menambahkan RSS dan Sitemap' => 'covers/postgresql.png',
        ];

        foreach ($covers as $title => $cover) {
            Article::where('title', $title)->update(['cover_image' => $cover]);
        }
    }
}
