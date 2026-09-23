<?php

namespace App\Controllers\Admin;
use App\Models\CRUDBaseModel;
use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public $BaseModel=null;
    public function __construct(){
    
    }
    public function index(){
        $db1 = db_connect();
        
        $emailSettingsModel = new \App\Models\EmailSettingsModel();
        $data['email_settings'] = $emailSettingsModel->getSettings();
        
        $data['user_count']=0;
        $data['active_device_count'] = 0;
         $sql = "SELECT count(*) as counts FROM ads_user ";
         $query= $db1->query($sql);
        $row = $query->getRowArray();
        if(isset($row) && count($row)>0){
                $data['user_count']=$this->convert_number($row['counts']);
                            
        }
        $data['customer_count']=0;
        $sql = "SELECT count(*) as counts FROM ads_customer ";
        $query= $db1->query($sql);
        $row = $query->getRowArray();
        if(isset($row) && count($row)>0){
            $data['customer_count']=$this->convert_number($row['counts']);
        }
        $sql = "SELECT * FROM `ads_device_reports` WHERE update_date > now() - interval 20 minute GROUP by device_id ";
        $query= $db1->query($sql);
        $row = $query->getResultArray();
        if(isset($row) && count($row)>0){
                $data['active_device_count']=count($row);
        }
        $data['device_count']=0;
        $sql = "SELECT count(*) as counts FROM ads_device_master ";
        $query= $db1->query($sql);
        $row = $query->getRowArray();
        if(isset($row) && count($row)>0){
            $data['device_count']=$this->convert_number($row['counts'])." ".(($data['active_device_count']>0)?" / ".$data['active_device_count']:"");
        }
        $data['total_campaign']=0;
        $data['active_campaign'] = 0;
        $data['deliverd_campaign'] = 0;
        $sql = "SELECT count(*) as total,
                sum(if(campaign_status=3,1,0)) as deliverd,
                sum(if(campaign_status=1,1,0)) as active
                FROM ads_advertiser_campaign ";
        $query= $db1->query($sql);
        $row = $query->getRowArray();
            if(isset($row) && count($row)>0){
                $data['total_campaign'] = $this->convert_number($row['total']);
                $data['active_campaign'] = $this->convert_number($row['active']);
                $data['deliverd_campaign'] = $this->convert_number($row['deliverd']);
            }
        $data['total_media'] = 0;
        $sql = "SELECT count(*) as total FROM ads_media_master ";
        $query= $db1->query($sql);
        $row = $query->getRowArray();
            if(isset($row) && count($row)>0){
                $data['total_media'] = $this->convert_number($row['total']);
            }

        $data['total_impressions'] = 0;
        $sql = "SELECT sum(count) as total FROM ads_device_reports ";
        $query= $db1->query($sql);
        $row = $query->getRowArray();
            if(isset($row) && count($row)>0){
                $data['total_impressions'] = $this->convert_number($row['total']);
            }
            $tdata['title']="All Device Details";
        
          $tdata['display_contents'] = [
            "ago"=>"Ago",
            "device_id"=>"ID",
            "device_name"=>"Device Name",
            "serial_no"=>"Serial Number",
            "device_uuid"=>"UUID",
            "company_name"=>"Assign To",
            "active_tv"=>"Status",
            "action"=>"Action"
            /*"action"=>"Action"*/
            ];
              $tdata['export_button']='<button class="btn btn-icon btn-success" onclick="ExportToExcel()">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-file-csv"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button>';
        $tdata ['ajax_url_for_data']=base_url("Masters/Device/table_ajax");
        $data['table_details'] = view("Layout/table-view",$tdata);
        return view('Admin/dashboard',$data);
    }
}
