<?php
namespace App\Controllers\Api;
use App\Models\CRUDBaseModel;
use App\Controllers\BaseController;
class Test extends BaseController {

    public function test(){
        
        $subject='login credentials';
            $mail_email='';//$_POST['user_email'];
            $_POST['user_password']='';
            $_POST['user_email']='';
            $tag=array(
                    '{{contact_email}}', '{{contact_no}}', '{{button_title}}', '{{redirection_url}}',
                    '{{user_note}}', '{{user_password_value}}', '{{user_password_wording}}', '{{user_name_value}}',
                    '{{user_name_wording}}', '{{message_tag1}}', '{{welcome_note_line2}}', '{{welcome_note_line1}}', '{{main_logo}}'
                    );
                $replace=array('help@unitglo.com', '', 'LOGIN', base_url('index.php/Logins/LoginPage'), 'login to continue', $_POST['user_password'], 'Password:', $_POST['user_email'], 'Username:', 'Thank you for enroling into our system,<br> you can log-in to your account at the link provided below',
                                'Warm welcome', 'Alfa-laval Alert', base_url("uploads/mail_images/alert_logo.png"));
                $this->load->view('email/registration_success','',true);
                $html=$this->load->view('email/registration_success','',true);
                $html=str_replace($tag,$replace,$html);
                print_r($html);
                // $this->send($mail_email,null,null,$subject,$html);
        // $this->send_email(63);  
        // $this->send_email(62); 
    }
    public function reminder_send(){
      
       $db=db_connect();
        exit();
        $mail[0]['receiver_email']='gaurangverenkar@gmail.com';
        $mail[0]['doc_id']=57;
        $mail[1]['receiver_email']='gaurangverenkar@gmail.com';
        $mail[1]['doc_id']=105;
        $mail[2]['receiver_email']='gaurangverenkar@gmail.com';
        $mail[2]['doc_id']=108;
        $mail[10]['receiver_email']='gaurangverenkar@gmail.com';
        $mail[10]['doc_id']=90;
        
        
        $mail[3]['receiver_email']='surajk4437@gmail.com';
        $mail[3]['doc_id']=36;
        $mail[4]['receiver_email']='surajk4437@gmail.com';
        $mail[4]['doc_id']=102;
        $mail[5]['receiver_email']='surajk4437@gmail.com';
        $mail[5]['doc_id']=124;
        $mail[9]['receiver_email']='surajk4437@gmail.com';
        $mail[9]['doc_id']=80;
 
        $mail[6]['receiver_email']='akashkulkarni1313@gmail.com';
        $mail[6]['doc_id']=34;
        $mail[7]['receiver_email']='akashkulkarni1313@gmail.com';
        $mail[7]['doc_id']=104;
        $mail[8]['receiver_email']='akashkulkarni1313@gmail.com';
        $mail[8]['doc_id']=123;

        foreach($mail as $key=>$value){
            $para=array();

            $doc_id=  $_POST['doc_id']=$value['doc_id'];
            $receiver_email= $_POST['receiver_email']=$value['receiver_email'];
            $subject=$_POST['subject']='crown multipal test email id-no:'.($key);
            $body=$_POST['body']='crown multipal test email id-no:'.($key);
            $from_email=$_POST['from_email']='help@unitglo.com';

            $curl = curl_init();
            $para['fire_from']='reminder_send';
            $para['doc_id']=$doc_id;
            $para['send_to']=$receiver_email;
            $para['error_genereted']='';

            
            
            curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://alfalaval.unitglo.com/admin/index.php/Api/Test/send_email2',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => [
                'doc_id' => $doc_id,
                'receiver_email' => $receiver_email,
                'subject' => $subject,
                'from_email'=>$from_email,
                'body'=>$body
               ]
            ]);

            $resp = print_r(curl_exec($curl));
            // Close request to clear up some resources
            if (!curl_exec($curl)) {
                $para['error_genereted']='Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl);
                die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
            }
            
            $db->table("test_crown")->insert($para);
            // $this->Base_Models->AddValues('test_crown',$para);
            curl_close($curl);
     
        }
       
    }
    
    
    
    function send_email2($Id=null, $cc=array() ){
        $db=db_connect();
        // echo"<br>i m called";
	    $this->load->library('pdf');
		 $body='';
		 $from_mail='';
		 $data=$this->input->post();

// 			$data=$this->input->post();

			$para['update_date']=date('Y-m-d H:i:s');
			$para['receiver_email']=$data['receiver_email'];
			$para['subject']=$data['subject'];
			$para['body']=$data['body'];
			$para['doc_id']=$data['doc_id'];

            $from_mail=$para['from_email']=$_POST['from_email'];

			$where['near_miss_id']=$data['doc_id'];
			
        $data['details']=$db->query( " select alert_near_miss.*,alert_users.* from alert_near_miss inner join alert_users on alert_users.user_id=alert_near_miss.user_id and near_miss_id=".$data['doc_id'] )->getResultArray();
        $data['near_miss_chat']=$db->query( " select alert_near_miss_chat.*,alert_users.* 
	                                            from alert_near_miss_chat 
	                                                inner join alert_users on alert_users.user_id=alert_near_miss_chat.user_id 
	                                                    and near_miss_id=".$data['doc_id'] )->getResultArray();
        $html=$this->load->view("NearMiss/nearmiss_brif_details_pdf",$data, true);

		$this->pdf->loadhtml($html);
	
	    $this->pdf->render();
	    $id = "NearMissDetails.pdf";
		$output = $this->pdf->output();

		$email=isset($data['receiver_email'])?$data['receiver_email']:" ";
		$subject=isset($data['subject'])?$data['subject']:"no subject";
		$name="";
		$body.=isset($data['body'])?$data['body']:" ";
		$formname="AlfaLaval";
		if(isset($id) && $id !="")
		file_put_contents( $_SERVER['DOCUMENT_ROOT']."/admin/application/core/".$id,$output);
		$attachment=true;



        $para3['fire_from']='send_email2';
            $para3['doc_id']=$data['doc_id'];
            $para3['send_to']=$data['receiver_email'];
            $para3['error_genereted']=$this->send($email,$name,$from_mail,$subject,$body,$formname,$attachment,$id,$cc);
     $db->table("test_crown")->insert($para3);
		return $para3['error_genereted'];
		
		
	}



}
?>
