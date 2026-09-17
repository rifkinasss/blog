<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminPostController extends Controller
{
    public function index()
    {
        return view('admin.posts.index', ['posts' => Post::with('category')->latest()->paginate(15)]);
    }

    public function create()
    {
        return view('admin.posts.form', ['post' => new Post, 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $post = $this->save($request, new Post);

        return redirect()->route('admin.posts.edit', $post)->with('success', 'Artikel disimpan.');
    }

    public function edit(Post $post)
    {
        return view('admin.posts.form', ['post' => $post, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(Request $request, Post $post)
    {
        $this->save($request, $post);

        return back()->with('success', 'Artikel diperbarui.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return back()->with('success', 'Artikel dihapus.');
    }

    private function save(Request $request, Post $post): Post
    {
        $data = $request->validate(['title' => 'required|max:180', 'excerpt' => 'nullable|max:300', 'body' => 'required', 'status' => 'required|in:draft,published', 'category_id' => 'nullable|exists:categories,id', 'cover_image' => 'nullable|image|max:4096', 'seo_title' => 'nullable|max:180', 'seo_description' => 'nullable|max:300']);
        $data['slug'] = Str::slug($data['title']);
        if ($data['status'] === 'published' && ! $post->published_at) {
            $data['published_at'] = now();
        }if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }$post->fill($data)->save();

        return $post;
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Kredensial tidak valid.']);
        }$request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
