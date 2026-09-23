<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CronSettingsModel;
use App\Models\CronLogsModel;

class CronSettings extends BaseController
{
    public function index()
    {
        $cronModel = new CronSettingsModel();
        
        $data['crons'] = $cronModel->findAll();
        // print_r($data);
        // exit;
;        return view('Admin/cron_settings', $data);
    }

    public function toggleStatus()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Invalid request']);
        }

        $cronId = $this->request->getVar('id');
        $newStatus = $this->request->getVar('status') === '1' ? 1 : 0;

        $cronModel = new CronSettingsModel();
        $cron = $cronModel->find($cronId);

        if (!$cron) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cron job not found']);
        }

        $previousStatus = $cron['is_enabled'];
        
        if ($previousStatus != $newStatus) {
            $cronModel->update($cronId, ['is_enabled' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')]);
            
            $logModel = new CronLogsModel();
            $logModel->insert([
                'cron_name' => $cron['cron_name'],
                'action' => $newStatus === 1 ? 'ENABLED' : 'DISABLED',
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'changed_by' => session()->get('user_id') ?? 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Cron status updated successfully']);
    }
}
