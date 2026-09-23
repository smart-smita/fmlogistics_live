<?php 
$this->extend("Layout/base_admin");
?>

<?php $this->section("breadcrumb_title_li");?>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?= current_url() ?>" class="text-muted text-hover-primary"><?=isset($title)?$title:"Please Set Title from CI"?></a>
	</li>
<!--end::Item-->
<?php $this->endSection();?>										
<?php $this->section("main_body");?>
<div id="media_div_style" style="display:none">

<!--begin::Card-->
									<div class="col-sm-4 col-xl-4">
										

										<div class="card h-100" style="border-radius:15px; box-shadow: 1px 5px 10px 1px rgb(37 37 37 / 60%);">
											<!--begin::Card body-->
											<div class="card-body d-flex flex-column overlay" style="padding: 0rem 1.05rem;  background-color: lightgrey; border-top-left-radius: 15px;border-top-right-radius: 15px; height: 110px;
    max-width: 100%; position: relative; overflow: hidden; object-fit: cover;">
												<!--begin::Heading-->
												<div class="fs-2tx fw-bolder">
												    <img style="width: 100%; height: 108px; object-fit: contain; top: 50%; left: 50%;" src="{{media_url}}">
												</div>
												<div class="overlay-layer card-rounded bg-dark bg-opacity-25">
												<!--<a href="javascript:edit_media('{{media_id}}')"> <i class="fa fa-edit" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i> </a>-->
												<!--<a href="javascript:remove_media('{{media_id}}')"> <i class="fa fa-trash" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i> </a>-->
												<a href="javascript:select_media('{{media_id}}')"> <i class="fa fa-plus icon-{{media_id}}" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i> </a>
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
<input type="hidden" id="user_id" value="<?=$user_id?>">
<input type="hidden" id="customer_id" value="<?=$customer_id?>">

<form action="<?=$action?>" method="post">
<div class=" row">
<h1><?=$title?></h1>


<div class="row">
        
        <lable >
            <input class="btn btn-sm btn-light btn-success" type="submit" value="Next" name="CheckOut"><br>
        </lable>
</div>

       <div id="div_list_details" class="row">    </div>
            
            
</div>
        </form>
	    <?php $this->endSection();?>
	    
	       <?php $this->section("javascript_section"); ?>

	<script>
	        ///////// ************* MEDIA ************ //////
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
                    $(".progress").hide();
                    
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
                    
    // setTimeout( $(".progress-bar").width('0%'),1000);

    }
    setTimeout(get_media_list(),1000);
    var arr_selected = [];
    function select_media(id){
        if($(".icon-"+id).is(".fa-plus")){
            arr_selected.push(id);
        }else{
            let index = arr_selected.indexOf(id);
            if (index !== -1) {
              // Use splice to remove the element
              arr_selected.splice(index, 1);
            }            
        }
        console.log(arr_selected);
         $(".icon-"+id).toggleClass('fa-plus fa-minus-square');
         $("#media_id_arr").val(arr_selected.join(","));

    }
	</script>
<?php $this->endSection();?>
