<?php

//namespace App\Controllers;
namespace App\Controllers\Customer;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Screen extends BaseController
{
 
 
   public function __construct(){
        helper("form");
     $db = null;
     $db['table']         = 'ads_screen_master';
     $db['allowedFields'] = [
       'screen_name','device_id','screen_discription','user_id','customer_id','status'];
     $db['primaryKey'] = "screen_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    
     public function index(){
         $postData['user_id'] =$this->user_id;
            if($this->customer_id!=0)
                $postData['customer_id'] = $this->customer_id;
                
         $data['table_data'] =  $this->BaseModel->select("*, 
         (select 
                (select group_concat(media_url) from ads_media_master where ads_media_master.status=1 and ads_media_master.media_id in (
                    select media_id from ads_playlist_items where ads_playlist_items.playlist_id in 
                            (select ads_screen_playlist.playlist_id from ads_screen_playlist where ads_screen_playlist.screen_id=ads_screen_master.screen_id
                            )
                    )
       )  ) as media_list")->where($postData)->where(["status!="=>"2"])->findAll();
      return view("Customer/screen",$data);
    }
    
     public function add($id=null){
                          $db = db_connect();

          $db1 = null;
     $db1['table']         = 'ads_media_playlist';
     $db1['allowedFields'] = [
       'playlist_name','playlist_description','user_id','customer_id','status'];
     $db1['primaryKey'] = "playlist_id";
     $ads_media_playlist = new CRUDBaseModel($db1);
        $postData['user_id'] =$this->user_id;
            if($this->customer_id!=0)
                $postData['customer_id'] = $this->customer_id;
          $data['table_data'] =  $ads_media_playlist->select("*, (select 
       (select group_concat(media_url) from ads_media_master where 
       ads_media_master.media_id in (select media_id from ads_playlist_items where ads_playlist_items.playlist_id=ads_media_playlist.playlist_id )
       )  ) as media_list")->where($postData)->where(["status!="=>"2"])->findAll();
       $data['save_screen_list_details']= base_url("Customer/Screen/save_details");
               $data['customer_id'] = $this->customer_id;
              $data['user_id'] = $this->user_id;
           if(isset($id)){
               $data['save_screen_list_details'] = base_url("/Customer/Screen/save_details/".$id);
                                    $ads_screen_playlist = $db->table('ads_screen_playlist');
                                    $ads_screen_playlist->where('status', '1');
                                    $ads_screen_playlist->where('screen_id', $id);

                                $data['screen_playlist_items'] = $ads_screen_playlist->get()->getResultArray();
                                     $data['details']=$this->BaseModel->find($id);
                                     $data['customer_id'] = $data['details']['customer_id'];
                                     $data['user_id'] = $data['details']['user_id'];

           }
                                             $ads_device_details = $db->table('ads_device_details');
                                $ads_device_details->where('status', '1');
                                $ads_device_details->where('user_id', $this->user_id);
                                $ads_device_details->limit(500);
                                $data['api_device'] = $ads_device_details->get()->getResultArray();
                                

           echo $this->update_details($this->user_id);
      return view("Customer/add_screen",$data);
    }
    
    
    public function list()
    {
        $data = [];

        $tdata['title']="Screen";
        $tdata['button_name']="Add Screen";
        $tdata['button_id']="screen";
        
        $tdata['display_contents'] = [
            "screen_id"=>"ID",
            "screen_name"=>"Screen Name",
            "action"=>"Action"
            ];
        $data['ajax_url']=base_url("Customer/Screen/save_details");
        $tdata ['ajax_url_for_data']=base_url("Customer/Screen/table_ajax");
        $data['table'] = view("Layout/table-view",$tdata);
        // $data['table'] ="";
        
        // start: to get select option values 
      //  return view("Layout/table-view",$data);
        return view("Customer/screen_list",$data);
    }
    public function table_ajax(){
        
        $tdata['table_data'] = $this->BaseModel->findAll();
        foreach($tdata['table_data'] as $key=>$row){
            $active = '<button class="btn btn-icon btn-success" onclick="url_call_ajax(\''.base_url("Customer/Screen/save_details/".$row['screen_id']).'/active\',$(this));">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-unlock"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button> ';
			$deactive = '<button  class="btn btn-icon btn-danger" onclick="url_call_ajax(\''.base_url("Customer/Screen/save_details/".$row['screen_id']).'/deactive\',$(this));">
                			<span class="indicator-label svg-icon svg-icon-2">
                			<i class="fa fa-lock"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';
				$delete = '<button data-ajax-url="'.base_url("Customer/Screen/save_details/".$row['screen_id']).'/delete" class="btn btn-icon btn-danger" onclick="delete_row(this);">
                			<span class="indicator-label svg-icon svg-icon-2">
                				
								<i class="fa fa-trash"></i>
                				
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';

            //$(this).attr('data-kt-indicator', 'on');$(this).attr('disabled', true);setTimeout(function (obj) {obj.attr('data-kt-indicator', 'off');obj.attr('disabled', false);},500,$(this));
             $edit = '<button data-ajax-url="'.base_url("Customer/Screen/get_form_data/".$row['screen_id']).'" class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['screen_id'].');">
											<span class="indicator-label svg-icon svg-icon-3">
												<i class="fa fa-edit"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button>
											';
											if($row['status']=="0"){
											    $deactive = "";
											    $tdata['table_data'][$key]['tr_class']="bg-light-warning";
											    
											}else if($row['status']=="1"){
											    $active = "";
											    
											}else if($row['status']=="2"){
											    $delete = "";
											    $deactive = "";
											    $tdata['table_data'][$key]['tr_class']="bg-light-danger";
											    
											}
			$tdata['table_data'][$key]['action']=$edit.$active.$deactive.$delete;
        }
        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);
        return $this->response->setJSON($tdata);

    }
    public function get_form_data($id){
        $responce['status'] = "0";
        $responce['message'] = "Details not found";
        if(isset($id)){

            $responce['data'] = $this->BaseModel->find($id);
            // print_r($responce['data']);
            // exit();
            $responce['status'] = "1";
            $responce['message'] = "Details found";
        }
        echo json_encode($responce);
    }
    public function save_details($id=null,$action=null){
                $request = service('request');
                $postData = $request->getVar();
    // print_r($postData);
    // exit();
                if(isset($postData['honeypot']))
                    {
                        // if($postData['honeypot'] != ""){
                        //     $responce['status'] = "0";
                        //     $responce['message'] = "Data insertion faild";
                        //     die;
                        // }
                     unset($postData['honeypot']);   
                    }
                    
                if(isset($id)){
                        $responce['message'] = "Data updation faild";
                        if(isset($action)){
                            switch($action){
                                case "active":
                                    $postData['status']= "1";
                                    $responce['status'] = "1";
                                    $responce['message'] = "Activated successfully";
                                    break;
                                case "deactive":
                                    $postData['status']= "0";
                                    $responce['status'] = "1";
                                    $responce['message'] = "Dectivated successfully";
                                    
                                    break;
                                case "delete":
                                    $postData['status']= "2";
                                    $responce['status'] = "1";
                                    $responce['message'] = "Deleted successfully";
                                    
                                    break;
                            }
                        }
                    if($this->BaseModel->update($id,$postData)){
                        if(isset($postData['playlist_id'])){
                            $screen_id = $id;
                                                    

                            $ads_playlist_items_list = [];
                            $db = db_connect();
                            $ads_screen_playlist = $db->table('ads_screen_playlist');
                            
                            $ads_screen_playlist->delete(['screen_id'=>$id]);
                            // $ads_playlist_items->set('status', '2');
                            // $ads_playlist_items->where('playlist_id', $id);
                            // $ads_playlist_items->update();
                            
                            $replace_flag = false;
                            foreach($postData['playlist_id'] as $key=>$row){
                            $ads_screen_playlist_items_list_NEW=[];

                                $ads_screen_playlist_items_list_NEW['start_date']= $postData['start_date'][$key];
                                $ads_screen_playlist_items_list_NEW['end_date']= $postData['end_date'][$key];
                                $ads_screen_playlist_items_list_NEW['playlist_id']= $row;
                                $ads_screen_playlist_items_list_NEW['customer_id']= $postData['customer_id'];
                                $ads_screen_playlist_items_list_NEW['user_id']= $postData['user_id'];
                                
                                $ads_screen_playlist_items_list_NEW['screen_id']= $screen_id;
                                $ads_screen_playlist_items_list_NEW['status']= "1";
                                array_push($ads_playlist_items_list,$ads_screen_playlist_items_list_NEW);
                            }
                            // if($replace_flag){
                            if($ads_screen_playlist->insertBatch($ads_playlist_items_list)){

                                $responce['status'] = "1";
                                $responce['message'] = "Data saved successfully";
                            }else{
                                $responce['status'] = "0";
                                $responce['message'] = "Data insertion issue into media item";
                                
                            }
                        }
                        }
                }else{
                    $postData['status']="1";
                        $responce['status'] = "0";
                        $responce['message'] = "Data insertion faild";
                    if($this->BaseModel->insert($postData)){
                        $screen_id = $this->BaseModel->getInsertID();
                        if(isset($postData['playlist_id'])){
                            $ads_playlist_items_list = [];
                            $db = db_connect();
                            $ads_screen_playlist = $db->table('ads_screen_playlist');
                            
                            $ads_screen_playlist->delete(['screen_id'=>$id]);
                            // $ads_playlist_items->set('status', '2');
                            // $ads_playlist_items->where('playlist_id', $id);
                            // $ads_playlist_items->update();
                            
                            $replace_flag = false;
                            foreach($postData['playlist_id'] as $key=>$row){
                            $ads_screen_playlist_items_list_NEW=[];

                                $ads_screen_playlist_items_list_NEW['start_date']= $postData['start_date'][$key];
                                $ads_screen_playlist_items_list_NEW['end_date']= $postData['end_date'][$key];
                                $ads_screen_playlist_items_list_NEW['playlist_id']= $row;
                                $ads_screen_playlist_items_list_NEW['customer_id']= $postData['customer_id'];
                                $ads_screen_playlist_items_list_NEW['user_id']= $postData['user_id'];
                                
                                $ads_screen_playlist_items_list_NEW['screen_id']= $screen_id;
                                $ads_screen_playlist_items_list_NEW['status']= "1";
                                array_push($ads_playlist_items_list,$ads_screen_playlist_items_list_NEW);
                            }
                            // if($replace_flag){
                            if($ads_screen_playlist->insertBatch($ads_playlist_items_list)){

                                $responce['status'] = "1";
                                $responce['message'] = "Data saved successfully";
                            }else{
                                $responce['status'] = "0";
                                $responce['message'] = "Data insertion issue into media item";
                                
                            }
                        }
                }
                    
                }
                echo json_encode($responce);
    }
}
