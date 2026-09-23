<?php

namespace App\Controllers\Api;

use App\Models\CRUDBaseModel;
use App\Controllers\BaseController;

class GetApi extends BaseController {

    //0 Faild,  1 success  
    public function lmraFormDetails(){
        $db=db_connect();
        $data['result']="0";
        $data['msg']="Contact To System Admin";        
        if(isset($_POST['fcm_id']) && isset($_POST['user_id'])){
            //$where['status'] = "2";

            //$data ['lmra_category'] = $this->Base_Models->GetAllValues ( "alert_lmra_categorys",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4 " );

            //$data ['lmra_data'] = $this->Base_Models->GetAllValues ( "alert_lmra",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4  , GROUP_CONCAT(CONCAT('{lmra_text:\"', lmra_text, '\", lmra_options:\"',lmra_options,'\"}')) lmra_data " );
            $data ['lmra_data'] = $db->query( "SELECT *, 'Yes' as title1, 'No' as title2, 'NA' as title3, 'Sevirity' as title4, CONCAT('[',GROUP_CONCAT(CONCAT('{\"lmra_id\":\"', lmra_id ,'\",\"title1\":\"Yes\",\"titlew\":\"No\",\"title3\":\"NA\",\"title4\":\"Sevirity\",\"lmra_text\":\"', lmra_text, '\", \"lmra_options\":\"',lmra_options,'\"}')),']') as lmra_data FROM `alert_lmra` WHERE `status` = '2' group by lmra_category_id" )->getResultArray();
             

            $data['result']="1";
            $data['msg']="done";
                
        }
        echo json_encode($data);
        // echo $this->convertIntoBase64(json_encode($data));
    }
    public function lmraDetailList(){
        
        $db=db_connect();
        $data['result']="0";
        $data['msg']="Contact To System Admin";        
        if(isset($_POST['fcm_id']) && isset($_POST['user_id'])){
            $where['user_id'] =$_POST['user_id'];
            $role=$db->table( "alert_users")->where(array('user_id'=>$_POST['user_id']))->get()->getResultArray();
            
            if(isset($role[0]['user_designation']) && $role[0]['user_designation']=='Reporting manager'){
                 $where['user_id'] =$_POST['user_id'];
                  $data ['lmra_data'] = $db->query( " select * from alert_lmra_details where alert_lmra_details.user_id in(select user_id from alert_users where employee_reporting_to='".$role[0]['user_id'] ."')" )->getResultArray();
            }
            else{
                 $data ['lmra_data'] = $db->table( "alert_lmra_details")->where($where)->get()->getResultArray();
            }
            //$data ['lmra_category'] = $this->Base_Models->GetAllValues ( "alert_lmra_categorys",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4 " );

            //$data ['lmra_data'] = $this->Base_Models->GetAllValues ( "alert_lmra",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4  , GROUP_CONCAT(CONCAT('{lmra_text:\"', lmra_text, '\", lmra_options:\"',lmra_options,'\"}')) lmra_data " );
           
             

            $data['result']="1";
            $data['msg']="done";
                
        }
         echo json_encode($data);
        // echo $this->convertIntoBase64(json_encode($data));
    }
    public function getDashBoard(){

    }
    function nearmissDetailsList(){
        $db=db_connect();
        $data['result']="0";
        $data['msg']="Contact To System Admin";        
        if(isset($_POST['fcm_id']) && isset($_POST['user_id'])){
            //$where['user_id'] =$_POST['user_id'];

            //$data ['lmra_category'] = $this->Base_Models->GetAllValues ( "alert_lmra_categorys",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4 " );

            //$data ['lmra_data'] = $this->Base_Models->GetAllValues ( "alert_lmra",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4  , GROUP_CONCAT(CONCAT('{lmra_text:\"', lmra_text, '\", lmra_options:\"',lmra_options,'\"}')) lmra_data " );
            //
            $data ['near_miss_data'] = $db->query( "
            select alert_near_miss.* ,
            alert_users.user_name,
            (select count(*) from 
            alert_near_miss_chat 
            where 
            alert_near_miss_chat.user_id= ".$_POST['user_id']."
            and 
            alert_near_miss.near_miss_id=alert_near_miss_chat.near_miss_id and 
            alert_near_miss_chat.read_status=0
            ) as chat_unread_count
            from alert_near_miss 
            
            inner join alert_users on alert_users.user_id=alert_near_miss.user_id and 
            (alert_users.user_id =".$_POST['user_id']." or find_in_set(".$_POST['user_id'].",group_member_ids)) 
            ORDER BY near_miss_id DESC" )->getResultArray();

            $data['result']="1";
            $data['msg']="done";
                
        }
        echo json_encode($data);
        // echo $this->convertIntoBase64(json_encode($data));
    }
    function lmraCommentsList(){
        $db=db_connect();
        $data['result']="0";
        $data['msg']="Contact To System Admin";        
        if(isset($_POST['fcm_id']) && isset($_POST['user_id']) && isset($_POST['lmra_id'])){
            //$where['user_id'] =$_POST['user_id'];

            //$data ['lmra_category'] = $this->Base_Models->GetAllValues ( "alert_lmra_categorys",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4 " );

            //$data ['lmra_data'] = $this->Base_Models->GetAllValues ( "alert_lmra",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4  , GROUP_CONCAT(CONCAT('{lmra_text:\"', lmra_text, '\", lmra_options:\"',lmra_options,'\"}')) lmra_data " );
            $data ['lmra_comments_data'] = $db->query( "select alert_lmra_comments.*,alert_users.user_name,alert_users.user_email,alert_users.user_contact,alert_users.user_designation from alert_lmra_comments inner join alert_users on alert_users.user_id=alert_lmra_comments.user_id where alert_lmra_comments.lmra_id=".$_POST['lmra_id']." and alert_lmra_comments.status !=2" )->getResultArray();
            if(count($data ['lmra_comments_data'] )>0){
                        $data['result']="1";
                        $data['msg']="done";
            }else{
                        $data['result']="0";
                        $data['msg']="No comments !!";
            }
                
        }
        echo json_encode($data);
        // echo $this->convertIntoBase64(json_encode($data));
    }
    function nearmissEngineerList(){
        $db=db_connect();
        $data['result']="0";
        $data['msg']="Contact To System Admin";        
        if(isset($_POST['fcm_id']) && isset($_POST['user_id']) ){
            //$where['user_id'] =$_POST['user_id'];

            //$data ['lmra_category'] = $this->Base_Models->GetAllValues ( "alert_lmra_categorys",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4 " );

            //$data ['lmra_data'] = $this->Base_Models->GetAllValues ( "alert_lmra",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4  , GROUP_CONCAT(CONCAT('{lmra_text:\"', lmra_text, '\", lmra_options:\"',lmra_options,'\"}')) lmra_data " );
            $data ['users_list_data'] = $db->query( "select alert_users.user_id,alert_users.user_name,alert_users.user_email,alert_users.user_contact,alert_users.user_designation from alert_users where user_id !=".$_POST['user_id']." and status = 1" )->getResultArray();
            if(count($data ['users_list_data'] )>0){
                        $data['result']="1";
                        $data['msg']="done";
            }else{
                        $data['result']="0";
                        $data['msg']="Users Not Found !!";
            }
                
        }
        echo json_encode($data);
        // echo $this->convertIntoBase64(json_encode($data));
    }
    public function notifications(){
        $db=db_connect();
        $data['result']="0";
        $data['msg']="Contact To System Admin";        
        if(isset($_POST['fcm_id']) && isset($_POST['user_id']) ){
            //$where['user_id'] =$_POST['user_id'];

            //$data ['lmra_category'] = $this->Base_Models->GetAllValues ( "alert_lmra_categorys",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4 " );
//id, title, description
            //$data ['lmra_data'] = $this->Base_Models->GetAllValues ( "alert_lmra",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4  , GROUP_CONCAT(CONCAT('{lmra_text:\"', lmra_text, '\", lmra_options:\"',lmra_options,'\"}')) lmra_data " );
            $where['user_id']=$_POST['user_id'];

            $where['type']=0;
            $data ['notifications'] = $db->table( "alert_notifications")->where($where)->get()->getResultArray();
            if(count($data ['notifications'] )>0){
                        $data['result']="1";
                        $data['msg']="done";
            }else{
                        $data['result']="0";
                        $data['msg']="Notification Not Found !!";
            }
                
        }
        echo json_encode($data);
        // echo $this->convertIntoBase64(json_encode($data));
    }
    
    
    public function sds(){
        $db=db_connect();
        $data['result']="0";
        $data['msg']="Contact To System Admin";        
        if(isset($_POST['fcm_id']) && isset($_POST['user_id']) ){
           

            //$where['type']=0;
            $data ['data'] = $db->table( "alert_sds")->get()->getResultArray();
            if(count($data ['data'] )>0){
                        $data['result']="1";
                        $data['msg']="done";
            }else{
                        $data['result']="0";
                        $data['msg']="Notification Not Found !!";
            }
                
        }
        echo json_encode($data);
        // echo $this->convertIntoBase64(json_encode($data));
    }
    
    
function dashboard_data(){
    $db=db_connect();
    $data['result']="0";
    $data['msg']="Contact To System Admin";        
    if(isset($_POST['fcm_id']) && isset($_POST['user_id'])){
        
        
        $data['near_miss']=$db->query(" SELECT count(if(near_miss_category_text= 'Lost Time Injury',1,NULL)) as 'lti', 
                                                                        count(if(near_miss_category_text= 'Minor Injury',1,NULL)) as 'minor', 
                                                                        count(if(near_miss_category_text= 'Near Miss',1,NULL))as 'near_miss' 
                                                                        FROM `alert_near_miss` 
                                                                        where (user_id ='".$_POST['user_id']."' or find_in_set('".$_POST['user_id']."',group_member_ids))")->getResultArray();
        
        $temp2=array();
        if(count($data['near_miss'])>0){
              $temp2=array();
              foreach($data['near_miss'][0]as $key=>$value){
               $temp2[$key]=$value; 
              }
          }
           
           
        $data['chart_data']=$temp2;
        $data['id']=$_POST['user_id'];
        $data['result']="1";
        $data['msg']="done";
            
    }
     echo json_encode($data);
    // echo $this->convertIntoBase64(json_encode($data));
}
    
}
?>