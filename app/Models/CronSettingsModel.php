<?php

namespace App\Models;

use CodeIgniter\Model;

class CronSettingsModel extends Model
{
    protected $table            = 'alert_cron_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'cron_key',
        'cron_name',
        'schedule',
        'is_enabled',
        'last_run',
        'updated_at',
        'updated_by'
    ];

    /**
     * Helper method to check if a specific cron is enabled
     * @param string $cronKey The key of the cron job (e.g. 'gemba_weekly_approval')
     * @return bool True if enabled, False if disabled or not found
     */
    public function isCronEnabled($cronKey)
    {
        $cron = $this->where('cron_key', $cronKey)->first();
        if ($cron) {
            return (bool)$cron['is_enabled'];
        }
        // If not found in DB, default to true or false? 
        // We'll default to false if it's not even registered, for safety.
        return false;
    }

    /**
     * Helper to update the last_run timestamp
     */
    public function updateLastRun($cronKey)
    {
        $this->where('cron_key', $cronKey)->set(['last_run' => date('Y-m-d H:i:s')])->update();
    }
}
