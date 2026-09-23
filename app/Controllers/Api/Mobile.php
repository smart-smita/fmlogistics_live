<?php

//namespace App\Controllers;
namespace App\Controllers\API;
use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\CRUDBaseModel;


class Mobile extends BaseController
{
    use ResponseTrait;

    public function __construct(){
        
    }
    public function getCitis(){
        //   ads_cities
        $db = db_connect();
        $sql = " select * from ads_cities";
        $query= $db->query($sql);
        $result = $query->getResultArray();
        return $this->respondCreated($result);
    }
    public function getStates(){
        //   ads_cities
        $db = db_connect();
        $sql = " select * from ads_states";
        $query= $db->query($sql);
        $result = $query->getResultArray();
        return $this->respondCreated($result);
    }
    public function getOrientations(){
    //   ads_cities
        $db = db_connect();
        $sql = "SELECT DISTINCT `orientation` FROM `ads_device_details`";
        $query= $db->query($sql);
        $result = $query->getResultArray();
        return $this->respondCreated($result);
    
    }
     public function getPrices(){
    //   ads_cities
        $db = db_connect();
        $sql = "SELECT min(cost_for_impression) as minCost,max(cost_for_impression) as maxCost FROM `ads_device_details`";
        $query= $db->query($sql);
        $result = $query->getResultArray();
        return $this->respondCreated($result);
    }
     public function getTrendingScreens(){
    //   ads_cities
        $db = db_connect();
        $sql = "SELECT * FROM ads_device_details";
        $query= $db->query($sql);
        $result = $query->getResultArray();
        return $this->respondCreated($result);
    }
     public function getFootfall(){
    //   ads_cities
        $db = db_connect();
        $sql = "SELECT min(monthly_footfall) as minFootFall , max(monthly_footfall) as maxFootFall FROM `ads_device_details`";
        $query= $db->query($sql);
        $result = $query->getResultArray();
        return $this->respondCreated($result);
    }
    
    public function getPlaylisLink($uid,$screen,$device_id=0){
        // https://ads.unitglo.com/Mobiyoung/Demo/API/getPlaylis/9/231
        $db =db_connect();
                 $sql = "select 
                 ads_media_master.media_id,
                 ads_media_master.media_type,
                 concat('".base_url()."','/',ads_media_master.media_url ) as media_url, ads_playlist_items.duration_sec,ads_playlist_items.loop_count from ads_screen_playlist, 
ads_media_master, 
ads_playlist_items,
ads_media_playlist where 
ads_media_master.status = 1 
and ads_media_playlist.status = 1
and ads_screen_playlist.status = 1
and ads_screen_playlist.status = 1
and ads_media_master.media_id = ads_playlist_items.media_id 
and ads_media_playlist.playlist_id = ads_playlist_items.playlist_id 
and ads_media_playlist.playlist_id = ads_screen_playlist.playlist_id 
and ads_screen_playlist.screen_id = ?";
                    $query= $db->query($sql, [$screen]);
                    $row = $query->getResultArray();
                    $responce['status'] = "1";
                    $responce['message'] = "Details Found";
                    $responce['data'] =$row;
                    $responce['reporting_url'] = base_url("API/Mobile/setDeviceReport/mobile");
                    $responce['device_id']= $device_id;
                    $responce['screen_id']= $screen;
                    $responce['customer_id']= "0";
                    $responce['user_id']= $uid;
                    $responce['report_date']= date("Y-m-d");
                    
                    
                    return view("player_play_list_from_link",$responce);
                                    // return $this->respondCreated($responce);

                    
    }
        public function checkBuild($id){
// header('Content-Type: application/jar');
// header('Content-Type: application/apk');
// header('Content-Disposition: attachment; filename="AJS-Application.apk"');
// $apk = file_get_contents('https://ads.unitglo.com/AJS-Application.apk');
// header("Content-Length: ".strlen($apk)); 
// print($apk);

// header('Content-Type: application/jar');
// header('Content-Type: application/apk');
// header('Content-Disposition: attachment; filename="main.apk"');
// header('Content-Length: ' . filesize ("main.apk"));
// readfile('main.apk');


//             <a class="thirdbox" 
//   href="/media/wysiwyg/pdf/systemaufbau-anleitungen.pdf" 
//   target="_blank" download="Systemaufbau_Anleitungen" title="Datei downloaden">...</a>
            // echo "Build is <a href='https://ads.unitglo.com/AJS-Application.apk'>".$id."</a> Upto date";
        echo " <script> 
         const url = 'intent://chromecast.com/#Intent;scheme=comgooglecast;package=com.google.android.apps.chromecast.app;end';
   window.location.replace(url);
        </script>";
    }
    public function GetSiteInfo($mode="web"){
           if($mode=="mobile"){
            $postData = json_decode(file_get_contents('php://input'), true);    
        }else{
            $postData = $_POST;
        }
        $responce['status'] = "0";
        $responce['message'] = "Request faild ";
        if(isset($postData) && isset($postData['user_email']) && isset($postData['password']) ){
             //user tab
            $sql = "select * from ads_user where email_id = ? and user_password = ?";
             //  $query=  db_connect()->query($sql,[$postData['user_email'],password_hash($postData['password'], PASSWORD_DEFAULT)]);
            $query=  db_connect()->query($sql,[$postData['user_email'],$postData['password']]);
            $data= $query->getResultArray();
            $responce['status'] = "0";
            $responce['message'] = "Details Not Found";
                 if(isset($data) && count($data)>0){
                    $db = db_connect();
                    $ads_device_details = $db->table('ads_device_details')->select("*,concat('".base_url()."/',device_image) as device_image");
                    $ads_device_details->where("status","1");
                    if(isset($postData['limit']) && isset($postData['offset'])){
                        $ads_device_details->limit($postData['limit'],$postData['offset']);
                    }
                    $responce  = $ads_device_details->get()->getResultArray();
                    // $responce  = json_decode(file_get_contents(WRITEPATH."../app/Controllers/API/data.json"), true);
                 }
        }
        return $this->respondCreated($responce);
    }
    //  public function GetSiteInfoLive($type,$mode="web"){
    //       if($mode=="mobile"){
    //         $postData = json_decode(file_get_contents('php://input'), true);    
    //     }else{
    //         $postData = $_POST;
    //     }
    //     $responce['status'] = "0";
    //     $responce['message'] = "Request faild ";
    //             if(isset($postData) && isset($postData['user_email']) && isset($postData['password']) ){
    //         //user tab
    //         $sql = "select * from ads_user where email_id = ? and user_password = ?";
    //         //  $query=  db_connect()->query($sql,[$postData['user_email'],password_hash($postData['password'], PASSWORD_DEFAULT)]);
    //         $query=  db_connect()->query($sql,[$postData['user_email'],$postData['password']]);
    //          $data= $query->getResultArray();
    //          $responce['status'] = "0";
    //          $responce['message'] = "Details Not Found";
    //              if(isset($data) && count($data)>0){
    //                 $builder = db_connect()->table('ads_device_master');
    //                 $builder->join('ads_device_details', 'ads_device_details.device_id = ads_device_master.device_id');

    //                 $responce  = $builder->get()->getResultArray();
    //              }
    //             }
    //              return $this->respondCreated($responce);
    // }

    // public function get($type,$mode="web"){
    //     $postData= null;
    //     if($mode=="mobile"){
    //         $postData = json_decode(file_get_contents('php://input'), true);    
    //     }else{
    //         $postData = $_POST;
    //     }
    //     $responce['status'] = "0";
    //     $responce['data'] = [];
    //     $responce['message'] = "Request faild ";
    //     switch($type){
    //         case "contactUs":
    //         //  $sql = "select * from animal_contact_us ";
    //          $query=  db_connect()->query($sql);
    //          $data= $query->getResultArray();
    //          $responce['status'] = "0";
    //          $responce['message'] = "Details Not Found";
    //          if(isset($data) && count($data)>0){
    //                 $responce['status'] = "1";
    //                 $responce['message'] = "Details Found";
    //                 $responce['data'] = $data;
    //          }else{
    //                 $responce['data'] = [];
    //                 $responce['status'] = "0";
    //                 $responce['message'] = "No Contact Us Details Found";

    //             }
    //         break;
          
    //     }
    //     $responce['status_message']="0 Faild or error / 1 success";
    //     return $this->respondCreated($responce);
    // }
    public function post($type,$mode="web"){
        if($mode=="mobile"){
            $postData = json_decode(file_get_contents('php://input'), true);    
        }else{
            $postData = $_POST;
        }
        $responce['status'] = "0";
        $responce['message'] = "Request faild ";
        switch($type){
            case "contactUs":
                // name	email	message
            if(isset($postData['name']) && isset($postData['email']) && isset($postData['message'])){
             $db = db_connect();
             $builder = $db->table('animal_contact_us');
             $data['name']=$postData['name'];
             $data['email']=$postData['email'];
             $data['message']=$postData['message'];
                $builder->insert($data);
                $id = $db->insertID();
                 if($id>0){
                    $responce['status'] = "1";
                    $responce['message'] = "Details Save";
                    $responce['data'] = ['id'=>$id];
                 }
                }else{
                    $responce['message'] = "Parameters is missing";
                }
            break;
            
         
        }
        $responce['status_message']="0 Faild or error / 1 success";
        return $this->respondCreated($responce); 
    }
    public function registerDevice($type="web"){
               $responce['status'] = "0";
                    $responce['message'] = "Details not Saved";
                            $postData = $_REQUEST;
            // $postData = json_decode(file_get_contents('php://input'), true);    

        if(isset($type) && isset($postData) && isset($postData['device_details'])){
                $db = db_connect();
             $builder = $db->table('ads_device_master');
             $count = $builder->countAll();
            //  if($count<251){
             $builder->where("serial_no",$postData['serial_no']);
             $builder->where("device_uuid",$postData['device_uuid']);
             $temp = $builder->get()->getResultArray();
             $data['device_details']=$postData['device_details'];
             $data['device_name']=$postData['device_name'];
             $data['serial_no']=$postData['serial_no'];
             $data['device_uuid']=$postData['device_uuid'];
             if(isset($postData['full_device_details']))
             $data['full_device_details']=$postData['full_device_details'];
             if(count($temp)>0){
                      $responce['status'] = "1";
                    $responce['message'] = "Details Save";
             }else
                if($builder->replace($data)){
                       $responce['status'] = "1";
                    $responce['message'] = "Details Save";
                }
            //  }
        }
        $responce['status_message']="0 Faild or error / 1 success";
        // $responce['post_Data']=$postData;
        return $this->respondCreated($responce); 
    }
   
    public function AuthUser($type="web"){
        // $request = service('request');
        $postData = null;
        
        if($type=="mobile"){
            $postData = json_decode(file_get_contents('php://input'), true);
        }else{
            $postData = $_REQUEST;
        }
        $responce['status'] = "0";
        $responce['message'] = "Request faild ";
        if(isset($postData) && isset($postData['user_email']) && isset($postData['password']) && isset($postData['serial_no']) && $postData['serial_no']!=""){
            //user tab
             $sql = "select ads_customer.*,ads_customer_user.user_id from ads_customer left join ads_customer_user on ads_customer_user.customer_id=ads_customer.customer_id where customer_email = ? and customer_password = ? ";
            //  $query=  db_connect()->query($sql,[$postData['user_email'],password_hash($postData['password'], PASSWORD_DEFAULT)]);
            $query=  db_connect()->query($sql,[$postData['user_email'],$postData['password']]);
             $data= $query->getResultArray();
             $responce['status'] = "0";
             $responce['message'] = "Details Not Found";
                 if(isset($data) && count($data)>0){
                            $responce['status'] = "1";
                            $responce['customer_id'] = $data[0]['customer_id'];
                            $responce['user_id'] = $data[0]['user_id'];
                            $responce['user_type'] = "user";
                            $responce['type_id'] = "1";
                            $responce['message'] = "Details Found";
                            $responce['data'] =$data[0];
                 }else{
                    $data= null;
                     //client tab
                    $sql = "select * from ads_user where email_id = ? and user_password = ?";
                    //  $query=  db_connect()->query($sql,[$postData['user_email'],password_hash($postData['password'], PASSWORD_DEFAULT)]);
                    $query=  db_connect()->query($sql,[$postData['user_email'],$postData['password']]);
                    $data= $query->getResultArray();
                    if(isset($data) && count($data)>0){
                        $responce['status'] = "1";
                        $responce['user_id'] = $data[0]['user_id'];
                        $responce['customer_id'] = "0";
                        $responce['user_type'] = "admin";
                        $responce['type_id'] = "2";
                        $responce['message'] = "Details Found";
                        $responce['data'] =$data[0]; 
                    }
             }
             if($responce['status'] ==1){
                     $query=  db_connect()->query("select * from ads_customer_device,ads_device_master where ads_customer_device.user_id=? and ads_customer_device.status=1 and ads_customer_device.device_id=ads_device_master.device_id and ads_device_master.serial_no=?",[$responce['user_id'],$postData['serial_no']]);
                     $data1= $query->getResultArray();
                    $responce['temo']= $data1;
                    if(isset($data1) && count($data1)>0){
                        
                    }else{
                         $responce= [];
                         $responce['status'] = "0";
                         $responce['message'] = "DGPlay Serial no :- ".$postData['serial_no']." Device is not linked with login user ";
                    }
             }
        }
                $responce['status_message']="0 Faild or error / 1 success";
                return $this->respondCreated($responce);
    }
    public function getScreenList($type="web"){
          $postData = null;
        if($type=="mobile"){
            $postData = json_decode(file_get_contents('php://input'), true);
        }else{
            $postData = $_REQUEST;
        }
        $responce['status'] = "0";
        $responce['message'] = "Request faild details not found";
                        // return $this->respondCreated($responce);
                        //if(isset($postData['customer_id']) && isset($postData['customer_id']) && isset($postData['customer_id']) && isset($postData['customer_id']))
            if(isset($postData) && ((isset($postData['customer_id']) && ($postData['customer_id']!="0" ) )|| (isset($postData['user_id']) && $postData['user_id']!="0") )){
                if(($postData['customer_id']!="0" && $postData['customer_id']!="") ||($postData['user_id']!="0" && $postData['user_id']!="")){
                $db =db_connect();
                $builder = $db->table('ads_screen_master');
                if($postData['customer_id']!="0" && $postData['customer_id']!="")
                    $builder->where("customer_id",$postData['customer_id']);
                if($postData['user_id']!="0" && $postData['user_id']!="")
                    $builder->where("user_id",$postData['user_id']);
                $builder->where("status","1");
                $data = $builder->get()->getResultArray();
                    $responce['status'] = "1";
                    $responce['message'] = "Details Found";
                    $responce['data'] =$data;
                }
            }
                $responce['status_message']="0 Faild or error / 1 success";
                return $this->respondCreated($responce);

    }
    public function setScreenToDevice($type="web"){
          $postData = null;
        if($type=="mobile"){
            $postData = json_decode(file_get_contents('php://input'), true);
        }else{
            $postData = $_REQUEST;
        }
        //serial_no
        //screen_id
        //customer_id
        //user_id
        $responce['status'] = "0";
        $responce['message'] = "Request faild details not found";
                        // return $this->respondCreated($responce);
                        //ads_device_master
                        //serial_no
                        //screen_id
            if(isset($postData) && isset($postData['serial_no']) && ((isset($postData['customer_id']) && $postData['customer_id']!="0")|| (isset($postData['user_id']) && $postData['user_id']!="0") )){
                $db =db_connect();
                $builder = $db->table('ads_screen_master');
                if($postData['customer_id']!="0" && $postData['customer_id']!="")
                $builder->where("customer_id",$postData['customer_id']);
                if($postData['user_id']!="0" && $postData['user_id']!="")
                $builder->where("user_id",$postData['user_id']);
                $builder->where("screen_id",$postData['screen_id']);
                $data = $builder->get()->getResultArray();
                if(count($data) >0){
                    // $data[]
                    $ads_device_master = $db->table('ads_device_master');
                    $ads_device_master->where("serial_no",$postData['serial_no']);
                    $ads_device_data = $ads_device_master->get()->getResultArray();
                    
                    if(count($ads_device_data) > 0){
                        $ads_device_data= $ads_device_data[0];
                        $ads_screen_device = $db->table('ads_screen_device');
                        $ads_screen_device->delete(['device_id'=>$ads_device_data['device_id']]);
                        $ads_screen_device->insert(['screen_id'=>$data[0]['screen_id'],'device_id'=>$ads_device_data['device_id']]);
                    // $ads_screen_device->insert()     
                        $responce['status'] = "1";
                        $responce['message'] = "Device Details saved"; 
                    }else{
                        $responce['status'] = "0";
                        $responce['message'] = "Device Details not match";    
                    }
                    //$builder = $db->table('ads_device_master');
                    
                }else{
                    $responce['status'] = "0";
                    $responce['message'] = "Screen Details not match";
                    // $responce['data'] =$data; 

                }
                // ads_device_master
            }
                 $responce['status_message']="0 Faild or error / 1 success";
                return $this->respondCreated($responce);

    }
    public function getPlaylist($type="web"){
          $postData = null;
        if($type=="mobile"){
            $postData = json_decode(file_get_contents('php://input'), true);
        }else{
            $postData = $_REQUEST;
        }
        $responce['status'] = "0";
        $responce['message'] = "Request faild details not found";
                        // return $this->respondCreated($responce);
                        //serial_no
                        //screen_id
                        //customer_id
                        //user_id
            if(isset($postData)  && $postData['screen_id']  && $postData['serial_no'] && ((isset($postData['customer_id']) && $postData['customer_id']!="0")|| (isset($postData['user_id']) && $postData['user_id']!="0") )){
                $db =db_connect();
                 $sql = "select 
                 ads_media_master.media_id,
                 ads_media_master.media_type,
                 concat('".base_url()."','/',ads_media_master.media_url ) as media_url, ads_playlist_items.duration_sec,ads_playlist_items.loop_count from ads_screen_playlist, 
ads_media_master, 
ads_playlist_items,
ads_media_playlist where 
ads_media_master.status = 1 
and ads_media_playlist.status = 1
and ads_screen_playlist.status = 1
and ads_screen_playlist.status = 1
and ads_media_master.media_id = ads_playlist_items.media_id 
and ads_media_playlist.playlist_id = ads_playlist_items.playlist_id 
and ads_media_playlist.playlist_id = ads_screen_playlist.playlist_id 
and ads_screen_playlist.screen_id = ?";
                    $query= $db->query($sql, [$postData['screen_id']]);
                    $row = $query->getResultArray();
                //ads_media_master
                //ads_media_playlist
                //ads_playlist_items
                //ads_screen_device
                //ads_screen_master
                /*
                select ads_media_master.media_url, ads_playlist_items.duration_sec,ads_playlist_items.loop_count from ads_screen_playlist, 
ads_media_master, 
ads_playlist_items,
ads_media_playlist where 
ads_media_master.status = 1 
and ads_media_playlist.status = 1
and ads_screen_playlist.status = 1
and ads_screen_playlist.status = 1
and ads_media_master.media_id = ads_playlist_items.media_id 
and ads_media_playlist.playlist_id = ads_playlist_items.playlist_id 
and ads_media_playlist.playlist_id = ads_screen_playlist.playlist_id 
and ads_screen_playlist.screen_id = 1

*/
                // $ads_screen_playlist = $db->table('ads_screen_playlist');
                // $ads_screen_playlist->where("screen_id",$postData['screen_id']);
                // $data = $builder->get()->getResultArray();
                
                // // $builder = $db->table('ads_media_master');
                // $data = $builder->get()->getResultArray();
                    $data = $row;
                    $responce['status'] = "1";
                    $responce['message'] = "Details Found";
                    $responce['data'] =$data; 
            }
                     $responce['status_message']="0 Faild or error / 1 success";
                return $this->respondCreated($responce);

    }
    
    public function setDevice($serial_no,$customer_id,$user_id){
        // ads_customer_device
    }
    
    function generateRandomOTP($length = 4) {
    // $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
    public function setDeviceReport($type="web"){
          $postData = null;
        if($type=="mobile"){
            $postData = json_decode(file_get_contents('php://input'), true);
        }else{
            $postData = $_REQUEST;
        }
        if(isset($postData) && isset($postData['device_id']) && (isset($postData['screen_id']) && ($postData['customer_id']!="0" || $postData['user_id']!=0))){
            // if(isset($postData['device_type']) && isset($postData['device_details']))
                $responce['status'] = "0";
                $responce['message'] = "Request faild details not found";
                $tpost =  $postData;
                unset($tpost['count']);
                
                $db =db_connect();
                $device_reports = $db->table('ads_device_reports');
                             $device_reports->where($tpost);
                            $temp = $device_reports->get()->getResultArray();
                if(count($temp)==0){
                // $device_reports->set('field', 'field+1', false);
                    $postData['report_date']=date("Y-m-d",strtotime($postData['report_date']));
                   $device_reports->insert($postData);
                }else{
                    
                        $postData['report_date']=date("Y-m-d",strtotime($postData['report_date']));
                        $count = $postData['count'];
                        unset($postData['count']);
                        $device_reports->set('count', 'count+'.$count, false);
                        $device_reports->where($postData);
                        $device_reports->update();
        
                        // $device_reports->replace([
                        //         'device_id'=>$postData['device_id'],
                        //         'screen_id'=>$postData['screen_id'],
                        //         'report_date'=>$postData['report_date'],
                        //         'customer_id'=>$postData['customer_id'],
                        //         'count'=>$postData['count'],
                        //     ]);
                            
                        }
                            
                                $responce['status'] = "1";
                                $responce['report_duration'] = "600";
                                $responce['message'] = "Details saved";

                }
                        $responce['status_message']="0 Faild or error / 1 success";
                return $this->respondCreated($responce);

        
    }

    public function emailSend($setTo="",$setSubject="OTP",$message=""){
        helper('email_service');
        sendSystemEmail($setTo, $setSubject, $message);
    }
}
?>