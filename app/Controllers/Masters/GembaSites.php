<?php

namespace App\Controllers\Masters;

use App\Controllers\BaseController;
use App\Models\GembaSitesModel;

class GembaSites extends BaseController
{
    protected $model;

    public function __construct()
    {
        helper(['gemba_acl']);
        $this->model = new GembaSitesModel();
    }

    public function index()
    {
        $tdata = [
            'title' => 'Gemba Sites Master',
            'display_contents' => [
                'id' => 'ID',
                'site_name' => 'Site Name',
                'site_type_1' => 'Site Type 1',
                'site_type_2' => 'Site Type 2',
                'site_category' => 'Site Category',
                'region' => 'Region',
                'status' => 'Status',
                'action' => 'Actions'
            ],
            'example2' => 'gemba_sites_table',
            'ajax_url_for_data' => base_url('gemba-sites/table_ajax'),
            'is_server_side' => true
        ];

        $data['table'] = view('Layout/table-view', $tdata);

        return view('Master/gemba_sites/list', $data);
    }

    public function table_ajax()
    {
        $req = service('request');
        $db = \Config\Database::connect();
        $builder = $db->table('alert_gemba_sites');
        $builder->where('status !=', 2);
        
        apply_gemba_role_filters($builder, 'alert_gemba_sites');

        // Search
        $searchValue = $req->getGet('search')['value'] ?? '';
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('site_name', $searchValue)
                ->orLike('site_type_1', $searchValue)
                ->orLike('site_type_2', $searchValue)
                ->orLike('site_category', $searchValue)
                ->orLike('region', $searchValue)
                ->groupEnd();
        }

        // Total Records
        $totalBuilder = clone $builder;
        $recordsFiltered = $totalBuilder->countAllResults(false);
        
        $totalBuilderAll = $db->table('alert_gemba_sites')->where('status !=', 2);
        apply_gemba_role_filters($totalBuilderAll, 'alert_gemba_sites');
        $totalRecords = $totalBuilderAll->countAllResults();

        // Order
        $order = $req->getGet('order');
        if ($order && isset($order[0])) {
            $columns = $req->getGet('columns');
            $colName = $columns[$order[0]['column']]['data'];
            if ($colName != 'action' && $colName != 'status') {
                $builder->orderBy($colName, $order[0]['dir']);
            } else {
                $builder->orderBy('id', 'DESC');
            }
        } else {
            $builder->orderBy('id', 'DESC');
        }

        // Pagination
        $start = (int) ($req->getGet('start') ?? 0);
        $length = (int) ($req->getGet('length') ?? 15);
        if ($length != -1) {
            $builder->limit($length, $start);
        }

        $results = $builder->get()->getResultArray();

        foreach ($results as &$row) {
            $row['status'] = ($row['status'] ?? 0) == 1 
                ? '<span class="badge badge-success fw-bold px-4 py-2">Active</span>' 
                : '<span class="badge badge-danger fw-bold px-4 py-2">Inactive</span>';
            
            $action = '<div class="d-flex justify-content-center gap-2">';
            
            if (gemba_can_write()) {
                // Edit Button (Blue/Solid)
                $action .= '<a href="' . base_url('gemba-sites/edit/' . $row['id']) . '" class="btn btn-icon btn-info btn-sm" title="Edit"><i class="fas fa-edit"></i></a>';
                
                // Delete Button (Red/Solid)
                $action .= '<a href="' . base_url('gemba-sites/delete/' . $row['id']) . '" class="btn btn-icon btn-danger btn-sm" onclick="return confirm(\'Are you sure?\')" title="Delete"><i class="fas fa-trash"></i></a>';
            }
            
            $action .= '</div>';
            $row['action'] = $action;
        }

        return $this->response->setJSON([
            'draw' => intval($req->getGet('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data' => $results
        ]);
    }

    private function getFormDropdownData()
    {
        $db = \Config\Database::connect();

        // Fetch Regions
        $data['db_regions'] = $db->query("SELECT region_name FROM alert_hse_region_master WHERE status = 1")->getResultArray();

        // Fetch Merged Site Names for the Dropdown (alert_client and alert_hse_client_master)
        $sites1 = $db->table("alert_client")->select("client_name as site_name")->where("status", 1)->get()->getResultArray();
        $sites2 = $db->table("alert_hse_client_master")->select("client_name as site_name")->where("status", 1)->get()->getResultArray();
        $mergedSites = array_filter(array_unique(array_merge(
            array_column($sites1, 'site_name'),
            array_column($sites2, 'site_name')
        )), function($val) { return trim((string)$val) !== ''; });
        natcasesort($mergedSites);
        $data['merged_sites'] = array_map(function($s) { return ['site_name' => $s]; }, array_values($mergedSites));

        return $data;
    }

    public function create()
    {
        gemba_require_write_access();
        if ($this->request->getMethod() === 'post') {
            $postData = $this->request->getVar();
            $postData['status'] = 1; // active by default
            if (
                $this->validate([
                    'site_name' => 'required',
                    'site_type_1' => 'required',
                    'site_type_2' => 'required',
                    'site_category' => 'required',
                    'region' => 'required'
                ])
            ) {
                // Manual uniqueness check across all relevant fields
                $db = \Config\Database::connect();
                $exists = $db->table('alert_gemba_sites')
                             ->where('site_name', $postData['site_name'])
                             ->where('site_type_1', $postData['site_type_1'])
                             ->where('site_type_2', $postData['site_type_2'])
                             ->where('site_category', $postData['site_category'])
                             ->where('region', $postData['region'])
                             ->where('status !=', 2)
                             ->countAllResults();

                if ($exists > 0) {
                    return redirect()->back()->withInput()->with('errors', ['site_name' => 'This exact combination of Site Name, Region, Site Types, and Category already exists.']);
                }

                $this->model->insert($postData);
                return redirect()->to('/gemba-sites')->with('success', 'Gemba Site created successfully.');
            } else {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $data = $this->getFormDropdownData();
        return view('Master/gemba_sites/form', $data);
    }

    public function edit($id = null)
    {
        gemba_require_write_access();
        $data['site'] = $this->model->find($id);
        if (!$data['site']) {
            return redirect()->to('/gemba-sites')->with('error', 'Record not found.');
        }

        if ($this->request->getMethod() === 'post') {
            $postData = $this->request->getVar();
            if (
                $this->validate([
                    'site_name' => 'required',
                    'site_type_1' => 'required',
                    'site_type_2' => 'required',
                    'site_category' => 'required',
                    'region' => 'required'
                ])
            ) {
                // Manual uniqueness check across all relevant fields
                $db = \Config\Database::connect();
                $exists = $db->table('alert_gemba_sites')
                             ->where('site_name', $postData['site_name'])
                             ->where('site_type_1', $postData['site_type_1'])
                             ->where('site_type_2', $postData['site_type_2'])
                             ->where('site_category', $postData['site_category'])
                             ->where('region', $postData['region'])
                             ->where('id !=', $id)
                             ->where('status !=', 2)
                             ->countAllResults();

                $oldSiteName = $data['site']['site_name'] ?? '';
                $newSiteName = trim($postData['site_name'] ?? '');

                if (!empty($oldSiteName) && !empty($newSiteName) && $oldSiteName !== $newSiteName) {
                    $renameService = new \App\Services\ClientSiteRenameService();
                    $renameRes = $renameService->renameGembaSite(
                        $oldSiteName,
                        $newSiteName,
                        session()->get('user_id') ?? 0,
                        session()->get('user_name') ?? 'System'
                    );

                    if ($renameRes['status'] === 0) {
                        return redirect()->back()->withInput()->with('errors', ['site_name' => $renameRes['message']]);
                    }
                } else {
                    $this->model->update($id, $postData);
                }

                return redirect()->to('/gemba-sites')->with('success', 'Gemba Site updated successfully.');
            } else {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $dropdownData = $this->getFormDropdownData();
        $data = array_merge($data, $dropdownData);

        return view('Master/gemba_sites/form', $data);
    }

    public function delete($id = null)
    {
        gemba_require_write_access();
        $site = $this->model->find($id);
        if ($site) {
            // soft delete
            $this->model->update($id, ['status' => 2]);
            return redirect()->to('/gemba-sites')->with('success', 'Gemba Site deleted successfully.');
        }
        return redirect()->to('/gemba-sites')->with('error', 'Record not found.');
    }
}
