<?php
namespace App\Controllers\Api;
use App\Models\CRUDBaseModel;
use App\Controllers\BaseController;
class PostApi extends BaseController {

    //0 Faild,  1 success  
    public function lmraFormDetails(){
        
        $db=db_connect();
        $data['result']="0";
        $data['msg']="Contact To System Admin";        

        if(isset($_POST['fcm_id']) && isset($_POST['user_id']) && $_POST['user_id']!=""){
                        $tdata['user_id']=$_POST['user_id'];
                        $tdata['lmra_project_name']=$_POST['customer_proj_name'];
                        $tdata['lmra_job_no']=$_POST['job_no'];
                        $tdata['lmra_location']=$_POST['location'];

                        $tdata['lmra_visit_date']=date('Y-m-d', strtotime($_POST['date']));
                        $tdata['lmra_visit_time']=$_POST['time'];
                        $tdata['lmra_json_data']=$_POST['data'];
                        $tdata['fcm_id']=$_POST['fcm_id'];
                        if(isset($_POST['task_to_be_perform']))
                        $tdata['task_to_be_perform']=$_POST['task_to_be_perform'];
                        if(isset($_POST['sta_data']))
                        $tdata['sta_data']=$_POST['sta_data'];
                        
                        $db->table('alert_lmra_details')->insert($data);
                        
            $user=$db->table('alert_users')->where(array('user_id'=>$_POST['user_id']) )->get()->getResultArray();  
              if(count($user)>0)  {
                 $user=$user[0];
                 
                 if(!empty($user['employee_reporting_to'])){
                    $mail_person= $db->table('alert_users')->where(array('user_id'=>$user['employee_reporting_to']))->get()->getResultArray();
                    $mail_person=$mail_person[0];
                    $email=$mail_person['user_email'];
                    if(!empty($mail_person['fcm_id'])){
                        $registration_ids=$mail_person['fcm_id'];
                        $user_id=$mail_person['user_id'];
                        $message='A new Lmra has been added ';
                        $title='New Lmra';
                        $this->pushNotification($registration_ids, $user_id,$message,$title);
                    }
                    if(isset($_POST['permission_required']) && $_POST['permission_required']==1){
                        $mail_person_heigher =$db->table("alert_users")->where(array('user_emp_country'=>$user['user_emp_country'] ,'user_designation'=>'Higher authority') )->get()->getResultArray();
                        // $mail_person_heigher=$this->Base_Models->GetAllValues('alert_users',array('user_emp_country'=>$user['user_emp_country'] , 'user_designation'=>'Higher authority') );
                        $mail_person_heigher=$mail_person_heigher[0];
                        $email_heigher=$mail_person_heigher['user_email'];
                        $heighr_messsage='a new lmra has been initiated job no:'.$_POST['job_no'];
                        $name=$mail_person['user_name'];
                        $this->send($email_heigher  , $name , $from_email=null,'Permision required for LMRA',$heighr_messsage) ;
                        sleep(4);
                    }
                 }
                 else{
                    
                    $mail_person=$db->table("alert_users")->where(array('user_emp_country'=>$user['user_emp_country'] ,'user_designation'=>'Higher authority') )->getResultArray();
                    //  $mail_person=$this->Base_Models->GetAllValues('alert_users',array('user_emp_country'=>$user['user_emp_country'] , 'user_designation'=>'Higher authority') );
                     if(count($mail_person)>0){
                         $mail_person=$mail_person[0];
                        $email=$mail_person['user_email'];
                     }
                 }
                 $subject='New Lmra';
                 $html='new Lmra Generated';//message

                 $name = "ALFALAVAL";
                 if(isset($email))
                $this->send($email  , $name , $from_email=null,$subject,$html) ;
              }
                
                
            $data['result']="1";
            $data['msg']="done";
                
        }
         echo json_encode($data);
        // echo $this->convertIntoBase64(json_encode($data));
    }
    public function test(){
        
        $this->send_email(63);  
        // $this->send_email(62); 
    }
    public function reminder_send(){
        $db=db_connect();
        $mail=$db->table("alert_mail")->where(array('email_reminder'=>1))->get()->getResultArray();
        // echo"<pre>";
        //  print_r($mail);
        //  exit();
//         
        foreach($mail as $key=>$value){
            $data=$this->send_email($value['mail_id']);
            sleep(100); 
        }
            
    }
    
    public function nearmissDetails(){
         
         $db=db_connect();
        // print_r($_POST);
        // print_r($_FILES);
        // $data['post'] = $_POST;
        // $data['file']=$_FILES;
        // $data['req']=$_REQUEST;
//        echo json_encode($data);
        $data['result']="0";
        $data['msg']="Contact To System Admin";        

        if(isset($_POST['fcm_id']) || isset($_POST['user_id']) && $_POST['user_id']!=""){
            $tdata['user_id']=$_POST['user_id'];
        $tdata['near_miss_category_text']=$_POST['category_of_incident'];
        $tdata['project_name']=$_POST['customer_proj_name'];
        $tdata['job_no']=$_POST['job_no'];
        
        $tdata['gps_location']=$_POST['exact_location'];
        $tdata['manual_location']=$_POST['location'];
        

        if(isset($_POST['near_miss_date']))
        $tdata['near_miss_date']=date('Y-m-d', strtotime($_POST['near_miss_date']));
        if(isset($_POST['near_miss_time']))
        $tdata['near_miss_time']=$_POST['near_miss_time'];
        
        if(isset($_POST['name_of_person']))
        $tdata['incident_person_name']=$_POST['name_of_person'];
        if(isset($_POST['designation']))
        $tdata['incident_person_designation']=$_POST['designation'];
        if(isset($_POST['age']))
        $tdata['incident_person_age']=$_POST['age'];
        if(isset($_POST['company']))
        $tdata['incident_person_company']=$_POST['company'];
        if(isset($_POST['shift']))
        $tdata['incident_person_sift']=$_POST['shift'];

        if(isset($_POST['task_activity']))
        $tdata['mode_of_work']=$_POST['task_activity'];

        // if(isset($_POST['task_activity']))
        // $tdata['task_activity']=$_POST['task_activity'];

        if(isset($_POST['nature_of_injury']))
        $tdata['nature_of_damage']=$_POST['nature_of_injury'];

        $tdata['body_part_affect']=0;
        if(isset($_POST['body_part_affect'])){
        $tdata['body_part_affect_details']=$_POST['body_part_affect'];
        $tdata['body_part_affect']=1;
        }

        if(isset($_POST['name_of_the_equipment_involved']))
        $tdata['involved_equipment_name']=$_POST['name_of_the_equipment_involved'];
        if(isset($_POST['description_briefly_how_the']))
        $tdata['description_of_occurred']=$_POST['description_briefly_how_the'];
        if(isset($_POST['what_could_have_happened']))
        $tdata['what_could_have_happened']=$_POST['what_could_have_happened'];
        if(isset($_POST['causes_of_incident']))
        $tdata['causes_of_incident']=$_POST['causes_of_incident'];

        if(isset($_POST['immediate_action_taken']))
        $tdata['immediate_action']=$_POST['immediate_action_taken'];
        if(isset($_POST['future_occurrence']))
        $tdata['future_occurrence']=$_POST['future_occurrence'];
        if(isset($_POST['hours']))
        $tdata['total_hours']=$_POST['hours'];
        if(isset($_POST['cost']))
        $tdata['total_cost']=$_POST['cost'];
        if(isset($_POST['near_miss_priority']))
            $tdata['near_miss_priority']=$_POST['near_miss_priority'];
        if(isset($_POST['priority_comment']))
            $tdata['priority_comment']=$_POST['priority_comment'];    
        

        //$tdata['user_id']=$_POST['user_id'];
        $tdata['images']="";
        
        if(isset($_FILES['file_1']) && $_FILES ['file_1'] ['error'] == 0){
            $exte = pathinfo( $_FILES ['file_1'] ['name'], PATHINFO_EXTENSION) ;
            if(strtolower($exte) == "jpeg" || strtolower($exte) == "jpg" || strtolower($exte) == "png"){
                $FILES['attach']=$_FILES['file_1'];
                $tdata['images'].=$this->saveFile($FILES).",";
            }
        }
        if(isset($_FILES['file_2']) && $_FILES ['file_2'] ['error'] == 0){
            $exte = pathinfo( $_FILES ['file_2'] ['name'], PATHINFO_EXTENSION) ;
            if(strtolower($exte) == "jpeg" || strtolower($exte) == "jpg" || strtolower($exte) == "png"){
                $FILES['attach']=$_FILES['file_2'];
                $tdata['images'].=$this->saveFile($FILES).",";
            }
       
        }

            $near_misss_id=$db->table("alert_near_miss")->where($tdata)->get()->getResultArray();
            $user=$db->table("alert_users")->where(array('user_id'=>$_POST['user_id']))->get()->getResultArray();  

              if(count($user)>0)  {
                 $user=$user[0];
                 
                 if(!empty($user['employee_reporting_to'])){
                    $mail_person= $db->table("alert_users")->where(array('user_id'=>$user['employee_reporting_to']) )->get()->getResultArray();
                    $mail_person=$mail_person[0];
                    $email=$mail_person['user_email'];
                    $_POST['group_member_ids']='';
                    $_POST['near_miss_id']=$near_misss_id;
                    $_POST['from_function']=1;
                    $_POST["user_id"].=','.$mail_person['user_id'];
                    $this->nearmissSaveEngineers();
                    if(!empty($mail_person['fcm_id'])){
                        $registration_ids=$mail_person['fcm_id'];
                        $user_id=$mail_person['user_id'];
                        $message='A new near miss has been added ';
                        $title='New Near-miss';
                        $this->pushNotification($registration_ids, $user_id,$message,$title);
                    }
                    
                    
                 }
                 else{
                     $mail_person=$db->table("alert_users")->where(array('user_emp_country'=>$user['user_emp_country'] ,'user_designation'=>'Higher authority') )->get()->getResultArray();
                     
                     if(count($mail_person)>0){
                        $mail_person=$mail_person[0];
                        $email=$mail_person['user_email'];
                         
                     }
                     
                 }
                 $subject='New Near-Miss';
                 $html='new Near-Miss Generated1';//message
                 $name = "ALFALAVAL";
                 if(isset($email))
                $abc=$this->send($email  , $name , $from_email=null,$subject,$html) ;
 
              }
        $data['result']="1";
        $data['msg']="done";
        
    }
    echo json_encode($data);
    // echo $this->convertIntoBase64(json_encode($data));
    }
public function saveFile($FILES){
        $date = new DateTime ();
        $date->setTimezone ( new DateTimeZone ( "Asia/Kolkata" ) );

    if (isset ( $FILES ['attach'] ) && $FILES ['attach'] ['error'] == 0) {
        $exte = pathinfo( $_FILES ['attach'] ['name'], PATHINFO_EXTENSION) ;
       
        if(strtolower($exte) == "jpeg" || strtolower($exte) == "jpg" || strtolower($exte) == "png" ){
            $responce['msg']="done";
            //  $temp = "uploads/attachment_images/question_images-" . $this->generate_random_string ( 10 );
            $exte = pathinfo( $FILES ['attach'] ['name'], PATHINFO_EXTENSION) ;
            //          $filename = $_FILES ['attach'] ['name'] ;
            //            $ext = end(explode('.', $filename ));
            //echo $ext;
            list ( $a, $b ) = explode ( '.',$FILES ['attach'] ['name'] );
            $b =$exte;
              $temp = "uploads/nearmiss/attachment_images";
            $image_folder = APPPATH . "../" . $temp;
            if (!file_exists($image_folder)) {
              mkdir($image_folder, 0777, true);
            }
            $image_name = "nearmiss_attachment_".rand().$date->format('YmdHis').rand().".".$b;
            $image_folder = $image_folder."/".$image_name;
           
            move_uploaded_file($FILES ['attach'] ['tmp_name'],$image_folder);
            $attach_url = $temp."/".$image_name;
            $para = null;
            $para['title'] = "Attach One";
            $para['url'] = base_url( $attach_url);
            $para['status'] = 1;
    //       $this->Base_Models->AddValues($attachTable,$para);  
            return  base_url( $attach_url);
        } 
    } 
    return  "error";
}
function lmraSaveComment(){
    $db=db_connect();
    $data['result']="0";
    $data['msg']="Contact To System Admin";        
    if(isset($_POST['fcm_id']) && isset($_POST['user_id']) && isset($_POST['lmra_id'])){
        //$where['user_id'] =$_POST['user_id'];
        $tdata['user_id']=$_POST['user_id'];
        $tdata['fcm_id']=$_POST['fcm_id'];
        $tdata['lmra_id']=$_POST['lmra_id'];
        $tdata['comment_text']=$_POST['comment'];
   
        //$data ['lmra_category'] = $this->Base_Models->GetAllValues ( "alert_lmra_categorys",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4 " );

        //$data ['lmra_data'] = $this->Base_Models->GetAllValues ( "alert_lmra",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4  , GROUP_CONCAT(CONCAT('{lmra_text:\"', lmra_text, '\", lmra_options:\"',lmra_options,'\"}')) lmra_data " );
        //$data ['lmra_comments_data'] = $this->Base_Models->CustomeQuary ( "select alert_lmra_comments.*,alert_users.user_name,alert_users.user_email,alert_users.user_contact,alert_users.user_designation from alert_lmra_comments inner join alert_users on alert_users.user_id=alert_lmra_comments.user_id where alert_lmra_comments.lmra_id=".$_POST['lmra_id'] );
         $db->table("alert_lmra_comments")->insert($tdata);
        // $this->Base_Models->AddValues("alert_lmra_comments",$tdata);
        $data['result']="1";
        $data['msg']="done";
            
    }
    echo json_encode($data);
    // echo $this->convertIntoBase64(json_encode($data));
}

 

function nearmissSaveEngineers(){
     $db=db_connect();
    $data['result']="0";
    $data['msg']="Contact To System Admin";        
    if(isset($_POST['fcm_id']) && isset($_POST['user_id']) && isset($_POST['near_miss_id']) && isset($_POST['group_member_ids'])){
        //$where['user_id'] =$_POST['user_id'];
        //$where['user_id']=$_POST['user_id'];
        //$tdata['fcm_id']=$_POST['fcm_id'];
        
        $where['near_miss_id']=$_POST['near_miss_id'];
        $tdata['group_member_ids']=$_POST["user_id"].",".$_POST['group_member_ids'];
        $tdata['group_member_count']=count(explode(",",$_POST['group_member_ids']));
                
        //$data ['lmra_category'] = $this->Base_Models->GetAllValues ( "alert_lmra_categorys",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4 " );

        //$data ['lmra_data'] = $this->Base_Models->GetAllValues ( "alert_lmra",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4  , GROUP_CONCAT(CONCAT('{lmra_text:\"', lmra_text, '\", lmra_options:\"',lmra_options,'\"}')) lmra_data " );
        //$data ['lmra_comments_data'] = $this->Base_Models->CustomeQuary ( "select alert_lmra_comments.*,alert_users.user_name,alert_users.user_email,alert_users.user_contact,alert_users.user_designation from alert_lmra_comments inner join alert_users on alert_users.user_id=alert_lmra_comments.user_id where alert_lmra_comments.lmra_id=".$_POST['lmra_id'] );
        
        $db->table("alert_near_miss")->update($tdata,$where);
        //  $this->Base_Models->UpadateValue("alert_near_miss",$tdata,$where);
        $data['result']="1";
        $data['msg']="done";
            
    }
    if(isset($_POST['from_function']) && $_POST['from_function']==1)
        return 1;
    else{
         echo json_encode($data);
        // echo $this->convertIntoBase64(json_encode($data));
    }
}
function sendEmail(){
    $db=db_connect();
    $data['result']="0";
    $data['msg']="Contact To System Admin";        
   
if(isset($_POST['fcm_id']) && isset($_POST['user_id']) && isset($_POST['near_miss_id']) ){
    
    //Array ( [receiver_email] => surajk4437@gmail.com [subject] => Test ICO [body] => TEST [doc_id] => 34 [doc_category] => 1 )
    
    $data['result']="1";
    $data['msg']="done";
    //$data['post']=print_r($_POST,true);
    
    $post_data['from_email']=$db->table("alert_users")->where(array('user_id'=>$_POST['user_id']),'user_email')[0]['user_email']->get()->getResultArray();
    $post_data['receiver_email']=$_POST['email'];
    $post_data['receiver_email']=$_POST['email'];
    $post_data['subject']=$_POST['subject'];
    $post_data['body']=$_POST['body'];
    $post_data['doc_id']=$_POST['near_miss_id'];
    $post_data['email_reminder']=$_POST['email_reminder'];
    $post_data['allow_feedback']=$_POST['allow_feedback'];
    $post_data['doc_category']="1";
    $post_data['user_id']=$_POST['user_id'];
    $nearmiss= $db->query(" select * ,GROUP_CONCAT(user_email) as emails from alert_users where FIND_IN_SET (user_id,(select group_member_ids from alert_near_miss where near_miss_id =".$_POST['near_miss_id']." )) ")->getResultArray();
    $cc = explode(",",$nearmiss[0]['emails']);
    unset($_POST);
    $_POST = $post_data;
    //$this->email_send_api(null,$post_data);
    $data['responce'] = $this->send_email(null,$cc);
    
                 
     // 0 lmra 1 near-miss
}
echo json_encode($data);
// echo $this->convertIntoBase64(json_encode($data));
}
function request_for_close_near_miss(){
    $db=db_connect();
    $data['result']="0";
    $data['msg']="faild";

    if(isset($_POST['fcm_id']) && isset($_POST['user_id']) && isset($_POST['near_miss_id']) && isset($_POST['description'])  ){
        // if(isset($_FILES['attach']))
        $image = "";
        if(isset($_FILES['image']) && $_FILES['image'] ['error'] == 0){
            $exte = pathinfo( $_FILES ['image'] ['name'], PATHINFO_EXTENSION);
            
            if(strtolower($exte) == "jpeg" || strtolower($exte) == "jpg" || strtolower($exte) == "png" ){
                $_FILES['attach']=$_FILES['image'];
                $image=$this->saveFile($_FILES);
            }
        }
        $para['chat_text']=$_POST['description'];
        $para['chat_image']=$image;
        $para['chat_type']=1;
        $para['user_id']=$_POST['user_id'];
        $para['near_miss_id']=$_POST['near_miss_id'];
        $db->table("alert_near_miss_chat")->insert($para);
        
        $db>query('UPDATE alert_near_miss
                                            SET images = concat(images,",'.$image.'" ),
                                                 status=2
                                             WHERE near_miss_id ='.$_POST['near_miss_id'])->getResultArray();
        $data['result']="1";
        $data['msg']="done";
        


    }
     echo json_encode($data);
    //  echo $this->convertIntoBase64(json_encode($data));
}





function approve_nearmiss(){
    $db=db_connect();
    $data['result']="0";
    $data['msg']="Contact To System Admin";        
    if(isset($_POST['fcm_id']) && isset($_POST['user_id']) && isset($_POST['near_miss_id']) && isset($_POST['status'])){
        //status 1 active
        // status 3 close
        $where['near_miss_id']=$_POST['near_miss_id'];
        $tdata['status']=$_POST['status'];
       
        $db->table("alert_near_miss")->update($tdata,$where);
        $data['result']="1";
        $data['msg']="done";
            
    }
    if(isset($_POST['from_function']) && $_POST['from_function']==1)
        return 1;
    else{
        echo json_encode($data);
        // echo $this->convertIntoBase64(json_encode($data));
    }
}
function approve_lmra(){
    $db=db_connect();
    $data['result']="0";
    $data['msg']="Contact To System Admin";        
    if(isset($_POST['fcm_id']) && isset($_POST['user_id']) && isset($_POST['lmra_details_id']) && isset($_POST['status'])){
        //status 1 active
        // status 3 close
        $where['lmra_details_id']=$_POST['lmra_details_id'];
        $tdata['status']=$_POST['status'];
       
        $db->table("alert_lmra_details")->where($tdata,$where)->get()->getResultArray();
        $data['result']="1";
        $data['msg']="done";
            
    }
    if(isset($_POST['from_function']) && $_POST['from_function']==1)
        return 1;
    else{
     echo json_encode($data);
        // echo $this->convertIntoBase64(json_encode($data));
    }
}

}
?>
