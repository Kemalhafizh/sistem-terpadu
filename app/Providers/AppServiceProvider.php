<?php

namespace App\Providers;

use App\Events\NewNotificationEvent;
use App\Models\ActiveSession;
use App\Models\AssetLoan;
use App\Models\CmsGallery;
use App\Models\CmsPage;
use App\Models\CmsPost;
use App\Models\CmsTestimonial;
use App\Models\ComplianceDocument;
use App\Models\ComplianceMilestone;
use App\Models\CounselingSession;
use App\Models\EdsAssessment;
use App\Models\EdsPeriod;
use App\Models\Exam;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\LessonPlan;
use App\Models\LoginAttempt;
use App\Models\MpkAudit;
use App\Models\MpkLpj;
use App\Models\PortfolioItem;
use App\Models\StudentAspiration;
use App\Models\Syllabus;
use App\Models\UksVisit;
use App\Models\User;
use App\Models\WorkflowRequest;
use App\Policies\AssetLoanPolicy;
use App\Policies\CmsPolicy;
use App\Policies\CompliancePolicy;
use App\Policies\CounselingSessionPolicy;
use App\Policies\CurriculumPolicy;
use App\Policies\EdsPolicy;
use App\Policies\ExamPolicy;
use App\Policies\ForumPolicy;
use App\Policies\MpkPolicy;
use App\Policies\PortfolioPolicy;
use App\Policies\UksPolicy;
use App\Policies\WorkflowPolicy;
use App\Services\AppearanceService;
use App\Support\Access\RouteAccess;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
        // Absolute Access for IT Admin / SuperAdmin
        // Menus show a link only when its route would let this user in (same
        // role: rule the request is judged by). See App\Support\Access\RouteAccess.
        Blade::if('canVisit', fn (string $routeName) => RouteAccess::allowsRoute(auth()->user(), $routeName));

        // Only superadmin bypasses every ability. Admin rights are granted
        // explicitly in each policy/gate (operator scope), not by default.
        Gate::before(function ($user, $ability) {
            if ($user->role === 'superadmin') {
                return true;
            }
        });

        // Register UKS Policy
        Gate::policy(UksVisit::class, UksPolicy::class);

        // Register Counseling Policy
        Gate::policy(CounselingSession::class, CounselingSessionPolicy::class);

        // Register Forum Policy
        Gate::policy(ForumThread::class, ForumPolicy::class);
        Gate::policy(ForumReply::class, ForumPolicy::class);

        // Register CMS Policy
        Gate::policy(CmsPage::class, CmsPolicy::class);
        Gate::policy(CmsPost::class, CmsPolicy::class);
        Gate::policy(CmsGallery::class, CmsPolicy::class);
        Gate::policy(CmsTestimonial::class, CmsPolicy::class);

        // Register Curriculum Policy
        Gate::policy(LessonPlan::class, CurriculumPolicy::class);
        Gate::policy(Syllabus::class, CurriculumPolicy::class);

        // Register Asset Loan Policy
        Gate::policy(AssetLoan::class, AssetLoanPolicy::class);

        // Register CBT Exam Policy
        Gate::policy(Exam::class, ExamPolicy::class);

        // Register EDS Policy
        Gate::policy(EdsPeriod::class, EdsPolicy::class);
        Gate::policy(EdsAssessment::class, EdsPolicy::class);

        // Register Compliance Policy
        Gate::policy(ComplianceDocument::class, CompliancePolicy::class);
        Gate::policy(ComplianceMilestone::class, CompliancePolicy::class);

        // Register MPK Policy
        Gate::policy(MpkAudit::class, MpkPolicy::class);
        Gate::policy(MpkLpj::class, MpkPolicy::class);
        Gate::policy(StudentAspiration::class, MpkPolicy::class);

        // Register Smart Alerts Policy

        // Register Workflow Engine Policy
        Gate::policy(WorkflowRequest::class, WorkflowPolicy::class);

        // Register e-Portfolio Policy
        Gate::policy(PortfolioItem::class, PortfolioPolicy::class);

        // Tahfidz Module Access Gate
        Gate::define('access-tahfidz', function ($user) {
            if ($user->role !== 'guru') {
                return false;
            }

            return $user->subjects()->whereIn('name', ['Tahfidz', 'PAI', 'Al-Quran Hadits'])->exists();
        });

        // BK Module Access Gate
        Gate::define('access-bk', function ($user) {
            if ($user->role !== 'guru') {
                return false;
            }

            return $user->subjects()->whereIn('name', ['BK', 'Bimbingan Konseling'])->exists();
        });

        // 1. Rate Limiter for Login (Prevent Brute Force)
        RateLimiter::for('login', function (Request $request) {
            return app()->environment('local')
              ? Limit::none()
              : Limit::perMinute(5)->by($request->email.$request->ip());
        });

        // 2. Global API / Finance rate limiter
        RateLimiter::for('finance', function (Request $request) {
            return app()->environment('local')
              ? Limit::none()
              : Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // 3. General Global Stability
        RateLimiter::for('global', function (Request $request) {
            return app()->environment('local')
              ? Limit::none()
              : Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // 4. Rate Limiter for SPMB Registration (Prevent Spam)
        RateLimiter::for('spmb', function (Request $request) {
            return app()->environment('local')
              ? Limit::none()
              : Limit::perHour(3)->by($request->ip());
        });

        // 5. Rate Limiter for API endpoints
        RateLimiter::for('api', function (Request $request) {
            return app()->environment('local')
              ? Limit::none()
              : Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // 6. Rate Limiter for WhatsApp Gateway to prevent spamming Fonnte API
        RateLimiter::for('whatsapp', function (Request $request) {
            return app()->environment('local')
              ? Limit::none()
              : Limit::perMinute(10)->by($request->user()?->id);
        });

        // 7. Rate Limiter for heavy PDF/Excel Exports
        RateLimiter::for('export', function (Request $request) {
            return app()->environment('local')
              ? Limit::none()
              : Limit::perMinute(3)->by($request->user()?->id);
        });

        // Broadcast Database Notifications in Real-time via Laravel Reverb
        Event::listen(NotificationSent::class, function (NotificationSent $event) {
            if ($event->channel === 'database') {
                $notifiable = $event->notifiable;
                if ($notifiable instanceof User) {
                    $dbNotification = $notifiable->notifications()->first();
                    if ($dbNotification) {
                        broadcast(new NewNotificationEvent($notifiable, $dbNotification->toArray()))->toOthers();
                    }
                }
            }
        });

        // =========================================================
        // SECURITY MONITORING & SIEM EVENT LISTENERS
        // =========================================================

        // A. Successful Login Listener
        Event::listen(Login::class, function (Login $event) {
            $request = request();

            LoginAttempt::create([
                'email' => $event->user->email,
                'ip_address' => $request->ip() ?: '127.0.0.1',
                'user_agent' => $request->userAgent(),
                'successful' => true,
                'country' => 'Indonesia (Lokal)',
            ]);

            if ($request->hasSession()) {
                $userAgent = strtolower($request->userAgent() ?: '');
                $deviceType = 'desktop';
                if (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad') || (str_contains($userAgent, 'android') && ! str_contains($userAgent, 'mobi'))) {
                    $deviceType = 'tablet';
                } elseif (str_contains($userAgent, 'mobi') || str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipod') || str_contains($userAgent, 'blackberry') || str_contains($userAgent, 'webos')) {
                    $deviceType = 'mobile';
                }

                ActiveSession::updateOrCreate(
                    ['session_id' => $request->session()->getId()],
                    [
                        'user_id' => $event->user->id,
                        'ip_address' => $request->ip() ?: '127.0.0.1',
                        'user_agent' => $request->userAgent(),
                        'device_type' => $deviceType,
                        'last_activity_at' => now(),
                    ]
                );

                $request->session()->put('active_session_id', $request->session()->getId());
            }
        });

        // B. Failed Login Listener
        Event::listen(Failed::class, function (Failed $event) {
            $request = request();
            LoginAttempt::create([
                'email' => $event->credentials['email'] ?? 'unknown',
                'ip_address' => $request->ip() ?: '127.0.0.1',
                'user_agent' => $request->userAgent(),
                'successful' => false,
                'failure_reason' => 'Kredensial tidak valid',
                'country' => 'Indonesia (Lokal)',
            ]);
        });

        // C. Logout Listener
        Event::listen(Logout::class, function (Logout $event) {
            $request = request();
            if ($event->user && $request->hasSession()) {
                ActiveSession::where('session_id', $request->session()->getId())->delete();
            }
        });

        // Register Appearance Setting View Composer
        view()->composer(['layouts.app', 'layouts.guest', 'layouts.public', 'components.sidebar'], function ($view) {
            $view->with('appearance', app(AppearanceService::class)->getSettings());
        });

        // =========================================================
        // OBSERVABILITY - SYSTEM ERROR LOG EVENT LISTENER
        // =========================================================
        Event::listen(MessageLogged::class, function (MessageLogged $event) {
            if (in_array($event->level, ['error', 'critical', 'alert', 'emergency'])) {
                try {
                    $logPath = storage_path('logs/observability.json');

                    // Filter context to prevent recursion or huge serialization
                    $contextData = [];
                    $exception = $event->context['exception'] ?? null;
                    if ($exception instanceof \Throwable) {
                        $contextData = [
                            'class' => get_class($exception),
                            'file' => $exception->getFile(),
                            'line' => $exception->getLine(),
                            'trace' => substr($exception->getTraceAsString(), 0, 1000),
                        ];
                    } else {
                        foreach ($event->context as $key => $value) {
                            if (is_scalar($value)) {
                                $contextData[$key] = $value;
                            } elseif (is_array($value)) {
                                $contextData[$key] = array_slice($value, 0, 10);
                            }
                        }
                    }

                    $logEntry = [
                        'timestamp' => now()->toIso8601String(),
                        'type' => 'error',
                        'level' => $event->level,
                        'message' => $event->message,
                        'context' => $contextData,
                        'url' => request()->fullUrl(),
                        'user_id' => auth()->id(),
                    ];

                    file_put_contents($logPath, json_encode($logEntry)."\n", FILE_APPEND | LOCK_EX);

                    // Trim log file to prevent infinite growth
                    if (file_exists($logPath) && filesize($logPath) > 5 * 1024 * 1024) {
                        $lines = file($logPath);
                        if (count($lines) > 1000) {
                            $trimmed = array_slice($lines, -1000);
                            file_put_contents($logPath, implode('', $trimmed), LOCK_EX);
                        }
                    }
                } catch (\Exception $e) {
                    // Fail silently
                }
            }
        });

        // Register default Password Rules policy from config/auth.php
        Password::defaults(function () {
            $rules = Password::min(config('auth.password_rules.min_length', 8));

            if (config('auth.password_rules.require_uppercase', true)) {
                $rules->mixedCase();
            }

            if (config('auth.password_rules.require_number', true)) {
                $rules->numbers();
            }

            if (config('auth.password_rules.require_special_char', false)) {
                $rules->symbols();
            }

            return $rules;
        });
    }
}
