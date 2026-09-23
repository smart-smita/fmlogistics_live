<?php

namespace App\Controllers\Masters;

use App\Controllers\BaseController;
use App\Models\SoftwareDefinitionModel;

class SoftwareDefinitions extends BaseController
{
    protected $defModel;

    public function __construct()
    {
        $this->defModel = new SoftwareDefinitionModel();
        helper(['form', 'url']);
    }

    public function getCategories()
    {
        return [
            'Category 1 Definitions' => [],
            'Other Definitions' => []
        ];
    }

    public function index()
    {
        $data['title'] = 'Software Definitions - Knowledge Base';
        $data['categories'] = $this->getCategories();
        
        $reqCategory = $this->request->getGet('category');
        if ($reqCategory) {
            $definitions = $this->defModel->where('category', $reqCategory)->orderBy('display_order', 'ASC')->orderBy('created_at', 'DESC')->findAll();
        } else {
            $definitions = $this->defModel->orderBy('display_order', 'ASC')->orderBy('created_at', 'DESC')->findAll();
        }
        
        $roleName = strtolower(session()->get('role') ?? '');
        $admin_flag = session()->get('admin_flag') ?? 0;
        $canWrite = in_array($roleName, ['super admin', 'super_admin', 'admin', 'auditor']) || $admin_flag == 1;
        
        $data['role'] = $roleName;
        $data['canWrite'] = $canWrite;
        $tdata['display_contents'] = [
            'id' => 'Sr No',
            'question' => 'Question',
            'status' => 'Status',
            'action' => 'Actions',
        ];

        $table_data = [];
        $i = 1;
        
        foreach ($definitions as $def) {
            $action = '<div class="d-flex flex-nowrap gap-1">';
            $action .= '<a href="'.base_url('software-definitions/view/'.$def['id']).'" class="btn btn-sm btn-primary" title="View Answer"><i class="fas fa-eye"></i></a>';
            if ($canWrite) {
                $action .= '<a href="'.base_url('software-definitions/edit/'.$def['id']).'" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>';
                $action .= '<a href="'.base_url('software-definitions/delete/'.$def['id']).'" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this question?\')" title="Delete"><i class="fas fa-trash"></i></a>';
            }
            $action .= '</div>';
            
            $status_badge = 'bg-success';
            if($def['status'] == 'Inactive') $status_badge = 'bg-secondary';
            if($def['status'] == 'Archived') $status_badge = 'bg-dark';

            $table_data[] = [
                'id' => $i++,
                'question' => '<strong>'.esc($def['question']).'</strong>',
                'status' => '<span class="badge '.$status_badge.' status-badge" style="font-size:0.75rem;padding:4px 8px;">'.$def['status'].'</span>',
                'action' => $action
            ];
        }

        $tdata['table_data'] = $table_data;
        $tdata['title'] = 'Definitions (Knowledge Base)';
        $tdata['enable_export'] = false;
        
        $addBtn = '';
        if ($canWrite) {
            $addBtn = '<a href="'.base_url('software-definitions/create').'" class="btn btn-primary btn-sm mb-4"><i class="fas fa-plus me-2"></i> Add Question</a>';
        }
        $tdata['top_dynamic_content'] = $addBtn;

        // Add export buttons for CSV and PDF Print
        $tdata['export_button'] = '
            <div class="card-toolbar" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" title="Export Data">
                <a href="'.base_url('software-definitions/export/csv').'" class="btn btn-sm btn-success me-2">
                    <i class="fas fa-file-excel me-1"></i> Excel / CSV
                </a>
                <a href="'.base_url('software-definitions/print').'" target="_blank" class="btn btn-sm btn-danger">
                    <i class="fas fa-file-pdf me-1"></i> Print / PDF
                </a>
            </div>
        ';

        $data['table'] = view('Layout/table-view', $tdata);

        return view('Master/SoftwareDefinitions/index', $data);
    }

    public function create()
    {
        $roleName = strtolower(session()->get('role') ?? '');
        $admin_flag = session()->get('admin_flag') ?? 0;
        $canWrite = in_array($roleName, ['super admin', 'super_admin', 'admin', 'auditor']) || $admin_flag == 1;
        
        if (!$canWrite) return redirect()->to('/software-definitions');

        $data['title'] = 'Add Definition';
        $data['categories'] = $this->getCategories();
        return view('Master/SoftwareDefinitions/form', $data);
    }

    public function store()
    {
        $roleName = strtolower(session()->get('role') ?? '');
        $admin_flag = session()->get('admin_flag') ?? 0;
        $canWrite = in_array($roleName, ['super admin', 'super_admin', 'admin', 'auditor']) || $admin_flag == 1;
        
        if (!$canWrite) return redirect()->to('/software-definitions');

        $data = [
            'question'       => $this->request->getVar('question'),
            'answer'         => $this->request->getVar('answer'),
            'category'       => 'Definitions',
            'sub_category'   => '',
            'keywords'       => '',
            'status'         => $this->request->getVar('status') ?: 'Active',
            'display_order'  => $this->request->getVar('display_order') ?: 0,
            'created_by'     => session()->get('user_id'),
            'created_at'     => date('Y-m-d H:i:s')
        ];

        $this->defModel->insert($data);
        return redirect()->to('/software-definitions')->with('success', 'Definition created successfully.');
    }

    public function edit($id)
    {
        $roleName = strtolower(session()->get('role') ?? '');
        $admin_flag = session()->get('admin_flag') ?? 0;
        $canWrite = in_array($roleName, ['super admin', 'super_admin', 'admin', 'auditor']) || $admin_flag == 1;
        
        if (!$canWrite) return redirect()->to('/software-definitions');

        $data['title'] = 'Edit Definition';
        $data['categories'] = $this->getCategories();
        $data['definition'] = $this->defModel->find($id);
        
        if (!$data['definition']) return redirect()->to('/software-definitions')->with('error', 'Definition not found.');

        return view('Master/SoftwareDefinitions/form', $data);
    }

    public function update($id)
    {
        $roleName = strtolower(session()->get('role') ?? '');
        $admin_flag = session()->get('admin_flag') ?? 0;
        $canWrite = in_array($roleName, ['super admin', 'super_admin', 'admin', 'auditor']) || $admin_flag == 1;
        
        if (!$canWrite) return redirect()->to('/software-definitions');

        $def = $this->defModel->find($id);
        if (!$def) return redirect()->to('/software-definitions')->with('error', 'Definition not found.');

        $data = [
            'question'       => $this->request->getVar('question'),
            'answer'         => $this->request->getVar('answer'), // Note: rich text can contain HTML
            'category'       => 'Definitions',
            'sub_category'   => '',
            'keywords'       => '',
            'status'         => $this->request->getVar('status'),
            'display_order'  => $this->request->getVar('display_order') ?: 0,
            'updated_by'     => session()->get('user_id'),
            'updated_at'     => date('Y-m-d H:i:s')
        ];

        $this->defModel->update($id, $data);
        return redirect()->to('/software-definitions')->with('success', 'Definition updated successfully.');
    }

    public function view($id)
    {
        $data['definition'] = $this->defModel->withDeleted()->find($id);
        if (!$data['definition']) {
            return redirect()->to('/software-definitions')->with('error', 'Definition not found.');
        }

        return view('Master/SoftwareDefinitions/view', $data);
    }

    public function delete($id)
    {
        $roleName = strtolower(session()->get('role') ?? '');
        $admin_flag = session()->get('admin_flag') ?? 0;
        $canWrite = in_array($roleName, ['super admin', 'super_admin', 'admin', 'auditor']) || $admin_flag == 1;
        
        if (!$canWrite) return redirect()->to('/software-definitions');

        $this->defModel->delete($id);
        return redirect()->to('/software-definitions')->with('success', 'Question deleted successfully.');
    }

    public function exportCsv()
    {
        $definitions = $this->defModel->orderBy('display_order', 'ASC')->findAll();
        
        $filename = 'Definitions_Export_' . date('YmdHis') . '.csv';
        
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv; ");
        
        $file = fopen('php://output', 'w');
        
        $header = array("Sr No", "Question", "Answer");
        fputcsv($file, $header);
        
        $i = 1;
        foreach ($definitions as $def) {
            $answer_plain = strip_tags($def['answer']); // Remove HTML tags for clean CSV
            fputcsv($file, [
                $i++,
                $def['question'],
                $answer_plain
            ]);
        }
        
        fclose($file);
        exit;
    }

    public function printView()
    {
        $data['title'] = 'Definitions Knowledge Base - Print View';
        $data['definitions'] = $this->defModel->orderBy('display_order', 'ASC')->findAll();
        return view('Master/SoftwareDefinitions/print', $data);
    }
}
