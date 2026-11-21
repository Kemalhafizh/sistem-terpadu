<?php

/*
 * Signed-in web routes that carry neither `role:` nor `can:`.
 * RouteAccess closes every such route to everyone but superadmin unless it
 * is listed here, and tests/Feature/Architecture/RouteGuardTest.php fails on
 * any such route that is not listed.
 *
 * Keys are "METHODS uri" as RouteAccess::key() writes them.
 */

return [
    // Open to every signed-in user, on purpose.
    'open_to_all' => [
        // Your own account and sign-in.
        'GET dashboard',
        'POST dashboard/save-layout',
        'GET profile',
        'PATCH profile',
        'DELETE profile',
        'PUT password',
        'GET force-change-password',
        'POST force-change-password',
        'GET verify-2fa',
        'POST verify-2fa',
        'GET verify-2fa/resend',
        'GET verify-email',
        'GET verify-email/{id}/{hash}',
        'POST email/verification-notification',
        'GET confirm-password',
        'POST confirm-password',
        'POST logout',
        // Your own inbox: the controllers read only the caller's notifications, conversations and push subscription.
        'GET notifications',
        'GET notifications/preferences',
        'POST notifications/preferences',
        'POST notifications/read-all',
        'POST notifications/mark-all-read',
        'POST notifications/{id}/read',
        'GET notifications/unread-count',
        'GET messages',
        'GET messages/compose',
        'POST messages',
        'POST messages/{conversation}/reply',
        'GET messages/search-users',
        'POST pwa/subscribe',
        // Two-factor setup / elevation and leaving impersonation apply to any authenticated session.
        'GET admin/impersonate-leave',
        'GET admin/security/2fa',
        'POST admin/security/2fa/confirm',
        'POST admin/security/2fa/disable',
        'GET admin/security/2fa/elevate',
        'POST admin/security/2fa/elevate',
        // Scoped by the model: Announcement::canBeSeenBy() answers 404 to anyone outside the audience.
        'GET announcements/{announcement}',
        'POST announcements/{announcement}/acknowledge',
        // Anyone may report bullying; the list and details stay with counsellors and leadership.
        'GET crisis/bullying/report',
        'POST crisis/bullying/report',
    ],

    // Fase 4A eliminated all remaining unguarded_debt routes by adding explicit
    // role middleware in routes/web.php and graduating universal session routes above.
    'unguarded_debt' => [],
];
