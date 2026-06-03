<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use App\Models\CmsPost;
use App\Models\CmsGallery;
use App\Models\CmsTestimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CmsController extends Controller
{
    /**
     * Authorize all actions in this controller using CmsPage policy.
     */
    protected function authorizeCms()
    {
        Gate::authorize('manage', CmsPage::class);
    }

    /**
     * Dashboard CMS.
     */
    public function dashboard()
    {
        $this->authorizeCms();

        $stats = [
            'total_posts' => CmsPost::count(),
            'total_views' => CmsPost::sum('view_count'),
            'active_galleries' => CmsGallery::where('is_active', true)->count(),
            'published_pages' => CmsPage::where('is_published', true)->count(),
        ];

        // For dashboard chart and listings
        $popularPosts = CmsPost::orderBy('view_count', 'desc')->take(5)->get();
        $recentPosts = CmsPost::latest()->take(5)->get();

        return view('cms.dashboard', compact('stats', 'popularPosts', 'recentPosts'));
    }

    // ==========================================
    // CMS PAGES CRUD
    // ==========================================

    public function pagesIndex()
    {
        $this->authorizeCms();
        $pages = CmsPage::orderBy('order', 'asc')->get();
        return view('cms.pages.index', compact('pages'));
    }

    public function pagesCreate()
    {
        $this->authorizeCms();
        return view('cms.pages.create');
    }

    public function pagesStore(Request $request)
    {
        $this->authorizeCms();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:cms_pages,slug',
            'content' => 'required|string',
            'meta_description' => 'nullable|string|max:255',
            'is_published' => 'boolean',
            'order' => 'integer',
            'show_in_nav' => 'boolean',
        ]);

        $validated['is_published'] = $request->has('is_published');
        $validated['show_in_nav'] = $request->has('show_in_nav');

        CmsPage::create($validated);

        return redirect()->route('cms.pages.index')->with('success', 'Halaman berhasil dibuat.');
    }

    public function pagesEdit(CmsPage $page)
    {
        $this->authorizeCms();
        return view('cms.pages.edit', compact('page'));
    }

    public function pagesUpdate(Request $request, CmsPage $page)
    {
        $this->authorizeCms();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:cms_pages,slug,' . $page->id,
            'content' => 'required|string',
            'meta_description' => 'nullable|string|max:255',
            'is_published' => 'boolean',
            'order' => 'integer',
            'show_in_nav' => 'boolean',
        ]);

        $validated['is_published'] = $request->has('is_published');
        $validated['show_in_nav'] = $request->has('show_in_nav');

        $page->update($validated);

        return redirect()->route('cms.pages.index')->with('success', 'Halaman berhasil diperbarui.');
    }

    public function pagesDestroy(CmsPage $page)
    {
        $this->authorizeCms();
        $page->delete();
        return redirect()->route('cms.pages.index')->with('success', 'Halaman berhasil dihapus.');
    }

    // ==========================================
    // CMS POSTS (NEWS/ARTICLES) CRUD
    // ==========================================

    public function postsIndex()
    {
        $this->authorizeCms();
        $posts = CmsPost::with('author')->latest()->paginate(10);
        return view('cms.posts.index', compact('posts'));
    }

    public function postsCreate()
    {
        $this->authorizeCms();
        return view('cms.posts.create');
    }

    public function postsStore(Request $request)
    {
        $this->authorizeCms();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:cms_posts,slug',
            'excerpt' => 'nullable|string|max:500',
            'body' => 'required|string',
            'cover_image' => 'nullable|image|max:2048', // max 2MB
            'category' => 'required|in:berita,prestasi,kegiatan,artikel',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $validated['is_published'] = $request->has('is_published');
        $validated['author_id'] = auth()->id();

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('cms/posts', 'public');
            $validated['cover_image'] = $path;
        }

        if ($validated['is_published'] && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        CmsPost::create($validated);

        return redirect()->route('cms.posts.index')->with('success', 'Artikel/Berita berhasil diterbitkan.');
    }

    public function postsEdit(CmsPost $post)
    {
        $this->authorizeCms();
        return view('cms.posts.edit', compact('post'));
    }

    public function postsUpdate(Request $request, CmsPost $post)
    {
        $this->authorizeCms();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:cms_posts,slug,' . $post->id,
            'excerpt' => 'nullable|string|max:500',
            'body' => 'required|string',
            'cover_image' => 'nullable|image|max:2048',
            'category' => 'required|in:berita,prestasi,kegiatan,artikel',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $validated['is_published'] = $request->has('is_published');

        if ($request->hasFile('cover_image')) {
            // Delete old file if exists
            if ($post->cover_image) {
                Storage::disk('public')->delete($post->cover_image);
            }
            $path = $request->file('cover_image')->store('cms/posts', 'public');
            $validated['cover_image'] = $path;
        }

        if ($validated['is_published'] && empty($validated['published_at'])) {
            $validated['published_at'] = $post->published_at ?: now();
        } elseif (!$validated['is_published']) {
            $validated['published_at'] = null;
        }

        $post->update($validated);

        return redirect()->route('cms.posts.index')->with('success', 'Artikel/Berita berhasil diperbarui.');
    }

    public function postsDestroy(CmsPost $post)
    {
        $this->authorizeCms();
        if ($post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
        }
        $post->delete();
        return redirect()->route('cms.posts.index')->with('success', 'Artikel/Berita berhasil dihapus.');
    }

    // ==========================================
    // CMS GALLERIES CRUD
    // ==========================================

    public function galleriesIndex()
    {
        $this->authorizeCms();
        $galleries = CmsGallery::orderBy('order', 'asc')->paginate(10);
        return view('cms.galleries.index', compact('galleries'));
    }

    public function galleriesStore(Request $request)
    {
        $this->authorizeCms();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image_path' => 'required|image|max:3072', // max 3MB
            'category' => 'nullable|string|max:100',
            'caption' => 'nullable|string|max:500',
            'order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store('cms/gallery', 'public');
            $validated['image_path'] = $path;
        }

        CmsGallery::create($validated);

        return redirect()->route('cms.galleries.index')->with('success', 'Foto galeri berhasil ditambahkan.');
    }

    public function galleriesUpdate(Request $request, CmsGallery $gallery)
    {
        $this->authorizeCms();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image_path' => 'nullable|image|max:3072',
            'category' => 'nullable|string|max:100',
            'caption' => 'nullable|string|max:500',
            'order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image_path')) {
            if ($gallery->image_path) {
                Storage::disk('public')->delete($gallery->image_path);
            }
            $path = $request->file('image_path')->store('cms/gallery', 'public');
            $validated['image_path'] = $path;
        }

        $gallery->update($validated);

        return redirect()->route('cms.galleries.index')->with('success', 'Foto galeri berhasil diperbarui.');
    }

    public function galleriesDestroy(CmsGallery $gallery)
    {
        $this->authorizeCms();
        if ($gallery->image_path) {
            Storage::disk('public')->delete($gallery->image_path);
        }
        $gallery->delete();
        return redirect()->route('cms.galleries.index')->with('success', 'Foto galeri berhasil dihapus.');
    }

    // ==========================================
    // CMS TESTIMONIALS CRUD
    // ==========================================

    public function testimonialsIndex()
    {
        $this->authorizeCms();
        $testimonials = CmsTestimonial::orderBy('order', 'asc')->paginate(10);
        return view('cms.testimonials.index', compact('testimonials'));
    }

    public function testimonialsStore(Request $request)
    {
        $this->authorizeCms();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'avatar_path' => 'nullable|image|max:1024', // max 1MB
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('avatar_path')) {
            $path = $request->file('avatar_path')->store('cms/testimonials', 'public');
            $validated['avatar_path'] = $path;
        }

        CmsTestimonial::create($validated);

        return redirect()->route('cms.testimonials.index')->with('success', 'Testimoni berhasil ditambahkan.');
    }

    public function testimonialsUpdate(Request $request, CmsTestimonial $testimonial)
    {
        $this->authorizeCms();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'avatar_path' => 'nullable|image|max:1024',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('avatar_path')) {
            if ($testimonial->avatar_path) {
                Storage::disk('public')->delete($testimonial->avatar_path);
            }
            $path = $request->file('avatar_path')->store('cms/testimonials', 'public');
            $validated['avatar_path'] = $path;
        }

        $testimonial->update($validated);

        return redirect()->route('cms.testimonials.index')->with('success', 'Testimoni berhasil diperbarui.');
    }

    public function testimonialsDestroy(CmsTestimonial $testimonial)
    {
        $this->authorizeCms();
        if ($testimonial->avatar_path) {
            Storage::disk('public')->delete($testimonial->avatar_path);
        }
        $testimonial->delete();
        return redirect()->route('cms.testimonials.index')->with('success', 'Testimoni berhasil dihapus.');
    }

    /**
     * Async image upload endpoint for rich text editor (Trix/TinyMCE/CKEditor).
     */
    public function uploadImage(Request $request)
    {
        $this->authorizeCms();

        $request->validate([
            'image' => 'required|image|max:4096', // max 4MB
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('cms/editor', 'public');
            return response()->json([
                'url' => asset('storage/' . $path),
            ]);
        }

        return response()->json(['error' => 'No image uploaded.'], 400);
    }
}
