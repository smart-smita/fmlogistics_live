<?php

namespace App\Controllers\Masters;

use App\Controllers\BaseController;
use Config\Database;

class Gemba_Ajax extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function get_sites()
    {
        $region = $this->request->getVar('region');
        $builder = $this->db->table('alert_gemba_audits')->select('DISTINCT(site_name) as site_name')->where('status !=', 2);
        if ($region) $builder->where('region', $region);
        return $this->response->setJSON($builder->orderBy('site_name', 'ASC')->get()->getResultArray());
    }

    public function get_site_category()
    {
        $region = $this->request->getVar('region');
        $site_name = $this->request->getVar('site_name');
        $builder = $this->db->table('alert_gemba_audits')->select('DISTINCT(site_category) as site_category')->where('status !=', 2);
        if ($region) $builder->where('region', $region);
        if ($site_name) $builder->where('site_name', $site_name);
        return $this->response->setJSON($builder->orderBy('site_category', 'ASC')->get()->getResultArray());
    }
}
