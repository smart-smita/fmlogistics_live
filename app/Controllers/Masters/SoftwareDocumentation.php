<?php

namespace App\Controllers\Masters;

use App\Controllers\BaseController;
use App\Models\SoftwareDocumentModel;
use App\Models\SoftwareDocumentVersionModel;
use App\Models\SoftwareDocumentDownloadModel;
use App\Models\SoftwareDefinitionModel;

class SoftwareDocumentation extends BaseController
{
    protected $docModel;
    protected $versionModel;
    protected $downloadModel;
    protected $defModel;

    public function __construct()
    {
        $this->docModel      = new SoftwareDocumentModel();
        $this->versionModel  = new SoftwareDocumentVersionModel();
        $this->downloadModel = new SoftwareDocumentDownloadModel();
        $this->defModel      = new SoftwareDefinitionModel();
        helper(['form', 'url']);
    }

    public function getCategories()
    {
        return [
            'SOPs' => [],
            'Protocols' => [],
            'OE Documents' => [],
            'Policies' => [],
            'Manuals' => ['OE Manuals', 'HSE Manuals'],
            'HSE Grid Checklist' => []
        ];
    }

    public function index()
    {
        $data['title'] = 'Software Documentation';
        $data['categories'] = $this->getCategories();
        
        $reqCategory = $this->request->getGet('category');
        if ($reqCategory) {
            $documents = $this->docModel->where('category', $reqCategory)->orderBy('created_at', 'DESC')->findAll();
        } else {
            $documents = $this->docModel->orderBy('created_at', 'DESC')->findAll();
        }
        
        $roleName = strtolower(session()->get('role') ?? '');
        $admin_flag = session()->get('admin_flag') ?? 0;
        $canWrite = in_array($roleName, ['super admin', 'super_admin', 'admin', 'auditor']) || $admin_flag == 1;
        
        $data['role'] = $roleName;
        $data['canWrite'] = $canWrite;

        // Summary KPIs requested by user
        $data['total_docs'] = $this->docModel->countAllResults();
        $data['total_defs'] = $this->defModel->countAllResults();
        $data['total_sops'] = $this->docModel->where('category', 'SOPs')->countAllResults();
        $data['total_protocols'] = $this->docModel->where('category', 'Protocols')->countAllResults();
        $data['total_oe'] = $this->docModel->where('category', 'OE Documents')->countAllResults();
        $data['total_policies'] = $this->docModel->where('category', 'Policies')->countAllResults();
        $data['total_manuals'] = $this->docModel->where('category', 'Manuals')->countAllResults();
        $data['total_hse'] = $this->docModel->where('category', 'HSE Grid Checklist')->countAllResults();

        // Prepare table data for table-view
        $tdata['display_contents'] = [
            'id' => 'Sr No',
            'doc_number' => 'Doc Number',
            'doc_name' => 'Name',
            'category' => 'Category',
            'version' => 'Version',
            'status' => 'Status',
            'action' => 'Actions',
        ];

        $table_data = [];
        $i = 1;
        
        foreach ($documents as $doc) {
            $action = '<div class="d-flex flex-nowrap gap-1">';
            $action .= '<a href="'.base_url('software-documentation/view/'.$doc['id']).'" class="btn btn-sm btn-primary" title="View Details"><i class="fas fa-eye"></i></a>';
            $action .= '<a href="'.base_url('software-documentation/download/'.$doc['id']).'" class="btn btn-sm btn-primary" title="Download"><i class="fas fa-download"></i></a>';
            if ($canWrite) {
                $action .= '<a href="'.base_url('software-documentation/edit/'.$doc['id']).'" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>';
                $action .= '<a href="'.base_url('software-documentation/delete/'.$doc['id']).'" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this document?\')" title="Delete"><i class="fas fa-trash"></i></a>';
            }
            $action .= '</div>';
            
            $status_badge = 'bg-success';
            if($doc['status'] == 'Inactive') $status_badge = 'bg-secondary';
            if($doc['status'] == 'Archived') $status_badge = 'bg-dark';

            $table_data[] = [
                'id' => $i++,
                'doc_number' => esc($doc['doc_number']),
                'doc_name' => '<strong>'.esc($doc['doc_name']).'</strong><br><small class="text-muted">'.esc($doc['department']).'</small>',
                'category' => esc($doc['category']),
                'version' => 'v'.esc($doc['version_number']),
                'status' => '<span class="badge '.$status_badge.' status-badge" style="font-size:0.75rem;padding:4px 8px;">'.$doc['status'].'</span>',
                'action' => $action
            ];
        }

        $tdata['table_data'] = $table_data;
        $tdata['title'] = 'Software Documentation List';

        $addBtn = '';
        if ($canWrite) {
            $addBtn = '<a href="'.base_url('software-documentation/create').'" class="btn btn-primary btn-sm mb-4"><i class="fas fa-upload me-2"></i> Upload Document</a>';
        }
        $tdata['top_dynamic_content'] = $addBtn;

        // Add Export Buttons
        // $tdata['export_button'] = '
        //     <div class="card-toolbar" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" title="Export Data">
        //         <button class="btn btn-sm btn-light-success me-2" onclick="ExportToExcel(\'xlsx\',\'Documentation\',false,\''.$tdata['title'].'\')">
        //             <i class="fas fa-file-excel me-1"></i> Excel
        //         </button>
        //         <button class="btn btn-sm btn-light-danger" onclick="window.print()">
        //             <i class="fas fa-file-pdf me-1"></i> Print / PDF
        //         </button>
        //     </div>
        // ';

        $data['table'] = view('Layout/table-view', $tdata);

        return view('Master/SoftwareDocumentation/index', $data);
    }

    public function create()
    {
        $roleName = strtolower(session()->get('role') ?? '');
        $admin_flag = session()->get('admin_flag') ?? 0;
        $canWrite = in_array($roleName, ['super admin', 'super_admin', 'admin', 'auditor']) || $admin_flag == 1;
        
        if (!$canWrite) {
            return redirect()->to('/software-documentation')->with('error', 'Unauthorized access.');
        }

        $data['title'] = 'Add Software Document';
        $data['categories'] = $this->getCategories();
        return view('Master/SoftwareDocumentation/form', $data);
    }

    public function store()
    {
        $roleName = strtolower(session()->get('role') ?? '');
        $admin_flag = session()->get('admin_flag') ?? 0;
        $canWrite = in_array($roleName, ['super admin', 'super_admin', 'admin', 'auditor']) || $admin_flag == 1;
        
        if (!$canWrite) return redirect()->to('/software-documentation');

        $file = $this->request->getFile('document_file');
        if ($file && !$file->isValid() && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            return redirect()->back()->withInput()->with('error', $file->getErrorString());
        }

        if (!$this->validate([
            'category' => 'required',
            'doc_name' => 'required',
            'version_number' => 'required',
            'document_file' => [
                'rules' => 'uploaded[document_file]|ext_in[document_file,pdf,jpg,jpeg,png,doc,docx,xls,xlsx,csv,ppt,pptx]|max_size[document_file,102400]',
                'errors' => [
                    'uploaded' => 'Please select a file.',
                    'ext_in' => 'Invalid file type. Allowed: pdf, jpg, jpeg, png, doc, docx, xls, xlsx, csv, ppt, pptx.',
                    'max_size' => 'The file is too large (max 100MB).'
                ]
            ]
        ])) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors()['document_file'] ?? 'Please fill all required fields.');
        }

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $fileType = $file->getClientExtension();
            $fileSize = $file->getSize();
            $originalName = $file->getClientName();

            // Store in writable/uploads/software_documents/
            $uploadPath = WRITEPATH . 'uploads/software_documents/';
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);

            $file->move($uploadPath, $newName);

            // Save Metadata
            $docData = [
                'document_id'    => 'DOC-' . time(), // Simple auto generation
                'category'       => $this->request->getVar('category'),
                'sub_category'   => '',
                'doc_name'       => $this->request->getVar('doc_name'),
                'doc_number'     => $this->request->getVar('doc_number'),
                'version_number' => $this->request->getVar('version_number') ?: '1.0',
                'revision_number'=> $this->request->getVar('revision_number'),
                'department'     => $this->request->getVar('department'),
                'description'    => $this->request->getVar('description'),
                'keywords'       => $this->request->getVar('keywords'),
                'effective_date' => $this->request->getVar('effective_date') ?: null,
                'review_date'    => $this->request->getVar('review_date') ?: null,
                'expiry_date'    => $this->request->getVar('expiry_date') ?: null,
                'file_name'      => $originalName,
                'file_path'      => $newName, // We just store the filename, the path is known securely
                'file_size'      => $fileSize,
                'file_type'      => $fileType,
                'status'         => $this->request->getVar('status') ?: 'Active',
                'uploaded_by'    => $this->session->get('user_id'),
                'upload_date'    => date('Y-m-d H:i:s'),
            ];

            $this->docModel->insert($docData);
            return redirect()->to('/software-documentation')->with('success', 'Document uploaded successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'File upload failed.');
    }

    public function edit($id)
    {
        $roleName = strtolower(session()->get('role') ?? '');
        $admin_flag = session()->get('admin_flag') ?? 0;
        $canWrite = in_array($roleName, ['super admin', 'super_admin', 'admin', 'auditor']) || $admin_flag == 1;
        
        if (!$canWrite) return redirect()->to('/software-documentation');

        $data['title'] = 'Edit Software Document';
        $data['categories'] = $this->getCategories();
        $data['document'] = $this->docModel->find($id);
        
        if (!$data['document']) return redirect()->to('/software-documentation')->with('error', 'Document not found.');

        return view('Master/SoftwareDocumentation/form', $data);
    }

    public function update($id)
    {
        $roleName = strtolower(session()->get('role') ?? '');
        $admin_flag = session()->get('admin_flag') ?? 0;
        $canWrite = in_array($roleName, ['super admin', 'super_admin', 'admin', 'auditor']) || $admin_flag == 1;
        
        if (!$canWrite) return redirect()->to('/software-documentation');

        $doc = $this->docModel->find($id);
        if (!$doc) return redirect()->to('/software-documentation')->with('error', 'Document not found.');

        if (!$this->validate([
            'category' => 'required',
            'doc_name' => 'required',
            'version_number' => 'required'
        ])) {
            return redirect()->back()->withInput()->with('error', 'Please fill all required fields.');
        }

        $docData = [
            'category'       => $this->request->getVar('category'),
            'sub_category'   => '',
            'doc_name'       => $this->request->getVar('doc_name'),
            'doc_number'     => $this->request->getVar('doc_number'),
            'version_number' => $this->request->getVar('version_number'),
            'revision_number'=> $this->request->getVar('revision_number'),
            'department'     => $this->request->getVar('department'),
            'description'    => $this->request->getVar('description'),
            'keywords'       => $this->request->getVar('keywords'),
            'effective_date' => $this->request->getVar('effective_date') ?: null,
            'review_date'    => $this->request->getVar('review_date') ?: null,
            'expiry_date'    => $this->request->getVar('expiry_date') ?: null,
            'status'         => $this->request->getVar('status'),
            'updated_by'     => $this->session->get('user_id'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        // Handle file replacement / versioning
        $file = $this->request->getFile('document_file');
        
        if ($file && !$file->isValid() && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            return redirect()->back()->withInput()->with('error', $file->getErrorString());
        }

        if ($file && $file->isValid() && !$file->hasMoved()) {
            
            if (!$this->validate([
                'document_file' => [
                    'rules' => 'ext_in[document_file,pdf,jpg,jpeg,png,doc,docx,xls,xlsx,csv,ppt,pptx]|max_size[document_file,102400]',
                    'errors' => [
                        'ext_in' => 'Invalid file type. Allowed: pdf, jpg, jpeg, png, doc, docx, xls, xlsx, csv, ppt, pptx.',
                        'max_size' => 'The file is too large (max 100MB).'
                    ]
                ]
            ])) {
                return redirect()->back()->withInput()->with('error', $this->validator->getErrors()['document_file']);
            }

            // Save the old file info to versions table
            $this->versionModel->insert([
                'document_id'    => $id,
                'version_number' => $doc['version_number'],
                'revision_number'=> $doc['revision_number'],
                'file_name'      => $doc['file_name'],
                'file_path'      => $doc['file_path'],
                'file_size'      => $doc['file_size'],
                'file_type'      => $doc['file_type'],
                'uploaded_by'    => $doc['uploaded_by'],
                'upload_date'    => $doc['upload_date']
            ]);

            // Save new file
            $newName = $file->getRandomName();
            $fileType = $file->getClientExtension();
            $fileSize = $file->getSize();
            $originalName = $file->getClientName();

            $uploadPath = WRITEPATH . 'uploads/software_documents/';
            $file->move($uploadPath, $newName);

            // Update docData with new file info
            $docData['file_name'] = $originalName;
            $docData['file_path'] = $newName;
            $docData['file_size'] = $fileSize;
            $docData['file_type'] = $fileType;
            $docData['uploaded_by'] = $this->session->get('user_id');
            $docData['upload_date'] = date('Y-m-d H:i:s');
        }

        $this->docModel->update($id, $docData);
        return redirect()->to('/software-documentation')->with('success', 'Document updated successfully.');
    }

    public function view($id)
    {
        $data['document'] = $this->docModel->withDeleted()->find($id);
        if (!$data['document']) {
            return redirect()->to('/software-documentation')->with('error', 'Document not found.');
        }
        
        $data['versions'] = $this->versionModel->where('document_id', $id)->orderBy('created_at', 'DESC')->findAll();
        $data['downloads'] = $this->downloadModel->where('document_id', $id)->countAllResults();

        return view('Master/SoftwareDocumentation/view', $data);
    }

    public function download($id, $version_id = null)
    {
        $filePath = '';
        $originalName = '';

        if ($version_id) {
            $version = $this->versionModel->find($version_id);
            if (!$version || $version['document_id'] != $id) return redirect()->to('/software-documentation')->with('error', 'Version not found.');
            $filePath = WRITEPATH . 'uploads/software_documents/' . $version['file_path'];
            $originalName = $version['file_name'];
        } else {
            $doc = $this->docModel->withDeleted()->find($id);
            if (!$doc) return redirect()->to('/software-documentation')->with('error', 'Document not found.');
            $filePath = WRITEPATH . 'uploads/software_documents/' . $doc['file_path'];
            $originalName = $doc['file_name'];
        }

        if (!file_exists($filePath)) {
            return redirect()->to('/software-documentation')->with('error', 'File does not exist on server.');
        }

        // Log download
        $this->downloadModel->insert([
            'document_id' => $id,
            'downloaded_by' => $this->session->get('user_id'),
            'download_date' => date('Y-m-d H:i:s')
        ]);

        return $this->response->download($filePath, null)->setFileName($originalName);
    }

    public function delete($id)
    {
        $roleName = strtolower(session()->get('role') ?? '');
        $admin_flag = session()->get('admin_flag') ?? 0;
        $canWrite = in_array($roleName, ['super admin', 'super_admin', 'admin', 'auditor']) || $admin_flag == 1;
        
        if (!$canWrite) return redirect()->to('/software-documentation'); // Only SA/Admin

        $doc = $this->docModel->find($id);
        if (!$doc) return redirect()->to('/software-documentation');

        $this->docModel->delete($id); // Soft delete
        return redirect()->to('/software-documentation')->with('success', 'Document deleted.');
    }

    public function restore($id)
    {
        $role = $this->session->get('role_id');
        if ($role != 1) return redirect()->to('/software-documentation'); // Only Super Admin

        $this->docModel->update($id, ['deleted_at' => null, 'is_deleted' => 0]);
        return redirect()->to('/software-documentation')->with('success', 'Document restored.');
    }
}
