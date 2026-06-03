<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Boot the trait and register Eloquent observers.
     */
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->logActivity('created');
        });

        static::updated(function ($model) {
            $model->logActivity('updated');
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });
    }

    /**
     * Record the activity to the database.
     */
    protected function logActivity(string $event)
    {
        $oldValues = null;
        $newValues = $this->getAttributes();

        if ($event === 'updated') {
            $newValues = $this->getChanges();
            $oldValues = array_intersect_key($this->getOriginal(), $newValues);
        }

        // Filter out sensitive or irrelevant columns
        $ignore = ['password', 'remember_token', 'updated_at', 'created_at', 'email_verified_at'];
        if ($oldValues) {
            $oldValues = array_diff_key($oldValues, array_flip($ignore));
        }
        $newValues = array_diff_key($newValues, array_flip($ignore));

        // Skip if it's an update but no significant fields changed
        if ($event === 'updated' && empty($newValues)) {
            return;
        }

        // Special case for deleted: capture everything useful
        if ($event === 'deleted') {
            $oldValues = array_diff_key($this->getOriginal(), array_flip($ignore));
            $newValues = null;
        }

        // Record impersonator if action was done during impersonation
        if (session()->has('impersonate_by')) {
            $newValues = $newValues ?? [];
            $newValues['__impersonated_by_admin_id'] = session('impersonate_by');
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
