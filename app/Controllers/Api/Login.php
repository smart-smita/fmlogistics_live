<?php
namespace App\Controllers\Api;
use App\Models\CRUDBaseModel;
use App\Controllers\BaseController;
class Login extends BaseController {
	
  public function authentication(){
      $db=db_connect();
    $response['result']="0";
    $response['msg']="Parameter not match";
    
    if(isset($_POST['userId']) && $_POST['userId'] != null && $_POST['userId'] != '' ){
      $_POST['user_id'] = $_POST['userId'];
    }
   //
    if( isset($_POST['password'])  && $_POST['password'] != null && $_POST['password'] != '' && isset($_POST['fcm_id']) && isset($_POST['user_id']) && $_POST['user_id'] != null && $_POST['user_id'] != '' ){
       $response['result'] = "0";
       $response['msg'] = "User Id not found !!!";
      
       $query = "select * from alert_users where (user_email = '".$_POST['user_id']."' or user_contact = '".$_POST['user_id']."') and user_password = '".$_POST['password']."' and status != 2";
       $userDataArray = $db->query($query)->getResultArray();
 
       if($userDataArray != null && count($userDataArray) > 0){
           
             $userData = $userDataArray[0];
             
             if ($userData['status'] != 1 && $userData['status'] !== '1') {
                 $response['result'] = "0";
                 $response['msg'] = "Your account is not active. Please contact the administrator.";
                 return $this->response->setJSON($response);
             }

             $response['data'] = $userData;
             $tableName = "alert_users";
             $datetime = new DateTime();
             $updatePara['last_date'] = $datetime->format('Y-m-d H:i:sP');
             $where['user_id'] = $userData['user_id'];
             $para['status']="2"; // manual set suraj
          if($userData['fcm_id'] != $_POST['fcm_id']){
             $updatePara['fcm_id'] = $_POST['fcm_id']; 
             $db->table($tableName)->where($where)->set($updatePara)->update();
            //  $db->table($tableName)->where($where)->update($updatePara);
             $response['result']="2";
             $response['msg'] = "Pending for device verification.";   
            $deviceDetailsTable = "alert_users_devices";
            $para['user_id'] = $userData['user_id'];    
            $para['fcm_id'] = $_POST['fcm_id'];

            if(isset($_POST['imei_no']) && $_POST['imei_no'] != null && $_POST['imei_no'] != ''){
              $para['imei_no'] = $_POST['imei_no'];
            }else{
              $para['imei_no'] = "unknown";
            }

            if(isset($_POST['product']) && $_POST['product'] != null && $_POST['product'] != ''){
              $para['product'] = $_POST['product'];
            }else{
              $para['product'] = "unknown";
            }

            if(isset($_POST['mobile_no']) && $_POST['mobile_no'] != null && $_POST['mobile_no'] != ''){
              $para['mobile_no'] = $_POST['mobile_no'];
            }else{
              $para['mobile_no'] = "unknown";
            }

            if(isset($_POST['manufacture']) && $_POST['manufacture'] != null && $_POST['manufacture'] != ''){
              $para['manufacture'] = $_POST['manufacture'];
            }else{
              $para['manufacture'] = "unknown";
            }

            if(isset($_POST['language']) && $_POST['language'] != null && $_POST['language'] != ''){
              $para['language'] = $_POST['language'];
            }else{
              $para['language'] = "unknown";
            }

            if(isset($_POST['hardware']) && $_POST['hardware'] != null && $_POST['hardware'] != ''){
              $para['hardware'] = $_POST['hardware'];
            }else{
              $para['hardware'] = "unknown";
            }

            if(isset($_POST['all_device_details']) && $_POST['all_device_details'] != null && $_POST['all_device_details'] != ''){
              $para['all_device_details'] = $_POST['all_device_details'];
            }else{
              $para['all_device_details'] = "unknown";
            }

            $temp = $db->table($deviceDetailsTable)->where($para)->getResultArray(); 
            if(count($temp)==0){
              $db->table($deviceDetailsTable)->insert($para); 
            }else{
              if($temp[0]['status']=="2"){
                $temp2=array('lti'=>0,'near_miss'=>0,'minor'=>0);
                  // $data['near_miss']=$this->Base_Models->CustomeQuary("SELECT count(status) as counts ,near_miss_category_text  FROM `alert_near_miss` GROUP by near_miss_category_text");
                
               if($userData['user_id']=='Higher authority'){
                //   $data['near_miss']=$this->Base_Models->CustomeQuary("SELECT count(status) as counts ,near_miss_category_text ,( if(near_miss_category_text= 'Lost Time Injury','lti', if(near_miss_category_text= 'Minor Injury','minor', if(near_miss_category_text= 'Near Miss','near_miss',''))) )as name FROM `alert_near_miss` where (user_id ='".$userData['user_id']."' or find_in_set('".$userData['user_id']."',group_member_ids)) GROUP by near_miss_category_text");
               }else{
                   //$data['near_miss']=$this->Base_Models->CustomeQuary("SELECT count(status) as counts ,near_miss_category_text ,( if(near_miss_category_text= 'Lost Time Injury','lti', if(near_miss_category_text= 'Minor Injury','minor', if(near_miss_category_text= 'Near Miss','near_miss',''))) )as name FROM `alert_near_miss` where (user_id ='".$userData['user_id']."' or find_in_set('".$userData['user_id']."',group_member_ids)) GROUP by near_miss_category_text");
               }
                    $data['near_miss']=$db->query(" SELECT count(if(near_miss_category_text= 'Lost Time Injury',1,NULL)) as 'lti', 
                                                                        count(if(near_miss_category_text= 'Minor Injury',1,NULL)) as 'minor', 
                                                                        count(if(near_miss_category_text= 'Near Miss',1,NULL))as 'near_miss' 
                                                                        FROM `alert_near_miss` 
                                                                        where (user_id ='".$userData['user_id']."' or find_in_set('".$userData['user_id']."',group_member_ids))")->getResultArray();
               
               
                if(count($data['near_miss'])>0){
                      $temp2=array();
                      foreach($data['near_miss'][0]as $key=>$value){
                       $temp2[$key]=$value; 
                      }
                  }
                $response['chart_data']=$temp;
                $response['result']="1";
                $response['id']=$userData['user_id'];
                
                $response['msg'] = "Login Successfully."; 
                $db->table($tableName)->where($where)->set($updatePara)->update();
                // $db->table($tableName)->where($where)->update($updatePara);
                // $this->Base_Models->UpadateValue($tableName,$updatePara,$where);
              }else{
                $updatePara=null;
                $updatePara['status']="0";
                $db->table($deviceDetailsTable)->where($where)->set($updatePara)->update();
                // $db->table($deviceDetailsTable)->where($where)->update($updatePara);

                // $this->Base_Models->UpadateValue($deviceDetailsTable,$updatePara,$where);
    
              }
            }
          }else{

           $temp2=array('lti'=>0,'near_miss'=>0,'minor'=>0);
          // $data['near_miss']=$this->Base_Models->CustomeQuary("SELECT count(status) as counts ,near_miss_category_text  FROM `alert_near_miss` GROUP by near_miss_category_text");
        //   $data['near_miss']=$this->Base_Models->CustomeQuary("SELECT 
        //                                     count(status) as counts ,
        //                                     near_miss_category_text ,
        //                                     ( if(near_miss_category_text= 'Lost Time Injury','lti', if(near_miss_category_text= 'Minor Injury','minor', if(near_miss_category_text= 'Near Miss','near_miss',''))) )as name FROM `alert_near_miss` where (user_id ='".$userData['user_id']."' or find_in_set('".$userData['user_id']."',group_member_ids)) GROUP by near_miss_category_text");
          
          
         $data['near_miss']=$db->query(" SELECT count(if(near_miss_category_text= 'Lost Time Injury',1,NULL)) as 'lti', 
                                                                        count(if(near_miss_category_text= 'Minor Injury',1,NULL)) as 'minor', 
                                                                        count(if(near_miss_category_text= 'Near Miss',1,NULL))as 'near_miss' 
                                                                        FROM `alert_near_miss` 
                                                                        where (user_id ='".$userData['user_id']."' or find_in_set('".$userData['user_id']."',group_member_ids))")->getResultArray();
          if(count($data['near_miss'])>0){
              $temp2=array();
              foreach($data['near_miss'][0]as $key=>$value){
               $temp2[$key]=$value; 
              }
          }
           
           
           $response['chart_data']=$temp2;
           $response['id']=$userData['user_id'];
            $response['result']="1";
             $response['msg'] = "Login Successfully.";
          }           

       }

    }     
     echo "".json_encode($response);
    // echo $this->convertIntoBase64(json_encode($response));
  } 
 
}
?>