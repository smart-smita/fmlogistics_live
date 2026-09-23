<?php $this->extend("Layout/base_admin"); ?>
<?php 
					$this->section("breadcrumb_title_li");
?>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?=base_url("Customer/Screen")?>" class="text-muted text-hover-primary">Screen</a>
	</li>
<!--end::Item-->
<?php $this->endSection();?>	

<?php $this->section("main_body"); ?>

										<input type="hidden" name="customer_id" id="customer_id" value="<?=$customer_id?>">
										<input type="hidden" name="user_id" id="user_id" value="<?=$user_id?>">

<div id="kt_content_container" class="container-xxl">
								
								<!--begin::Toolbar-->
								<div class="d-flex flex-wrap flex-stack mb-6">
									<!--begin::Title-->
									<h3 class="fw-bolder my-2">My Screen 
									<span class="fs-6 text-gray-400 fw-bold ms-1"></span></h3>
									<!--end::Title-->
									<!--begin::Controls-->
									<div class="d-flex align-items-center my-2">
										<!--begin::Select wrapper-->
										
										<!--end::Select wrapper-->
									<!--	<button class="btn btn-primary btn-sm" onclick="">Save PlayList</button>-->
									<br><br>
									</div>
									<!--end::Controls-->
								</div>
								<!--end::Toolbar-->
								
									<div class="row">
									    <!--<form class=" row fv-plugins-bootstrap5 fv-plugins-framework">-->
									   <div class="col-sm-3 col-xl-3">
									        <div class="col-md-12 fv-row fv-plugins-icon-container">
										        <input type="text" value="<?=isset($details['screen_name'])?$details['screen_name']:""?>" class="form-control form-control-solid" placeholder="Please enter Title" id="title" name="title" style="background-color:white;">
									        </div>
									   </div>
									   <div class="col-sm-4 col-xl-4">
									       <div class="col-md-12 fv-row fv-plugins-icon-container">
										    <input type="text" value="<?=isset($details['screen_discription'])?$details['screen_discription']:""?>" class="form-control form-control-solid" placeholder="Please enter description" id="description" name="description" style="background-color:white;">
									       </div> 
									       
									   </div>
									    <div class="col-sm-3 col-xl-3">
									       <div class="col-md-12 fv-row fv-plugins-icon-container">
									           <select name="device_id" id="device_id" class="form-select">
									               <option value="0">Select Device</option>
									           <?php 
									           foreach($api_device as $apirow){
									           
									           ?>
									           <option value="<?=$apirow['device_details_id']?>" <?=(isset($details['device_id']) && $details['device_id']==$apirow['device_details_id'])?"selected":""?>><?=$apirow['device_name']?></option>
									           <?php } ?>
									           </select>
									           <!--//device_details_id-->
										    <!--<input type="text" value="<?php //isset($details['screen_discription'])?$details['screen_discription']:""?>" class="form-control form-control-solid" placeholder="Please enter description" id="description" name="description" style="background-color:white;">-->  
									       </div> 
									       
									   </div>
									   
									   <div class="col-sm-2 col-xl-2">
									       
									    <button class="btn btn-primary btn-sm" onclick="save_play_list_details()">Save Screen</button>
									   </div>
									   
									    <!--</form>-->
									</div>
								<br><br>
								
								<!--begin::Row-->

								<div class="row g-6 g-xl-9">
								    			<div class="progress">
                                        <div class="progress-bar"></div>
                                    </div>
                                <div id="uploadStatus"></div>					
								<div class="col-xl-4">
								    <div class="card card-flush h-xl-100">
								    
								    <div class="card-header pt-7">
												<!--begin::Title-->
												<h3 class="card-title align-items-start flex-column">
													<span class="card-label fw-bolder text-dark">Select Play List</span>
													<!--<span class="text-gray-400 mt-1 fw-bold fs-6">1M Products Shipped so far</span>-->
												</h3>
												<!--end::Title-->
									</div>    
								    
								    <div class="card-body">
								        <div class="hover-scroll-overlay-y pe-6 me-n6" style="height: 415px">
								        <!-- START -->
								        
								        <div class="row row g-6 g-xl-9 " id="div_list_details">
								            
<?php 
if(isset($table_data))
foreach($table_data as $row){
    
?>
									<!--begin::Col-->
									<div class="col-sm-12 col-xl-12 playlist_id_<?=$row['playlist_id']?>">
										<!--begin::Card-->
										<div class="card h-100" style="border-radius:15px; box-shadow: 1px 5px 10px 1px rgb(37 37 37 / 60%);">
										
											<!--begin::Card body-->
											<div class="card-body d-flex flex-column overlay" style="padding: 0rem 1.05rem; background-color: lightgrey; border-top-left-radius: 15px; border-top-right-radius: 15px; height: 100px; max-width: 100%; position: relative; overflow: hidden; object-fit: cover; display:inline-flex !important;">
												<!--begin::Heading-->
												<div class="fs-2tx fw-bolder" style="height: 100px; display: inline-flex; max-width: 100%; object-fit: cover; position: relative; overflow: hidden;">
												    <?php 
												    $media_list = explode(",",$row['media_list']);
												    for($i = 0 ; $i < count($media_list) && $i<3;$i++){
												              if(strpos($media_list[$i],"mp4")){
												                  ?>
		    											<video style="width: 30%; height: 100px; padding-left: 2px; padding-right: 2px;" src="<?=base_url($media_list[$i])?>">
	    										                  <?php
        
      }else{

												    ?>
												    
    	    											<img style="width: 30%; height: 100px; padding-left: 2px; padding-right: 2px;" src="<?=base_url($media_list[$i])?>">
	    											<?php } } ?>
	    											<input type="hidden" class="playlist_id" value="<?=$row['playlist_id']?>">
	    											<input type="hidden" class="start_date" value="<?=date("Y-m-d")?>">
	    											<input type="hidden" class="end_date" value="<?=date("Y-m-d")?>">
												</div>
												<div class="overlay-layer card-rounded bg-dark bg-opacity-25">
												<a href="<?=base_url("Customer/Playlist/add/".$row['playlist_id'])?>"> <i class="fa fa-edit" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i> </a>
												<a onclick="add_to_playlist(this)"> <i class="fa fa-plus" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i></a>

												</div>
												
											</div>
											<!--end::Card body-->
											
												<!--begin::Card header-->
											<div class="card-header flex-nowrap border-0">
												<!--begin::Card title-->
												<div class="card-title m-0">
													<!--begin::Icon-->
												
													<!--end::Icon-->
													<!--begin::Title-->
													<p class="fs-4 fw-bold text-hover-primary text-gray-600 m-0"><?=$row['playlist_name']?></p>
													<!--end::Title-->
												</div>
												<!--end::Card title-->
											
											</div>
											<!--end::Card header-->
										</div>
										<!--end::Card-->
									</div>
									<!--end::Col-->
<?php 
}
?>		
				
                                        </div>
    								        
								        <!--END-->
								        </div>
								    </div>
								        
								    </div>
								    
								</div>
                                    
                                    <div class="col-xl-8">
                                        
                                        <div class="row row g-6 g-xl-9 " id="div_playlist_details">

								
										
										</div>
									</div>
								</div>
									
								
								<!--end::Row-->
								
							
							</div>
							
							
							
							
	<?php $this->endSection(); ?>
	
	
	<?php $this->section("javascript_section"); ?>

<script>
function add_to_playlist(obj){
        //<a href="#" onclick="add_to_playlist(this)"> <i class="fa fa-plus" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i> </a>
        //#div_playlist_details
        // fa-trash $(obj)parent().parent().parent().parent()
        //												<a href="#" onclick="$(this).parent().parent().parent().parent().remove()"> <i class="fa fa-trash" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i> </a>
    
      var temp =$(obj).parent().parent().parent().parent().html();
      temp=temp.replace("onclick=\"add_to_playlist(this)\"","onclick=\"$(this).parent().parent().parent().parent().remove()\"");
      temp=temp.replace("fa fa-plus","fa fa-trash");
    //   temp=temp.replace("</a>",'</a><a  onclick="pencil_details(this)"> <i class="fa fa-edit" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i></a>');
     $("#div_playlist_details").append("<div class=\"col-sm-4 col-xl-4\">"+temp+"</div>");

    }
       function save_play_list_details(){
         let formData = new FormData();
        formData.append("user_id",$("#user_id").val());
        formData.append("customer_id",$("#customer_id").val());
        formData.append("screen_name",$("#title").val());
        formData.append("device_id",$("#device_id").val());
        
        formData.append("screen_discription",$("#description").val());
        if($("#div_playlist_details").find(".playlist_id").length>0)
            $.each($("#div_playlist_details").find(".playlist_id"),function(i,o){
                formData.append("playlist_id[]",$(this).val());
            });
        if($("#div_playlist_details").find(".start_date").length>0)
            $.each($("#div_playlist_details").find(".start_date"),function(i,o){
                formData.append("start_date[]",$(this).val());
            });
            
        if($("#div_playlist_details").find(".end_date").length>0)
            $.each($("#div_playlist_details").find(".end_date"),function(i,o){
                formData.append("end_date[]",$(this).val());
            });
            
$.ajax({
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = ((evt.loaded / evt.total) * 100);
                        $(".progress-bar").width(percentComplete + '%');
                        $(".progress-bar").html(percentComplete+'%');
                    }
                }, false);
                return xhr;
            },
            type: 'POST',
            url: '<?=$save_screen_list_details?>',
            data: formData,
            
            contentType: false,
            // contentType: "application/json; charset=utf-8",

            cache: false,
            processData:false,
            beforeSend: function(){
                $(".progress-bar").width('0%');
                
            },
            error:function(){
                $('#uploadStatus').html('<p style="color:#EA4335;">Please try again.</p>');
            },
            success: function(resp){
                resp = JSON.parse(resp);
            // $.each($("#div_playlist_details").find(".media_id"),function(i,o){console.log($(this).val())})
                if(resp.status == 1){
                    // $('#uploadForm')[0].reset();
               //     create_list(resp.data);
               $('#uploadStatus').html('<p style="color:green;">'+resp.message+'!</p>');

                }else if(resp.message == 'err'){
                    $('#uploadStatus').html('<p style="color:#EA4335;">Detail Not Found.</p>');
                }else{
                    $('#uploadStatus').html('<p style="color:#EA4335;">'+resp.message+'!</p>');
                }
                
                setTimeout(function(){
                    $(".progress-bar").width('0%');
                    $("#uploadStatus").html("");
                    <?php if(!isset($screen_playlist_items)){ ?>
                    window.location.reload();
                    <?php } ?>
                },2000);
                
            }
        });
    }
    function load_details(){
        // var media_id_arr = []
        // if($("#div_list_details").find(".media_id").length>0)
        //     $.each($("#div_playlist_details").find(".media_id"),function(i,o){
        //         formData.append("media_id[]",$(this).val());
        //     });
        var temp = null;
<?php if(isset($screen_playlist_items)){
                            foreach($screen_playlist_items as $row){
                            ?>
                            temp = null;
      temp =$("#div_list_details").find(".playlist_id_<?=$row['playlist_id']?>").clone();
      if(temp.length!=0){
          $(temp).find(".start_date").val("<?=$row['start_date']?>");
      $(temp).find(".end_date").val("<?=$row['end_date']?>");
      temp = temp.html();
      
      temp=temp.replace("onclick=\"add_to_playlist(this)\"","onclick=\"$(this).parent().parent().parent().parent().parent().remove()\"");
      temp=temp.replace("fa fa-plus","fa fa-trash");
    //temp=temp.replace("</a>",'</a><a  onclick="pencil_details(this)"> <i class="fa fa-edit" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i></a>');
      temp = '<div class="card h-100 playlist_id_<?=$row['playlist_id']?>" style="border-radius:15px; box-shadow: 1px 5px 10px 1px rgb(37 37 37 / 60%);">'+temp+'</div>';
     $("#div_playlist_details").append("<div class=\"col-sm-4 col-xl-4\">"+temp+"</div>");
                            }
<?php }}?>
    }
    load_details();
</script>

    <?php $this->endSection(); ?>