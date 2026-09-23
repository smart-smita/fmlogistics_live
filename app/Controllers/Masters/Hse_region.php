<?php

namespace App\Controllers\Masters;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;

class Hse_region extends BaseController
{
    /**
     * @var CRUDBaseModel
     */
    protected $BaseModel;

    public function __construct(){
        helper("form");
        $db = null;
        $db['table']         = 'alert_hse_region_master';
        $db['allowedFields'] = ['region_name','status'];
        $db['primaryKey']    = "region_id";
        $this->BaseModel = new CRUDBaseModel($db);
    }

    public function index()
    {
        $data = [];

        $tdata['title']       = "HSE Region";
        $tdata['button_name'] = "Add HSE Region";
        $tdata['button_id']   = "HseRegion";

        $tdata['display_contents'] = [
            "region_id"   => "ID",
            "region_name" => "Region",
            "action"      => "Action"
        ];

        $data['ajax_url'] = base_url("Masters/Hse_region/save_details");
        $tdata['ajax_url_for_data'] = base_url("Masters/Hse_region/table_ajax");
        $data['table'] = view("Layout/table-view", $tdata);

        return view("Master/add_hse_region", $data);
    }

    public function table_ajax()
    {
        $tdata['table_data'] = $this->BaseModel->findAll();
        $statusMessages = [
            0 => '<span class="badge badge-warning">Pending</span>',
            1 => '<span class="badge badge-info">Verified</span>',
            2 => '<span class="badge badge-success">Active</span>',
            3 => '<span class="badge badge-secondary">Deactivated</span>'
        ];

        foreach($tdata['table_data'] as $key => $row){
            $tdata['table_data'][$key]['sr_no'] = $key + 1;

            $active = '<button class="btn btn-icon btn-success" onclick="url_call_ajax(\''.base_url("Masters/Hse_region/save_details/".$row['region_id']).'/active\',$(this));">
                <span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-unlock"></i></span>
                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button> ';

            $deactive = '<button class="btn btn-icon btn-danger" onclick="url_call_ajax(\''.base_url("Masters/Hse_region/save_details/".$row['region_id']).'/deactive\',$(this));">
                <span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-lock"></i></span>
                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button> ';

            $delete = '<button data-ajax-url="'.base_url("Masters/Hse_region/save_details/".$row['region_id']).'/delete" class="btn btn-icon btn-danger" onclick="delete_row(this);">
                <span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-trash"></i></span>
                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button> ';

            $edit = '<button data-ajax-url="'.base_url("Masters/Hse_region/get_form_data/".$row['region_id']).'" class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['region_id'].');">
                <span class="indicator-label svg-icon svg-icon-3"><i class="fa fa-edit"></i></span>
                <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>';

            if($row['status'] == "0"){
                $deactive = "";
                $tdata['table_data'][$key]['tr_class'] = "bg-light-warning";
            } else if($row['status'] == "1"){
                $active = "";
            } else if($row['status'] == "2"){
                $delete = "";
                $deactive = "";
                $tdata['table_data'][$key]['tr_class'] = "bg-light-danger";
            }

            $tdata['table_data'][$key]['action'] = "<center>".$statusMessages[$row['status']]."<br><br>".$active.$deactive.$delete.$edit."</center>";
        }

        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);
        return $this->response->setJSON($tdata);
    }

    public function get_form_data($id)
    {
        $responce['status']  = "0";
        $responce['message'] = "Details not found";
        if(isset($id)){
            $responce['data']    = $this->BaseModel->find($id);
            $responce['status']  = "1";
            $responce['message'] = "Details found";
        }
        echo json_encode($responce);
    }

    public function save_details($id = null, $action = null)
    {
        $request  = service('request');
        $postData = $request->getVar();

        if(isset($postData['honeypot'])){
            unset($postData['honeypot']);
        }

        if(isset($id)){
            $responce['message'] = "Data updation failed";
            if(isset($action)){
                switch($action){
                    case "active":   $postData['status'] = "1"; break;
                    case "deactive": $postData['status'] = "0"; break;
                    case "delete":   $postData['status'] = "2"; break;
                }
            }
            if($this->BaseModel->update($id, $postData)){
                $responce['status']  = "1";
                $responce['message'] = "Data saved successfully";
            }
        } else {
            $postData['status'] = "1";
            $responce['status']  = "0";
            $responce['message'] = "Data insertion failed";
            if($this->BaseModel->insert($postData)){
                $responce['status']  = "1";
                $responce['message'] = "Data saved successfully";
            }
        }
        echo json_encode($responce);
    }
}
