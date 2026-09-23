<?php

namespace App\Controllers;

use App\Models\CRUDBaseModel;

class Authentication extends BaseController
{
    public $session = null;
    public function __construct(){
       // helper("form");
            // $this->session = \Config\Services::session();
            // $this->session->start();

    }
    public function log_out(){
        if (session_id()) {
            $db = db_connect();
            $db->table('alert_login_history')
               ->where('session_id', session_id())
               ->update(['logout_time' => date('Y-m-d H:i:s')]);
        }
        $this->session->destroy();
        return redirect()->to(base_url('Authentication'));
    }
    public function index($status= null)
    {
        $status="";
        // print_r($_SESSION);
        if( isset($_SESSION['student_id']) && $_SESSION['student_id']!="0"){
            $status="done";
        }
        if(isset($status) && $status=="done"){
            echo "Login done";        
        }else{
            require_once APPPATH."Libraries/vendor/autoload.php";
            $google_client = new \Google_Client();
            $google_client->setClientId(env("google.clientId"));
            $google_client->setClientSecret(env("google.clientSecret"));
            $google_client->setRedirectUri(base_url("/Authentication/oauthResponce"));
            $this->session->set("type","login");
            //session()->has("type");
            $google_client->addScope("email");
            $google_client->addScope("profile");
            $data['google_login_url']=$google_client->createAuthUrl();
            $data['login_check']=base_url("Authentication/login_check");
            return view('Forms/Authentication/student_login',$data);
        }
    }
    public function login_check(){
                     $responce['status']=0;
                     $responce['message']="Login Faild";
                     $responce['url'] = base_url("Authentication");
        if($_POST['email']=="admin@admin.com" && $_POST['password'] =="admin!@#123"){
            //  $this->session->set("student_id",$row['student_id']);
                            $this->session->set("details",'');
                            $this->session->set("role","admin");
                        $responce['status']=1;
                        $responce['message']="You have successfully logged in!";
                        $responce['url'] = base_url("Customer/Audit_dashboard/OE_Audit");
        }else{
                     $db1 = db_connect();
                     $sql = "SELECT * FROM asti_student_registration WHERE email = ? and password = ? ";
                     $responce['status']=0;
                     $responce['message']="Login Faild";
                     $responce['url'] = base_url("Authentication");
                        $query= $db1->query($sql, [$_POST['email'],$_POST['password']]);
                        $row = $query->getRowArray();
                        if(isset($row)){
                            //echo "Login Done";
                            $responce['status']=1;
                            $responce['message']="You have successfully logged in!";
                            $responce['url'] = base_url("Student/student_profile_view/".$row['student_id']);
                            $this->session->set("student_id",$row['student_id']);
                            $this->session->set("details",$row);
                            $this->session->set("role","student");
                        }
        }
                                return $this->response->setJSON($responce);

    }
    public function forgot_password(){
        return view('Forms/Authentication/forgot_password');
    }
    public function sign_up($is_google=null,$student_id=null){
        
        if(isset($is_google) && $is_google=="done"){
            if(isset($student_id)){
                     $db = db_connect();
                     $sql = "SELECT * FROM asti_student_registration WHERE student_id = ?";
                        $query= $db->query($sql, [$student_id]);
                        $row = $query->getRowArray();
                        $data['details']=$row;
            }
        }else{
            require_once APPPATH."Libraries/vendor/autoload.php";
            $google_client = new \Google_Client();
            $google_client->setClientId(env("google.clientId"));
            $google_client->setClientSecret(env("google.clientSecret"));
            $google_client->setRedirectUri(base_url("/Authentication/oauthResponce"));

            $this->session->set("type","sign_up");
            //session()->has("type");

            $google_client->addScope("email");
            $google_client->addScope("profile");
            $data['google_login_url']=$google_client->createAuthUrl();
        }
        $data['sign_up_action'] = base_url("Authentication/accept_sign_up_details");
        return view('Forms/Authentication/student_sign_up',$data);
    }
    public function accept_sign_up_details(){
        $student_id = 0;
                $table_inset['first_name']=$_POST['first-name'];
                $table_inset['last_name']=$_POST['last-name'];
                $table_inset['email']=$_POST['email'];
                $table_inset['password']=$_POST['password'];
                $table_inset2['first_name']=$_POST['first-name'];
                $table_inset2['last_name']=$_POST['last-name'];
               
                // $table_inset['email_verified']="1";
                $table_inset['status']="0";
                //$table_inset['gmail_object']=json_encode($data);
                     $db = null;
                     $db['table']         = 'asti_student_registration';
                     $db['allowedFields'] = [
                        'first_name','last_name','email','password','status'];
                     $db['primaryKey'] = "student_id";
                     $BaseModel = new CRUDBaseModel($db);
                    
                     $db3['table']         = 'asti_student_information';
                     $db3['allowedFields'] = [
                        'first_name','last_name'];
                     $db3['primaryKey'] = "student_id";
                     $BaseModel3 = new CRUDBaseModel($db3);
                    
                    
                     $db1 = db_connect();
                     $sql = "SELECT * FROM asti_student_registration WHERE email = ?";
                        $query= $db1->query($sql, [$table_inset['email']]);
                        $row = $query->getRowArray();
                        if(isset($row)){
                             $BaseModel->update($row['student_id'],$table_inset);
                             $BaseModel3->update($row['student_id'],$table_inset2);
                             $message= "done";
                             $student_id =  $row['student_id'];
                             echo "Update";
                        }else{
                            $BaseModel->insert($table_inset);
                            $BaseModel3->insert($table_inset2);
                            
                            $message= "done";
                            $student_id = $BaseModel->getInsertID();
                        echo "insert";
                        }
                        
                        
                              return redirect()->to(base_url("Student/profile/$student_id"));

        // echo "Session Create function";
    }

    public function oauthResponce(){
                    //$this->session->set("type","sign_up");
            //session()->has("type");

        $type = (session()->has("type") && $this->session->get('type') !="")?$this->session->get('type'):null;
        
        //print_r($_REQUEST);
        $message = "Error";
        $student_id = "";
        if($this->request->getVar('code')){
                    require_once APPPATH."Libraries/vendor/autoload.php";
                    $google_client = new \Google_Client();
                    $google_client->setClientId(env("google.clientId"));
                    $google_client->setClientSecret(env("google.clientSecret"));
                    $google_client->setRedirectUri(base_url("/Authentication/oauthResponce"));
                    $google_client->addScope("email");
                    $google_client->addScope("profile");
            $token = $google_client->fetchAccessTokenWithAuthCode($this->request->getVar('code'));
            if(!isset($token['error'])){
                $google_client->setAccessToken($token['access_token']);
                $google_service = new \Google_Service_Oauth2($google_client);
                $data = $google_service->userinfo->get();
                //print_r($data);
                $table_inset['first_name']=$data['givenName'];
                $table_inset['last_name']=$data['familyName'];
                $table_inset['email']=$data['email'];
                $table_inset['email_verified']="1";
                $table_inset['status']="0";
                $table_inset['gmail_object']=json_encode($data);
                     $db = null;
                     $db['table']         = 'asti_student_registration';
                     $db['allowedFields'] = [
                        'first_name','last_name','email','email_verified','gmail_object','status'];
                     $db['primaryKey'] = "student_id";
                     $BaseModel = new CRUDBaseModel($db);
                     $db1 = db_connect();
                     $sql = "SELECT * FROM asti_student_registration WHERE email = ?";
                        $query= $db1->query($sql, [$table_inset['email']]);
                        $row = $query->getRowArray();
                        
                        if(isset($row)){
                             $BaseModel->update($row['student_id'],$table_inset);
                             $message= "done";
                             $student_id =  $row['student_id'];
                             echo "Update";
                        }else{
                            $BaseModel->insert($table_inset);
                            $message= "done";
                            $student_id = $BaseModel->getInsertID();
                        echo "insert";
                        }
                    }
        }
                switch($type){
                    case "sign_up":
                        return redirect()->to(base_url("Authentication/sign_up/$message/$student_id"));
                        break;
                    case "login":
                        echo "Session Create function";
                        //return redirect()->to(base_url("Authentication/index/$message"));
                        break;
                }

    }
}
