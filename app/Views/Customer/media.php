<?php $this->extend("Layout/base_admin"); ?>
<?php 
					$this->section("breadcrumb_title_li");
?>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?= current_url() ?>" class="text-muted text-hover-primary">Media</a>
	</li>
<!--end::Item-->
<?php $this->endSection();?>	

<?php $this->section("main_body"); ?>

 <!--style="display:none"-->
<div id="" style="display:none">
    <button class="media_div_style_btn btn btn-icon btn-success" onclick="">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-unlock"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
	</button>   
</div>
<div id="media_div_style" style="display:none">

<!--begin::Card-->
									<div class="col-sm-4 col-xl-3">
										

										<div class="card h-100" style="border-radius:15px; box-shadow: 1px 5px 10px 1px rgb(37 37 37 / 60%);">
											<!--begin::Card body-->
											<div class="card-body d-flex flex-column overlay" style="padding: 0rem 1.05rem;  background-color: lightgrey; border-top-left-radius: 15px;border-top-right-radius: 15px; height: 110px;
    max-width: 100%; position: relative; overflow: hidden; object-fit: cover;">
												<!--begin::Heading-->
												<div class="fs-2tx fw-bolder">
												    <img style="width: 100%; height: 108px; object-fit: contain; top: 50%; left: 50%;" src="{{media_url}}">
												</div>
												<div class="overlay-layer card-rounded bg-dark bg-opacity-25">
												<a href="javascript:edit_media('{{media_id}}')"> <i class="fa fa-edit" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i> </a>
												<a href="javascript:remove_media('{{media_id}}')"> <i class="fa fa-trash" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i> </a>
												</div>
												
											</div>
											<!--end::Card body-->
											
												<!--begin::Card header-->
											<div class="card-header flex-nowrap border-0" style="height: 70px !important;
    overflow: hidden !important;
    width: 100% !important;
    padding: 1rem 1.5rem !important;
    display: inline-block;">
												<!--begin::Card title-->
												<div class="card-title m-0">
													<!--begin::Icon-->
												
													<!--end::Icon-->
													<!--begin::Title-->
													<p class="fs-4 fw-bold text-hover-primary text-gray-600 m-0">{{media_name}}</p>
													<p class="fs-4 text-hover-primary text-gray-600 m-0">{{media_type}}</p>
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
									<h3 class="fw-bolder my-2">My Media 
									<span class="fs-6 text-gray-400 fw-bold ms-1 count_span"></span></h3>
									<!--end::Title-->
									<!--begin::Controls-->
									<div class="d-flex align-items-center my-2">
										<!--begin::Select wrapper-->
										
										<!--end::Select wrapper-->
										<button class="btn btn-primary btn-sm" onclick="$('#media_file').click()" >Add Media</button>
										<input class="btn btn-primary btn-sm" type=file name='media_file' id="media_file" style="display: none;" accept=".mp4,.gif,.jpg,.jpeg,.png">
										<input type="hidden" name="customer_id" id="customer_id" value="<?=$customer_id?>">
										<input type="hidden" name="user_id" id="user_id" value="<?=$user_id?>">
									</div>
									<!--end::Controls-->
								</div>
								<!--end::Toolbar-->
								<!--begin::Row-->

								<div class="row g-6 g-xl-9">
    								<div class="progress">
                                        <div class="progress-bar"></div>
                                    </div>
                                <div id="uploadStatus"></div>

									<!--begin::Col-->
									<div class="row row g-6 g-xl-9 " id="div_list_details">
									</div>
									<!--end::Col-->
									
									
								</div>
								<!--end::Row-->
								
							
							</div>
							
							
							
							
	<?php $this->endSection(); ?>
	
	
	<?php $this->section("javascript_section"); ?>
	<script>
	// File type validation
    $("#media_file").change(function(){
        var allowedTypes = [ 'video/mp4','image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        var file = this.files[0];
        var fileType = file.type;
        if(!allowedTypes.includes(fileType)){
            alert('Please select a valid file (PDF/DOC/DOCX/JPEG/JPG/PNG/GIF).');
            $("#media_file").val('');
            return false;
        }else{
            upload_data();
        }
    });
    
    
    
     function upload_data(){
         
        // e.preventDefault();
        let myForm = document.getElementById('media_file');
                
        
console.log(myForm);
        let formData = new FormData();
        formData.append("media_file",myForm.files[0]);
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
            url: '<?=$action_url?>',
            data: formData,
            contentType: false,
            cache: false,
            processData:false,
            beforeSend: function(){
                $(".progress-bar").width('0%');
                // $('#uploadStatus').html('<img src="images/loading.gif"/>');
            },
            error:function(){
                $('#uploadStatus').html('<p style="color:#EA4335;">File upload failed, please try again.</p>');
            },
            success: function(resp){
                resp = JSON.parse(resp);
                if(resp.status == 1){
                    // $('#uploadForm')[0].reset();
                    $('#uploadStatus').html('<p style="color:#28A74B;">'+resp.message+'!</p>');
                }else if(resp.message == 'err'){
                    $('#uploadStatus').html('<p style="color:#EA4335;">Please select a valid file to upload.</p>');
                }else{
                    $('#uploadStatus').html('<p style="color:#EA4335;">'+resp.message+'!</p>');
                }
                setTimeout(function(){ 
                    $(".progress-bar").width('0%');
                    $("#uploadStatus").html("");
                    get_media_list();
                },1000);

            }
        });
    }
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
                    $(".count_span").html("Found media : "+resp.data.length);
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
      temp=temp.replace("{{media_type}}",obj['media_type']);
      temp=temp.replaceAll("{{media_id}}",obj['media_id']);
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
function remove_media(id){
     Swal.fire({
              title: 'Do you want delete?',
              //showDenyButton: true,
              showCancelButton: true,
              confirmButtonText: 'Save',
              //denyButtonText: `Don't delete`,
            }).then((result) => {
              /* Read more about isConfirmed, isDenied below */
              if (result.isConfirmed) {
                //Swal.fire('Saved!', '', 'success')
                url_call_ajax("<?=base_url("Customer/Media/save_details/")?>/"+id+"/delete",$("#media_div_style_btn"),window.location.reload());
              } 
            //   else if (result.isDenied) {
            //     Swal.fire('Changes are not saved', '', 'info')
            //   }
            });
}
function edit_media(id){
    var fd =new FormData();
    Swal.fire({
  title: 'Submit Media File name',
  input: 'text',
  inputAttributes: {
    autocapitalize: 'off'
  },
  showCancelButton: true,
  confirmButtonText: 'Save',
  showLoaderOnConfirm: true,
  preConfirm: (login) => {
      fd.append("media_name",login);
    return fetch('<?=base_url("Customer/Media/save_details")?>/'+id,   {
        method: "POST",
    body: fd}
)
    
      .then(response => {
        if (!response.ok) {
          throw new Error(response.statusText)
        }
        return response;
      })
      .catch(error => {
        Swal.showValidationMessage(
          `Request failed: ${error}`
        )
      })
  },
  allowOutsideClick: () => !Swal.isLoading()
}).then((result) => {
//   console.log(result) ;
  if (result.value.ok) {
    // alert(result.message);
    window.location.reload();
    // Swal.fire({
    //   title: `${result.value.login}'s avatar`,
    //   imageUrl: result.value.avatar_url
    // })
    
  }else{
       
  }
})
}
    </script>
    <?php $this->endSection(); ?>
