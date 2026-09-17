<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class LandingPageSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@naslabs.my.id')],
            ['name' => 'NasLabs Admin', 'password' => env('ADMIN_PASSWORD', 'password')],
        );

        $categories = collect([
            'Engineering' => 'Software architecture, implementation notes, and practical engineering decisions.',
            'Homelab' => 'Self-hosted infrastructure, reliability experiments, and operations notes.',
            'Data' => 'Database design, data modeling, and data-intensive systems.',
            'Learning' => 'Reading notes, experiments, and durable learning records.',
        ])->mapWithKeys(fn (string $description, string $name) => [
            $name => Category::updateOrCreate(['slug' => str($name)->slug()], ['name' => $name, 'description' => $description]),
        ]);

        $tags = collect(['Docker', 'Linux', 'Observability', 'PostgreSQL'])->mapWithKeys(fn (string $name) => [
            $name => Tag::firstOrCreate(['slug' => str($name)->slug()], ['name' => $name]),
        ]);

        $articles = [
            [
                'title' => 'Docker Compose sebagai Baseline untuk Service Homelab',
                'category' => 'Homelab',
                'tags' => ['Docker', 'Linux'],
                'excerpt' => 'Membuat stack homelab mudah dijalankan ulang melalui konfigurasi yang kecil, terdokumentasi, dan bisa diaudit.',
                'content' => "# Docker Compose sebagai baseline\n\nDocker Compose bukan jawaban untuk semua masalah, tetapi ia memberi baseline yang baik untuk service rumahan: deklaratif, mudah dibaca, dan cepat dipulihkan.\n\n## Checklist\n\n- Gunakan volume yang jelas\n- Pisahkan secret dari file Compose\n- Dokumentasikan port dan health check\n- Uji restore sebelum service bertambah banyak\n",
                'cover_image' => 'covers/homelab.png',
                'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Membuat Observability yang Berguna untuk Server Kecil',
                'category' => 'Engineering',
                'tags' => ['Observability', 'Linux'],
                'excerpt' => 'Mulai dari signal yang tepat: uptime, disk, memory, backup, dan alert yang benar-benar dapat ditindaklanjuti.',
                'content' => "# Observability yang berguna\n\nObservability untuk homelab tidak perlu kompleks. Mulai dari beberapa signal yang dapat menjawab pertanyaan operasional paling penting.\n\n## Signal awal\n\n1. Apakah service dapat dijangkau?\n2. Apakah disk dan memory mendekati batas?\n3. Apakah backup terakhir berhasil?\n4. Apakah alert memiliki tindakan yang jelas?\n",
                'cover_image' => 'covers/engineering-journal.png',
                'published_at' => now()->subDays(7),
            ],
        ];

        foreach ($articles as $data) {
            $article = Article::firstOrCreate(
                ['slug' => str($data['title'])->slug()],
                [
                    'user_id' => $admin->id,
                    'category_id' => $categories[$data['category']]->id,
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                    'cover_image' => $data['cover_image'],
                    'status' => ArticleStatus::Published,
                    'published_at' => $data['published_at'],
                    'meta_title' => $data['title'],
                    'meta_description' => $data['excerpt'],
                ],
            );

            $article->tags()->syncWithoutDetaching(collect($data['tags'])->map(fn (string $tag) => $tags[$tag]->id));
        }
    }
}
