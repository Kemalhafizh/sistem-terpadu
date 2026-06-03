<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use App\Models\CmsPost;
use App\Models\CmsGallery;
use App\Models\CmsTestimonial;
use App\Models\Student;
use App\Models\User;
use App\Models\Classroom;
use Illuminate\Http\Request;

class PublicSiteController extends Controller
{
    /**
     * Display the dynamic landing page.
     */
    public function home()
    {
        $posts = CmsPost::with('author')
            ->published()
            ->latest('published_at')
            ->take(3)
            ->get();

        $galleries = CmsGallery::active()
            ->orderBy('order', 'asc')
            ->get();

        $testimonials = CmsTestimonial::where('is_active', true)
            ->orderBy('order', 'asc')
            ->get();

        // Calculate real-time stats
        $studentCount = Student::where('status', 'aktif')->count();
        $teacherStaffCount = User::whereIn('role', [
            'guru', 'staf_tu', 'admin', 'superadmin', 'bk', 
            'waka_kesiswaan', 'waka_kurikulum', 'waka_sarpras', 'waka_humas', 'kepala_sekolah'
        ])->count();
        $classroomCount = Classroom::count();

        $navPages = CmsPage::published()
            ->inNavbar()
            ->get();

        return view('welcome', compact(
            'posts', 
            'galleries', 
            'testimonials', 
            'studentCount', 
            'teacherStaffCount', 
            'classroomCount',
            'navPages'
        ));
    }

    /**
     * Display a custom static page.
     */
    public function page(string $slug)
    {
        $page = CmsPage::published()
            ->where('slug', $slug)
            ->firstOrFail();

        $navPages = CmsPage::published()
            ->inNavbar()
            ->get();

        return view('public.page', compact('page', 'navPages'));
    }

    /**
     * Display post/news index.
     */
    public function posts(Request $request)
    {
        $query = CmsPost::with('author')->published();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->byCategory($request->input('category'));
        }

        $posts = $query->latest('published_at')->paginate(9);

        $navPages = CmsPage::published()
            ->inNavbar()
            ->get();

        return view('public.posts.index', compact('posts', 'navPages'));
    }

    /**
     * Display a single post detail.
     */
    public function post(string $slug)
    {
        $post = CmsPost::with('author')
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        // Safely increment view count
        $post->increment('view_count');

        // Fetch related posts (same category, excluding current post)
        $relatedPosts = CmsPost::published()
            ->where('id', '!=', $post->id)
            ->where('category', $post->category)
            ->latest('published_at')
            ->take(3)
            ->get();

        // If not enough related posts, grab some latest published posts
        if ($relatedPosts->count() < 3) {
            $extraPosts = CmsPost::published()
                ->where('id', '!=', $post->id)
                ->whereNotIn('id', $relatedPosts->pluck('id'))
                ->latest('published_at')
                ->take(3 - $relatedPosts->count())
                ->get();
            $relatedPosts = $relatedPosts->merge($extraPosts);
        }

        $navPages = CmsPage::published()
            ->inNavbar()
            ->get();

        return view('public.posts.show', compact('post', 'relatedPosts', 'navPages'));
    }

    /**
     * Display gallery page.
     */
    public function gallery(Request $request)
    {
        $query = CmsGallery::active();

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $galleries = $query->orderBy('order', 'asc')->paginate(12);
        
        $categories = CmsGallery::active()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        $navPages = CmsPage::published()
            ->inNavbar()
            ->get();

        return view('public.gallery', compact('galleries', 'categories', 'navPages'));
    }
}
