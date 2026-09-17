<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Enums\UserRole;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(['email' => env('ADMIN_EMAIL', 'admin@naslabs.my.id')], ['name' => 'NasLabs Admin', 'role' => UserRole::Administrator, 'password' => env('ADMIN_PASSWORD', 'password')]);
        $categories = collect(['Engineering', 'Homelab', 'Data', 'Learning'])->mapWithKeys(fn ($name) => [$name => Category::firstOrCreate(['slug' => str($name)->slug()], ['name' => $name])]);
        $tags = collect(['Laravel', 'PostgreSQL', 'Linux', 'Docker', 'Observability', 'Clean Code'])->mapWithKeys(fn ($name) => [$name => Tag::firstOrCreate(['slug' => str($name)->slug()], ['name' => $name])]);

        $articles = [
            ['title' => 'Membangun Personal Engineering Journal dengan Laravel', 'category' => 'Engineering', 'tags' => ['Laravel', 'Clean Code'], 'excerpt' => 'Catatan arsitektur sederhana untuk blog teknis yang cepat, mudah dirawat, dan nyaman dikembangkan.', 'content' => "# Kenapa engineering journal?\n\nMenulis membuat keputusan teknis lebih mudah ditinjau ulang. Blog ini menggunakan Laravel monolith dengan Blade untuk halaman public dan Livewire untuk dashboard.\n\n## Prinsip awal\n\n- Server-rendered untuk SEO dan performa\n- Markdown sebagai source of truth\n- Action class untuk business operation\n- PostgreSQL sebagai database utama\n\nTargetnya bukan sistem paling kompleks, tetapi sistem yang bisa terus dipakai dan dirawat."],
            ['title' => 'Menyiapkan Homelab Kecil yang Bisa Dipantau', 'category' => 'Homelab', 'tags' => ['Linux', 'Docker', 'Observability'], 'excerpt' => 'Beberapa pelajaran dari mengubah mini PC bekas menjadi server rumahan yang terukur.', 'content' => "# Homelab yang pragmatis\n\nHomelab yang baik tidak harus mahal. Yang penting adalah mengetahui apa yang berjalan, kapan rusak, dan bagaimana memulihkannya.\n\n## Baseline\n\nSaya mulai dari Docker Compose, backup terjadwal, dan monitoring CPU, memory, disk, serta uptime. Setelah baseline stabil, barulah menambah service baru."],
            ['title' => 'PostgreSQL: Mulai dari Schema yang Sederhana', 'category' => 'Data', 'tags' => ['PostgreSQL', 'Clean Code'], 'excerpt' => 'Cara memodelkan artikel, kategori, dan tag tanpa menambah abstraksi sebelum waktunya.', 'content' => "# Schema yang cukup\n\nUntuk blog personal, tiga tabel inti sudah cukup: `articles`, `categories`, dan `tags`, dengan pivot `article_tag`.\n\nRelasi yang eksplisit membuat query tetap mudah dibaca. Optimasi seperti search engine atau repository layer bisa ditambahkan ketika kebutuhan nyata muncul."],
            ['title' => 'Markdown sebagai Format Konten yang Tahan Lama', 'category' => 'Learning', 'tags' => ['Laravel', 'Clean Code'], 'excerpt' => 'Mengapa plain text dan Markdown cocok untuk knowledge base yang ingin tetap portabel.', 'content' => "# Konten yang portabel\n\nMarkdown mudah dibaca manusia, mudah disimpan di PostgreSQL, dan tidak mengunci tulisan pada editor tertentu.\n\nRenderer tetap harus menghapus raw HTML dan menolak unsafe link agar konten yang ditampilkan tetap aman."],
            ['title' => 'Eksperimen: Menambahkan RSS dan Sitemap', 'category' => 'Engineering', 'tags' => ['Laravel', 'Observability'], 'excerpt' => 'Draft eksperimen untuk memastikan artikel mudah ditemukan crawler dan dibaca dari feed reader.', 'content' => "# RSS dan sitemap\n\nArtikel yang baik perlu bisa ditemukan. Sitemap membantu crawler memahami URL canonical, sementara RSS memberi pembaca cara mengikuti tulisan baru.\n\n> Ini masih draft eksperimen untuk validasi format dan metadata.", 'status' => ArticleStatus::Draft],
        ];

        foreach ($articles as $data) {
            $article = Article::updateOrCreate(['slug' => str($data['title'])->slug()], ['user_id' => $admin->id, 'category_id' => $categories[$data['category']]->id, 'title' => $data['title'], 'excerpt' => $data['excerpt'], 'content' => $data['content'], 'status' => $data['status'] ?? ArticleStatus::Published, 'published_at' => ($data['status'] ?? ArticleStatus::Published) === ArticleStatus::Published ? now()->subDays(rand(1, 12)) : null, 'meta_title' => $data['title'], 'meta_description' => $data['excerpt']]);
            $article->tags()->sync(collect($data['tags'])->map(fn ($tag) => $tags[$tag]->id));
        }

        $this->call(ArticleMediaSeeder::class);
        $this->call(LandingPageSeeder::class);
    }
}
