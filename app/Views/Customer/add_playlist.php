<?php $this->extend("Layout/base_admin"); ?>
<?php 
					$this->section("breadcrumb_title_li");
?>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?=base_url("Customer/Playlist")?>" class="text-muted text-hover-primary">PlayList</a>
	</li>
<!--end::Item-->
<?php $this->endSection();?>	

<?php $this->section("main_body"); ?>

										<input type="hidden" name="customer_id" id="customer_id" value="<?=$customer_id?>">
										<input type="hidden" name="user_id" id="user_id" value="<?=$user_id?>">

<div id="media_div_style" style="display:none;">
<!--begin::Card-->
									<div class="col-sm-6 col-xl-6">
										

										<div class="card h-100 media_id_{{media_id}}" style="border-radius:15px; box-shadow: 1px 5px 10px 1px rgb(37 37 37 / 60%);">
											<!--begin::Card body-->
											<div class="card-body d-flex flex-column overlay" style="padding: 1rem 1.05rem;  background-color: lightgrey; border-top-left-radius: 15px;border-top-right-radius: 15px; height: 110px;
    max-width: 100%; position: relative; overflow: hidden; object-fit: cover;">
												<!--begin::Heading-->
												<div class="fs-2tx fw-bolder">
												    <img style="width:100%;" src="{{media_url}}">
												</div>
												<div class="overlay-layer card-rounded bg-dark bg-opacity-25">
												    <input type=hidden name="media_id" class="media_id" value="{{media_id}}">
												     <input type=hidden name="media_type" class="media_type" value="{{media_type}}">
												      <input type=hidden name="duration_sec" class="duration_sec" value="3">
												      <input type=hidden name="loop_count" class="loop_count" value="1">
												<a onclick="add_to_playlist(this)"> <i class="fa fa-plus" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i></a>
												</div>
												
											</div>
											<!--end::Card body-->
											
												<!--begin::Card header-->
											<div class="card-header flex-nowrap border-0" style="height: 70px !important; overflow: hidden !important; width: 100% !important; padding: 1rem 1rem !important; display: inline-block;">
												<!--begin::Card title-->
												<div class="card-title m-0">
													<!--begin::Icon-->
												
													<!--end::Icon-->
													<!--begin::Title-->
													<p class="fs-7 fw-bold text-hover-primary text-gray-600 m-0">{{media_name}}</p>
													
													<!--end::Title-->
												</div>
												<!--end::Card title-->
											
											</div>
											<!--end::Card header-->
										</div>
									</div>
										<!--end::Card-->
    
</div>


<div id="kt_content_container" class="container-xxl">
								
								<!--begin::Toolbar-->
								<div class="d-flex flex-wrap flex-stack mb-6">
									<!--begin::Title-->
									<h3 class="fw-bolder my-2">My PlayList 
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
									    <div class=" row fv-plugins-bootstrap5 fv-plugins-framework">
									   <div class="col-sm-5 col-xl-5">
									        <div class="col-md-12 fv-row fv-plugins-icon-container">
										        <input type="text" value="<?=isset($details['playlist_name'])?$details['playlist_name']:""?>" class="form-control form-control-solid" placeholder="Please enter Title" id="title" name="title" style="background-color:white;">
									        </div>
									   </div>
									   <div class="col-sm-5 col-xl-5">
									       <div class="col-md-12 fv-row fv-plugins-icon-container">
										    <input type="text" value="<?=isset($details['playlist_description'])?$details['playlist_description']:""?>" class="form-control form-control-solid" placeholder="Please enter description" id="description" name="description" style="background-color:white;">
									       </div> 
									       
									   </div>
									   
									   <div class="col-sm-2 col-xl-2">
									       
									    <button class="btn btn-primary btn-sm" onclick="save_play_list_details()">Save PlayList</button>
									   </div>
									   
									    </div>
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
								    
								    <div class="card-header pt-7" >
												<!--begin::Title-->
												<h3 class="card-title align-items-start flex-column">
													<span class="card-label fw-bolder text-dark">Select Media</span>
													<!--<span class="text-gray-400 mt-1 fw-bold fs-6">1M Products Shipped so far</span>-->
												</h3>
												<!--end::Title-->
									</div>    
								    
								    <div class="card-body">
								        <div class="hover-scroll-overlay-y pe-6 me-n6" style="height: 415px">
								        <!-- START -->
								        
								        <div class="row row g-6 g-xl-9 " id="div_list_details">
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
		<?php $this->section("modals_section"); ?>
	<div class="modal fade" tabindex="-1" id="kt_modal_1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set image duration</h5>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <span class="svg-icon svg-icon-2x"></span>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <div class="fv-row mb-10 fv-plugins-icon-container">
												<!--begin::Label-->
												<label class="d-flex align-items-center fs-5 fw-bold mb-2">
													<span class="required">Duration in seconds</span>
													<i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="" data-bs-original-title="Specify your image duration in seconds" aria-label="Specify your image duration in seconds"></i>
												</label>
												<!--end::Label-->
												<!--begin::Input-->
												<input type="number" min="0" class="form-control form-control-lg form-control-solid kt_modal_1_duration"  placeholder="" value="0">
												<!--end::Input-->
											<div class="fv-plugins-message-container invalid-feedback"></div></div>
                
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="update_data()">Save changes</button>
            </div>
        </div>
    </div>
</div>
	<div class="modal fade" tabindex="-1" id="kt_modal_2">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Video Loop Count</h5>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <span class="svg-icon svg-icon-2x"></span>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <div class="fv-row mb-10 fv-plugins-icon-container">
												<!--begin::Label-->
												<label class="d-flex align-items-center fs-5 fw-bold mb-2">
													<span class="required">Loop Count</span>
													<i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="" data-bs-original-title="Specify your video loop count" aria-label="Specify your video loop count"></i>
												</label>
												<!--end::Label-->
												<!--begin::Input-->
												<input type="number" min="0" class="form-control form-control-lg form-control-solid kt_modal_2_loop"  placeholder="" value="0">
												<!--end::Input-->
											<div class="fv-plugins-message-container invalid-feedback"></div></div>
                
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="update_data()">Save changes</button>
            </div>
        </div>
    </div>
</div>

	<?php $this->endSection(); ?>

	
	<?php $this->section("javascript_section"); ?>

<script>
     function get_media_list(){
        let formData = new FormData();
        formData.append("user_id",$("#user_id").val());
        formData.append("customer_id",$("#customer_id").val());

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
            url: '<?=$media_list_url?>',
            data: formData,
            // contentType: false,
            contentType: "application/json; charset=utf-8",

            cache: false,
            processData:false,
            beforeSend: function(){
                $(".progress-bar").width('0%');
                
            },
            error:function(){
                $('#uploadStatus').html('<p style="color:#EA4335;">Please try again.</p>');
            },
            success: function(resp){
            //    resp = JSON.parse(resp);
                if(resp.status == 1){
                    // $('#uploadForm')[0].reset();
                    create_list(resp.data);
                    load_details();
                    
                }else if(resp.message == 'err'){
                    $('#uploadStatus').html('<p style="color:#EA4335;">Detail Not Found.</p>');
                }else{
                    $('#uploadStatus').html('<p style="color:#EA4335;">'+resp.message+'!</p>');
                }
                
                setTimeout(function(){ 
                    $(".progress-bar").width('0%');
                    $("#uploadStatus").html("");
                },1000);
                
            }
        });


    }
    function create_list(data){
        // data.media_name
        // data.media_type
        // data.media_url
        
     var div_data = $("#media_div_style").html();
     var div_list_details = "";
     $.each(data,function(index,obj){
     var temp =div_data;
    //  alert(obj['media_name']);
    //  alert(obj.media_name);
      temp=temp.replace("{{media_name}}",obj['media_name']);
      temp=temp.replaceAll("{{media_id}}",obj['media_id']);
      temp=temp.replace("{{media_type}}",obj['media_type']);
            if(obj['media_type']=="mp4"){
        temp=temp.replace("<img","<video");
      }

      temp=temp.replace("{{media_url}}",obj['media_url']);
         div_list_details +=temp;
     });
     $("#div_list_details").html(div_list_details);
                    
    setTimeout( $(".progress-bar").width('0%'),1000);

    }
    setTimeout(get_media_list(),1000);
    function add_to_playlist(obj){
        //<a href="#" onclick="add_to_playlist(this)"> <i class="fa fa-plus" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i> </a>
        //#div_playlist_details
        // fa-trash $(obj)parent().parent().parent().parent()
        //												<a href="#" onclick="$(this).parent().parent().parent().parent().remove()"> <i class="fa fa-trash" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i> </a>
    
      var temp =$(obj).parent().parent().parent().parent().html();
      temp=temp.replace("onclick=\"add_to_playlist(this)\"","onclick=\"$(this).parent().parent().parent().parent().remove()\"");
      temp=temp.replace("fa fa-plus","fa fa-trash");
      temp=temp.replace("</a>",'</a><a  onclick="pencil_details(this)"> <i class="fa fa-edit" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i></a>');
     $("#div_playlist_details").append("<div class=\"col-sm-4 col-xl-4\">"+temp+"</div>");

    }
    function save_play_list_details(){
         let formData = new FormData();
        formData.append("user_id",$("#user_id").val());
        formData.append("customer_id",$("#customer_id").val());
        formData.append("playlist_name",$("#title").val());
        formData.append("playlist_description",$("#description").val());
        if($("#div_playlist_details").find(".media_id").length>0)
            $.each($("#div_playlist_details").find(".media_id"),function(i,o){
                formData.append("media_id[]",$(this).val());
            });
        if($("#div_playlist_details").find(".loop_count").length>0)
            $.each($("#div_playlist_details").find(".loop_count"),function(i,o){
                formData.append("loop_count[]",$(this).val());
            });
            
        if($("#div_playlist_details").find(".duration_sec").length>0)
            $.each($("#div_playlist_details").find(".duration_sec"),function(i,o){
                formData.append("duration_sec[]",$(this).val());
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
            url: '<?=$save_play_list_details?>',
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
                    <?php if(!isset($playlist_items)){ ?>
                    window.location.reload();
                    <?php } ?>
                },2000);
                
            }
        });
    }
    function load_details(){
        //         var media_id_arr = []
        // if($("#div_list_details").find(".media_id").length>0)
        //     $.each($("#div_playlist_details").find(".media_id"),function(i,o){
        //         formData.append("media_id[]",$(this).val());
        //     });
        var temp = null;
<?php if(isset($playlist_items)){ 
                            foreach($playlist_items as $row){
                            ?>
                            temp = null;
      temp =$("#div_list_details").find(".media_id_<?=$row['media_id']?>").clone();
      if(typeof temp.html() !="undefined"){
      $(temp).find(".duration_sec").val("<?=$row['duration_sec']?>");
      $(temp).find(".loop_count").val("<?=$row['loop_count']?>");
      temp = temp.html();
      temp=temp.replace("onclick=\"add_to_playlist(this)\"","onclick=\"$(this).parent().parent().parent().parent().remove()\"");
      temp=temp.replace("fa fa-plus","fa fa-trash");
      temp=temp.replace("</a>",'</a><a  onclick="pencil_details(this)"> <i class="fa fa-edit" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i></a>');
      temp = '<div class="card h-100 media_id_<?=$row['media_id']?>" style="border-radius:15px; box-shadow: 1px 5px 10px 1px rgb(37 37 37 / 60%);">'+temp+'</div>';
     $("#div_playlist_details").append("<div class=\"col-sm-4 col-xl-4\">"+temp+"</div>");
      }
<?php }}?>
    }
                        
    var media_obj_for_modal = null;
    function pencil_details(obj){
        // alert(media_id);
    //  kt_modal_2_loop
    // kt_modal_1_duration
//    $(obj).parent().parent().parent().parent().find(".media_type").val()
    // var file_type = $("#div_playlist_details").find("div.media_id_"+media_id).find(".media_type").val();
    var file_type =     $(obj).parent().parent().parent().parent().find(".media_type").val();
    media_obj_for_modal = obj;
    if((/(gif|jpe?g|tiff?|png|webp|bmp)$/i).test(file_type)){
        $("#kt_modal_1").find(".kt_modal_1_duration").val($(obj).parent().parent().parent().parent().find(".duration_sec").val());
        $("#kt_modal_1").modal("show");
    }else{
        $("#kt_modal_2").find(".kt_modal_2_loop").val($(obj).parent().parent().parent().parent().find(".loop_count").val());
        $("#kt_modal_2").modal("show");
    }
    }
    $('.modal').on('hidden.bs.modal', function () {
      media_obj_for_modal = null
    });
function update_data(){
    var obj = media_obj_for_modal
      var file_type =     $(obj).parent().parent().parent().parent().find(".media_type").val();
    media_obj_for_modal = obj;
    if((/(gif|jpe?g|tiff?|png|webp|bmp)$/i).test(file_type)){
        $(obj).parent().parent().parent().parent().find(".duration_sec").val($("#kt_modal_1").find(".kt_modal_1_duration").val());
        $("#kt_modal_1").modal("hide");
    }else{
        $(obj).parent().parent().parent().parent().find(".loop_count").val($("#kt_modal_2").find(".kt_modal_2_loop").val());
        $("#kt_modal_2").modal("hide");
    }
  media_obj_for_modal = null;
}
</script>

    <?php $this->endSection(); ?>
    