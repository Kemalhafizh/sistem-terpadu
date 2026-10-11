<?php

use Illuminate\Support\Facades\Route;

/*
 * FASE 119: modules cut from the school ERP stay cut. Each PR of the debloat
 * adds the URI prefixes it removed; a route that comes back under one of them
 * fails here.
 */

dataset('removed prefixes', [
    // PR 1: developer tools and showcase technology.
    'deploy/', 'developer/', 'ux/', 'etl', 'plugins', 'api-docs', 'graphql', 'api-clients', 'api-stats',
    'query-performance', 'asset-performance', 'load-test', 'scaling',
    'admin/observability', 'admin/queue-monitor', 'admin/report-builder', 'admin/uat', 'admin/disaster-recovery',
    'admin/analytics/edw', 'admin/vendor-sla', 'admin/webhooks', 'admin/bpa',
    'api/v1/infrastructure', 'api/v1/debloat', 'api/v1/design', 'api/v1/iot/energy', 'api/v1/blockchain/did',
    'api/v1/certificates/verify',
    // PR 2: corporate management, marketing and media.
    'strategic/', 'quality', 'risk', 'sop', 'workflow/', 'hc/cpd', 'hc/succession', 'hc/workload',
    'recruitment', 'compensation', 'wellness', 'turnover', 'omnichannel', 'drip-campaigns', 'nps', 'pr/',
    'publishing/', 'mentorship', 'partnerships', 'volunteers', 'media', 'finance/audit',
    'financial/cost-centers', 'financial/procurement', 'growth/lifecycle',
    'analytics/predictive', 'analytics/engagement', 'analytics/finance', 'analytics/benchmarking',
    'api/v1/analytics/at-risk', 'api/v1/analytics/treasury', 'api/v1/analytics/student/{id}/risk', 'api/mobile/token',
    // PR 3: gimmicks and the mobile endpoints behind them (AI, gamification, GPS/QR attendance, OCR, signatures).
    'adaptive', 'micro-lessons', 'gamified-quizzes', 'spaced-repetition', 'transcripts', 'knowledge', 'homework-copilot',
    'academic/competency', 'academic/copilot', 'academic/pathways', 'academic/scorm', 'academic/virtual',
    'accessibility', 'hall-of-fame', 'alumni/reunion-planner', 'interactive-ui', 'dashboards/', 'mobile/',
    'gamification', 'chatbot', 'admin/chatbot', 'smart-alerts', 'career-portal', 'college-counseling', 'internships',
    'showcase', 'evaluations', 'student/idcard', 'growth/wellbeing', 'mini-bank', 'merchant/', 'admin/finance/canteen',
    'admin/finance/wadiah', 'class-feed', 'family-plan', 'parent-skills', 'green-school', 'campus', 'geofencing',
    'surveillance', 'safety-drills', 'emergency-alerts', 'crisis/command-center', 'crisis/declare', 'crisis/incident',
    'crisis/protocols', 'crisis/broadcast', 'islamic-events', 'dawah', 'spiritual', 'crowdfunding', 'endowment',
    'foundation', 'data-engineering', 'data-quality', 'nlq', 'report-scheduler', 'ocr', 'documents/pending-signatures',
    'documents/signature', 'documents/test-request', 'verify-document',
    'api/v1/ai', 'api/v1/gamification', 'api/v1/blockchain', 'api/v1/iot', 'api/v1/mobile/attendance',
    'api/v1/documents/ocr', 'api/v1/documents/signature',
    // PR 4: the second calendar (one calendar per school, AcademicEvent).
    'calendar-hub',
]);

test('route modul yang sudah dipotong tidak muncul lagi', function (string $prefix) {
    $back = collect(Route::getRoutes()->getRoutes())
        ->map(fn ($route) => $route->uri())
        ->filter(fn (string $uri) => $uri === rtrim($prefix, '/') || str_starts_with($uri, $prefix))
        ->values()->all();

    expect($back)->toBe([]);
})->with('removed prefixes');
