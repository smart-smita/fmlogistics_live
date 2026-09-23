<?php

namespace App\Controllers;

class Blue_space extends BaseController
{
  
      public function __construct(){
    }
    
    public function create_form(){
                return view('creat_form');

    }
    public function details(){        return view('deatils');
}
    public function steps(){        return view('steps');
}
}
?>