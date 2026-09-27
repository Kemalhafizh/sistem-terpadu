<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UatFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UatFeedbackController extends Controller
{
    /**
     * Store a new UAT feedback.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'category' => 'required|string|in:bug,ui_suggestion,unclear_feature,slow,other',
            'description' => 'required|string|min:5',
            'page_url' => 'required|string',
            'device_info' => 'nullable|array',
            'screenshot' => 'nullable|string', // base64 encoded image or file path if uploaded in other way
        ]);

        $screenshotPath = null;

        // Handle base64 screenshot upload from html2canvas with strict extension whitelist
        if (! empty($validated['screenshot']) && str_starts_with($validated['screenshot'], 'data:image')) {
            try {
                $imageParts = explode(';base64,', $validated['screenshot']);
                if (count($imageParts) === 2) {
                    $imageTypeAux = explode('image/', $imageParts[0]);
                    $rawType = strtolower(trim($imageTypeAux[1] ?? 'png'));

                    // Strict whitelist: Only allow safe raster image formats
                    $allowedExtensions = [
                        'png' => 'png',
                        'jpeg' => 'jpg',
                        'jpg' => 'jpg',
                        'webp' => 'webp',
                    ];

                    if (! isset($allowedExtensions[$rawType])) {
                        throw new \InvalidArgumentException('Format gambar tidak didukung atau tidak aman.');
                    }

                    $imageType = $allowedExtensions[$rawType];
                    $imageBase64 = base64_decode($imageParts[1]);

                    if ($imageBase64 === false) {
                        throw new \InvalidArgumentException('Data gambar base64 tidak valid.');
                    }

                    $filename = 'screenshot_'.time().'_'.uniqid().'.'.$imageType;
                    $path = 'uat_screenshots/'.$filename;

                    Storage::disk('public')->put($path, $imageBase64);
                    $screenshotPath = $path;
                }
            } catch (\Exception $e) {
                // Log screenshot error without blocking feedback submission
                report($e);
            }
        } elseif ($request->hasFile('screenshot_file')) {
            // Support standard file upload with strict safe raster image whitelist
            $request->validate([
                'screenshot_file' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:5120', // Max 5MB
            ]);
            $screenshotPath = $request->file('screenshot_file')->store('uat_screenshots', 'public');
        }

        $feedback = UatFeedback::create([
            'user_id' => auth()->id(),
            'page_url' => $validated['page_url'],
            'rating' => $validated['rating'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'screenshot_path' => $screenshotPath,
            'device_info' => $validated['device_info'] ?? [],
            'status' => 'open',
            'priority' => 'medium',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Feedback Anda berhasil dikirim! Terima kasih atas kontribusi Anda dalam pengembangan Sistem Terpadu.',
                'data' => $feedback,
            ]);
        }

        return redirect()->back()->with('success', 'Feedback berhasil dikirim!');
    }

    /**
     * Display the UAT feedback dashboard for admins.
     */
    public function dashboard(Request $request)
    {
        $query = UatFeedback::with('user');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('page_url', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('role', 'like', "%{$search}%");
                    });
            });
        }

        $feedbacks = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Calculate simple stats for widgets
        $stats = [
            'total' => UatFeedback::count(),
            'open' => UatFeedback::where('status', 'open')->count(),
            'in_progress' => UatFeedback::where('status', 'in_progress')->count(),
            'fixed' => UatFeedback::where('status', 'fixed')->count(),
            'verified' => UatFeedback::where('status', 'verified')->count(),
        ];

        return view('admin.uat.feedback-dashboard', compact('feedbacks', 'stats'));
    }

    /**
     * Update status, priority, and developer notes.
     */
    public function updateStatus(Request $request, $id)
    {
        $feedback = UatFeedback::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:open,in_progress,fixed,verified,wont_fix',
            'priority' => 'required|string|in:critical,high,medium,low',
            'developer_notes' => 'nullable|string',
        ]);

        $feedback->update($validated);

        return redirect()->back()->with('success', 'Status feedback UAT berhasil diperbarui!');
    }

    /**
     * Display the UAT feedback report for management.
     */
    public function report()
    {
        // Total feedback
        $totalFeedback = UatFeedback::count();

        // Average rating
        $avgRating = UatFeedback::avg('rating') ?: 0;

        // Feedback by Category
        $byCategory = UatFeedback::select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->get()
            ->mapWithKeys(function ($item) use ($totalFeedback) {
                return [$item->category => [
                    'count' => $item->total,
                    'percentage' => $totalFeedback > 0 ? round(($item->total / $totalFeedback) * 100, 1) : 0,
                ]];
            })->toArray();

        // Ensure all categories exist in output structure
        foreach (['bug', 'ui_suggestion', 'unclear_feature', 'slow', 'other'] as $cat) {
            if (! isset($byCategory[$cat])) {
                $byCategory[$cat] = ['count' => 0, 'percentage' => 0];
            }
        }

        // Feedback by Status
        $byStatus = UatFeedback::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) use ($totalFeedback) {
                return [$item->status => [
                    'count' => $item->total,
                    'percentage' => $totalFeedback > 0 ? round(($item->total / $totalFeedback) * 100, 1) : 0,
                ]];
            })->toArray();

        foreach (['open', 'in_progress', 'fixed', 'verified', 'wont_fix'] as $stat) {
            if (! isset($byStatus[$stat])) {
                $byStatus[$stat] = ['count' => 0, 'percentage' => 0];
            }
        }

        // Feedback by Priority
        $byPriority = UatFeedback::select('priority', DB::raw('count(*) as total'))
            ->groupBy('priority')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->priority => $item->total];
            })->toArray();

        foreach (['critical', 'high', 'medium', 'low'] as $prio) {
            if (! isset($byPriority[$prio])) {
                $byPriority[$prio] = 0;
            }
        }

        // Top pages reported
        $topPages = UatFeedback::select('page_url', DB::raw('count(*) as total'), DB::raw('avg(rating) as avg_rating'))
            ->groupBy('page_url')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        return view('admin.uat.feedback-report', compact(
            'totalFeedback',
            'avgRating',
            'byCategory',
            'byStatus',
            'byPriority',
            'topPages'
        ));
    }
}
