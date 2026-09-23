<?php

namespace App\Controllers;
use App\Models\CRUDBaseModel;

class Demo extends BaseController
{
    public $BaseModel=null;
    public function __construct(){
        helper("form");
     $db = null;
     $db['table']         = 'asti_course_master';
     $db['allowedFields'] = [
        'course_name','course_duration','course_total_semister','course_detail','status'];
     $db['primaryKey'] = "course_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
          return view('test');
        //return view('dashboard');
    }
    
     public function user()
    {
          return view('Master/user');
        //return view('dashboard');
    }
    
    public function akash(){
        $data = [];
        $rules = [
            "username"=>"",
            "email"=>"",
            "contact"=>""
        ];
        
        
        print_r($this->BaseModel->findAll());
        //return view("test_akash");
    }
    public function login(){
        return view("Forms/student_login");
    }
    public function fees()
    {
        return view('Master/fees');
    }
}
