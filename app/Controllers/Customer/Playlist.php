<?php

//namespace App\Controllers;
namespace App\Controllers\Customer;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Playlist extends BaseController
{
 
   public function __construct(){
        helper("form");
     $db = null;
     $db['table']         = 'ads_media_playlist';
     $db['allowedFields'] = [
       'playlist_name','playlist_description','user_id','customer_id','status'];
     $db['primaryKey'] = "playlist_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    
    public function index(){
            $postData['user_id'] =$this->user_id;
            if($this->customer_id!=0)
                $postData['customer_id'] = $this->customer_id;
                

       $data['table_data'] =  $this->BaseModel->select("*, (select 
       (select group_concat(media_url) from ads_media_master where ads_media_master.status=1 and 
       ads_media_master.media_id in (select media_id from ads_playlist_items where ads_playlist_items.playlist_id=ads_media_playlist.playlist_id )
       )  ) as media_list")->where($postData)->where(["status!="=>"2"])->findAll();
    //   echo "<pre>";
    //   print_r($data['table_data']);
    //   exit();
    
      return view("Customer/playlist",$data);
    }
    
     public function add($id=null){
           $data['media_list_url'] = base_url("/Customer/Media/table_ajax");
           $data['save_play_list_details'] = base_url("/Customer/Playlist/save_details");
              $data['customer_id'] = $this->customer_id;
              $data['user_id'] = $this->user_id;

           if(isset($id)){
           $postData['user_id'] =$this->user_id;
            if($this->customer_id!=0)
                $postData['customer_id'] = $this->customer_id;
                

               $data['save_play_list_details'] = base_url("/Customer/Playlist/save_details/".$id);
                 $db = db_connect();
                                    $ads_playlist_items = $db->table('ads_playlist_items');
                                    $ads_playlist_items->where('status', '1');
                                    $ads_playlist_items->where('playlist_id', $id);
                                    $data['playlist_items'] = $ads_playlist_items->get()->getResultArray();
               $data['details']=$this->BaseModel->find($id);
                             $data['customer_id'] = $data['details']['customer_id'];
                             $data['user_id'] = $data['details']['user_id'];

           }
        // $data['device_list']= $this->update_details($this->user_id);
        echo $this->update_details($this->user_id);
      return view("Customer/add_playlist",$data);
    }
    
 
    public function save_details($id=null,$action=null){
                $request = service('request');
                $postData = $request->getVar();
                
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
                        $responce['status'] = "0";
                        $responce['message'] = "Data updation faild";
                        // url
                        if(isset($action)){
                            switch($action){
                                case "active":
                                    $postData['status']= "1";
                                    $db = db_connect();
                                    $ads_playlist_items = $db->table('ads_playlist_items');
                                    $ads_playlist_items->set('status', '1');
                                    $ads_playlist_items->where('playlist_id', $id);
                                    $ads_playlist_items->update();
                                    // $ads_playlist_items->replace(['status'=>'1',"playlist_id"=>$id]);
                                    // $ads_playlist_items->update(['status'=>'1'],["playlist_id"=>$id]);
                                    break;
                                case "deactive":
                                    $postData['status']= "0";
                                    $db = db_connect();
                                    $ads_playlist_items = $db->table('ads_playlist_items');
                                    $ads_playlist_items->set('status', '0');
                                    $ads_playlist_items->where('playlist_id', $id);
                                    $ads_playlist_items->update();
                                    break;
                                case "delete":
                                    $postData['status']= "2";
                                    $db = db_connect();
                                    $ads_playlist_items = $db->table('ads_playlist_items');
                                    $ads_playlist_items->set('status', '2');
                                    $ads_playlist_items->where('playlist_id', $id);
                                    $ads_playlist_items->update();
                                    //$ads_playlist_items->replace(['status'=>'2',"playlist_id"=>$id]);
                                    break;
                            }
                        }
                    if($this->BaseModel->update($id,$postData)){
                        if(isset($postData['media_id'])){
                            $ads_playlist_items_list = [];
                        $playlist_id = $id;
                            $db = db_connect();
                            $ads_playlist_items = $db->table('ads_playlist_items');
                            
                            $ads_playlist_items->delete(['playlist_id'=>$id]);
                            // $ads_playlist_items->set('status', '2');
                            // $ads_playlist_items->where('playlist_id', $id);
                            // $ads_playlist_items->update();
                            
                            $replace_flag = false;
                            foreach($postData['media_id'] as $key=>$row){
                            $ads_playlist_items_list_NEW=[];
                                $media_id= $postData['media_id'][$key];
                                $loop_count= $postData['loop_count'][$key];
                                $duration_sec = $postData['duration_sec'][$key];
                               $ads_playlist_items_list_NEW['playlist_id']= $playlist_id;
                               $ads_playlist_items_list_NEW['media_id']= $media_id;
                               $ads_playlist_items_list_NEW['loop_count']= $loop_count;
                               $ads_playlist_items_list_NEW['duration_sec']= $duration_sec;
                               $ads_playlist_items_list_NEW['status']= "1";
                                array_push($ads_playlist_items_list,$ads_playlist_items_list_NEW);
                                // $replace_flag= $ads_playlist_items->replace($ads_playlist_items_list_NEW);
                            }
                            // if($replace_flag){
                            if($ads_playlist_items->insertBatch($ads_playlist_items_list)){

                                $responce['status'] = "1";
                                $responce['message'] = "Data saved successfully";
                            }else{
                                $responce['status'] = "0";
                                $responce['message'] = "Data insertion issue into media item";
                            }
                        }else{
                        $responce['status'] = "1";
                        $responce['message'] = "Data saved successfully";
                    }
                    }
                    
                }else{
                    $postData['status']="1";
                        $responce['status'] = "0";
                        $responce['message'] = "Data insertion faild";
                        $ads_playlist_items_list = [];
                        if(!isset($postData['media_id'])){
                            $responce['message'] = "Data insertion faild!! Please select media files.";
                        }else
                    if( $this->BaseModel->insert($postData)){
                        $playlist_id = $this->BaseModel->getInsertID();
                            $db = db_connect();
                            $ads_playlist_items = $db->table('ads_playlist_items');
                            foreach($postData['media_id'] as $key=>$row){
                            $ads_playlist_items_list_NEW=[];
                                $media_id= $postData['media_id'][$key];
                                $loop_count= $postData['loop_count'][$key];
                                $duration_sec = $postData['duration_sec'][$key];
                               $ads_playlist_items_list_NEW['playlist_id']= $playlist_id;
                               $ads_playlist_items_list_NEW['media_id']= $media_id;
                               $ads_playlist_items_list_NEW['loop_count']= $loop_count;
                               $ads_playlist_items_list_NEW['duration_sec']= $duration_sec;
                               $ads_playlist_items_list_NEW['status']= "1";
                                array_push($ads_playlist_items_list,$ads_playlist_items_list_NEW);
                            }
                            if($ads_playlist_items->insertBatch($ads_playlist_items_list)){
                                $responce['status'] = "1";
                                $responce['message'] = "Data saved successfully";
                            }else{
                                $responce['status'] = "0";
                                $responce['message'] = "Data insertion issue into media item";
                                
                            }
                    }
                }
                echo json_encode($responce);
    }
}
