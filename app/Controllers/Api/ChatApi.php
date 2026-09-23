<?php
namespace App\Controllers\Api;

use App\Models\CRUDBaseModel;
use App\Controllers\BaseController;

class ChatApi extends BaseController{
   
    public function uploadImage(){
        $responce['result']="1";
        $responce['msg']="fail";
        $date = new DateTime ();
        $date->setTimezone ( new DateTimeZone ( "Asia/Kolkata" ) );

        if (isset ( $_FILES ['attach'] ) && $_FILES ['attach'] ['error'] == 0) {
            $responce['msg']="done";
            //  $temp = "uploads/attachment_images/question_images-" . $this->generate_random_string ( 10 );
            $exte = pathinfo( $_FILES ['attach'] ['name'], PATHINFO_EXTENSION) ;
            if(strtolower($exte) == "jpeg" || strtolower($exte) == "jpg" || strtolower($exte) == "png" ){
//            $filename = $_FILES ['attach'] ['name'] ;
//            $ext = end(explode('.', $filename ));
//echo $ext;


        list ( $a, $b ) = explode ( '.',$_FILES ['attach'] ['name'] );
            $b =$exte;
              $temp = "uploads/chat/attachment_images";
$image_folder = APPPATH . "../" . $temp;
          if (!file_exists($image_folder)) {
              mkdir($image_folder, 0777, true);
           }
           $image_name = "chat_attachment_".$date->format('YmdHis').".".$b;
           $image_folder = $image_folder."/".$image_name;
           
    move_uploaded_file($_FILES ['attach'] ['tmp_name'],$image_folder);
$attach_url = $temp."/".$image_name;
        $para = null;
        $para['title'] = "Attach One";
        $para['url'] = base_url( $attach_url);
        $para['status'] = 1;
//       $this->Base_Models->AddValues($attachTable,$para);  
$responce['file_path'] =  base_url( $attach_url);
if(isset($_POST['fcm_id']) && isset($_POST['user_id']) && isset($_POST['near_miss_id']) && isset($_POST['chat_text']) && $_POST['near_miss_id']!=""){

//     $fcmids=array();
//     $user_data = $this->Base_Models->CustomeQuary("
//     SELECT alert_users.fcm_id,alert_users.user_id ,alert_near_miss.group_member_ids
//     from alert_near_miss 
//     inner join 
//     alert_users on 
//     (alert_users.user_id=alert_near_miss.user_id or find_in_set( alert_users.user_id,alert_near_miss.group_member_ids) ) and 
//     alert_near_miss.near_miss_id = ".$_POST['near_miss_id']."
//       ");
//     $tdata = null;
//     $tdata['chat_text']=$_POST['chat_text'];
//     $tdata['near_miss_id']=$_POST['near_miss_id'];    
//     $tdata['user_id']=$_POST['user_id'];

// $group_ids="";
// if(count($user_data)>0){
//     $group_ids=$user_data[0]['group_member_ids'];
// }
// $tdata['group_user_ids']=$group_ids;
// $this->Base_Models->AddValues("alert_near_miss_chat",$tdata);

//         for($i=0;$i<count($user_data);$i++){
//             $fcmids[$i]=$user_data[$i]['fcm_id'];
// //send message function         
// //
//         }


//start 
$db=db_connect();
$fcmids=array();
        $user_data = $db->query("
        SELECT alert_users.fcm_id,alert_users.user_name,if (alert_users.user_id=".$_POST['user_id'].",alert_users.user_name,'') as title
        ,alert_users.user_id ,alert_near_miss.group_member_ids,
        alert_near_miss.user_id as n_user_id
        from alert_near_miss 
        inner join 
        alert_users on 
        (alert_users.user_id=alert_near_miss.user_id or find_in_set( alert_users.user_id,alert_near_miss.group_member_ids) ) and 
        alert_near_miss.near_miss_id = ".$_POST['near_miss_id']."
        order by title DESC
        ")->getResultArray();
        $tdata['chat_text']=$_POST['chat_text'];
        $tdata['near_miss_id']=$_POST['near_miss_id'];
        $tdata['user_id']=$_POST['user_id'];
        $tdata['chat_type']="1";
        $tdata['chat_image']=$responce['file_path'];
        $title ="";
        $group_ids="";
        if(count($user_data)>0){
            $group_ids=$user_data[0]['group_member_ids'];
            $title = $user_data[0]['title'];
            if($_POST['user_id']!=$user_data[0]['n_user_id']){
                $group_ids .=",".$user_data[0]['n_user_id'];
            }
        }

        //$_POST['user_id']
        $tdata['group_user_ids']=$group_ids;
        $db->table("alert_near_miss_chat")->where($tdata)->get()->getResultArray();
        $userids=array();
                for($i=0;$i<count($user_data);$i++){
                    if($_POST['user_id']!=$user_data[$i]['user_id']){
                        array_push($fcmids,$user_data[$i]['fcm_id']);
                        //$fcmids[$i]=$user_data[$i]['fcm_id'];
                        array_push($userids,$user_data[$i]['user_id']);
                        //$userids[$i]=$user_data[$i]['user_id'];
                    }
        //send message function         
        //
                }
        
        $fcmdata['img']=$responce['file_path'];
        $fcmdata['near_miss_id']=$_POST['near_miss_id'];
        $data['fcm_responce']=$this->pushNotification($fcmids, $userids,$_POST['chat_text'],$title,"1",$fcmdata);;
// print_r($userids);
// print_r( $fcmdata);

// print_r($data);
// exit();

//end 

    $data['result']="1";
    $data['msg']="done";
   
    //(alert_users.user_id =".$_POST['user_id']." or find_in_set(".$_POST['user_id'].",group_member_ids))

}
}
}

    echo json_encode($responce);
    // echo $this->convertIntoBase64(json_encode($response));
    }
    
    
public function chatList(){
    $db=db_connect();
    $data['result']="0";
    $data['msg']="Contact To System Admin";        
    if(isset($_POST['fcm_id']) && isset($_POST['user_id']) && isset($_POST['near_miss_id'])){
        //$where['status'] = "2";

        //$data ['lmra_category'] = $this->Base_Models->GetAllValues ( "alert_lmra_categorys",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4 " );

        //$data ['lmra_data'] = $this->Base_Models->GetAllValues ( "alert_lmra",$where,"*,'Yes' as title1 , 'No' as title2,'NA' as title3, 'Sevirity' as title4  , GROUP_CONCAT(CONCAT('{lmra_text:\"', lmra_text, '\", lmra_options:\"',lmra_options,'\"}')) lmra_data " );
        $data ['near_miss_chat_data'] = $db->query(   "select alert_near_miss_chat.*,alert_users.user_name,alert_users.user_email,alert_users.user_contact,alert_users.user_designation from alert_near_miss_chat inner join alert_users on alert_users.user_id=alert_near_miss_chat.user_id where alert_near_miss_chat.near_miss_id=".$_POST['near_miss_id']." and alert_near_miss_chat.status !=2 and (find_in_set(".$_POST['user_id'].",alert_near_miss_chat.group_user_ids) or alert_near_miss_chat.user_id=".$_POST['user_id'].") ORDER BY alert_near_miss_chat.chat_id ASC" )->getResultArray();
        if(count($data ['near_miss_chat_data'] )>0){
            $data['result']="1";
            $data['msg']="done";
            $where['near_miss_id']=$_POST['near_miss_id'];
            $where['user_id']=$_POST['user_id'];
            $tdata['read_status']=1;
                    
            $db->table("alert_near_miss_chat")->where($where)->set($tdata)->update();

}else{
            $data['result']="0";
            $data['msg']="No message !!";
}
            
    }
    echo json_encode($data);
        // echo $this->convertIntoBase64(json_encode($data));
}
public function saveChat(){
    $db=db_connect();
    $data['result']="0";
    $data['msg']="Contact To System Admin";        
    if(isset($_POST['fcm_id']) && isset($_POST['user_id']) && isset($_POST['near_miss_id']) && isset($_POST['chat_text']) && $_POST['near_miss_id']!=""){

        $fcmids=array();
        $user_data = $db->query("
        SELECT alert_users.fcm_id,alert_users.user_name,if (alert_users.user_id=".$_POST['user_id'].",alert_users.user_name,'') as title
        ,alert_users.user_id ,alert_near_miss.group_member_ids,
        alert_near_miss.user_id as n_user_id
        from alert_near_miss 
        inner join 
        alert_users on 
        (alert_users.user_id=alert_near_miss.user_id or find_in_set( alert_users.user_id,alert_near_miss.group_member_ids ) ) and alert_users.fcm_id !='device_change' and alert_users.fcm_id !='' and 
        alert_near_miss.near_miss_id = ".$_POST['near_miss_id']."
        order by title DESC
        ")->getResultArray();
        $tdata['chat_text']=$_POST['chat_text'];
        $tdata['near_miss_id']=$_POST['near_miss_id'];
        $tdata['user_id']=$_POST['user_id'];
        $title ="";
$group_ids="";
if(count($user_data)>0){
    $group_ids=$user_data[0]['group_member_ids'];
    $title = $user_data[0]['title'];
    if($_POST['user_id']!=$user_data[0]['n_user_id']){
        $group_ids .=",".$user_data[0]['n_user_id'];
    }
}

//$_POST['user_id']
$tdata['group_user_ids']=$group_ids;
$db->table("alert_near_miss_chat")->where($tdata)->get()->getResultArray();
$userids=array();
        for($i=0;$i<count($user_data);$i++){
            if($_POST['user_id']!=$user_data[$i]['user_id']){
                array_push($fcmids,$user_data[$i]['fcm_id']);
                //$fcmids[$i]=$user_data[$i]['fcm_id'];
                array_push($userids,$user_data[$i]['user_id']);
                //$userids[$i]=$user_data[$i]['user_id'];
            }
//send message function         
//
        }
        $fcmdata['img']="";
        $fcmdata['near_miss_id']=$_POST['near_miss_id'];
        
        //print_r ($user_data);
        $this->pushNotification($fcmids, $userids,$_POST['chat_text'],$title,"1",$fcmdata);

        $data['result']="1";
        $data['msg']="done";
        // $registration_ids=$fcmids;
        // $message=$tdata['chat_text'];
        // $type="";
        // $data['img']="";
        // $this->pushNotification($registration_ids, $message,$title);
        //(alert_users.user_id =".$_POST['user_id']." or find_in_set(".$_POST['user_id'].",group_member_ids))

    }
    echo json_encode($data);
    // echo $this->convertIntoBase64(json_encode($data));
}


//alert_near_miss_chat insert value 
//sendMessage

public function sendMessage(){

//alert_notifications
//
        
}
	public function test_notification($fcm_id="f0IT1qkHd_o:APA91bHfp8ai2L8uNbrxdpRr-pTkQePIV5uHVAFkwF4WA2REXd2eRv-CoLqHnupa-Y1A5FuotCYZcCoyUQHud0eQUhlYwncplVcuqAO5PBfKtZ_DheTOYoqrtXFWnS7qQIts63_CZ-_Q"){
	    $data['type_text']="1";
	    $data['img']="";
	    $data['near_miss_id']="43";
	    $this->pushNotification($fcm_id, "1","DEMO BODY - ".date("M,d,Y h:i:s A") ,"title");
	   
	}

}
?>