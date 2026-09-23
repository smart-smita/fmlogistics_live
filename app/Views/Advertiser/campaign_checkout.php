<?php 
$this->extend("Layout/base_admin");
$importation = 0;
$cpi = 0;

$dayNames = array(
    'sunday',
    'monday', 
    'tuesday', 
    'wednesday', 
    'thursday', 
    'friday', 
    'saturday', 
 );
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
									<div class="col-sm-6 col-xl-6">
										

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
<form action="<?=$action?>" method="post" onsubmit="return validate();">
<input type="hidden" name="media_id" id="media_id_arr">
	
        <div class="row mt-5 gy-5 g-xl-8">
            
            <div class="col-md-6">
                <div class="col-md-12" >
                    <div class="card mb-xl-6 mx-sm-4 p-10">
                        <h1>Add campaign details</h1>
                        <!--begin::Row-->

								<div class="row g-6 g-xl-9">
    								<div class="progress">
                                        <div class="progress-bar"></div>
                                    </div>
                                <div id="uploadStatus"></div>

									<!--begin::Col-->
									<div class="row row g-6 g-xl-9 " id="div_list_details" style="height: 250px;
    overflow: auto;">
									</div>
									<!--end::Col-->
									
									
								</div>
								<!--end::Row-->
								
                        <div class="fv-row mb-10 fv-plugins-icon-container">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Enter campaign name</label>
                            <!--end::Label-->
        
                            <!--begin::Input-->
                            <input type="text" class="form-control form-control-solid" name="campaign_name" placeholder="Campaign name" required>
                            <!--end::Input-->
                            
                        </div>
                        
                        <div class="fv-row mb-10">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Enter campaign duration</label>
                            <!--end::Label-->
                    
                            <!--begin::Input-->
                            <input class="form-control form-control-solid" name="campaign_duration" placeholder="Pick date range" id="kt_daterangepicker" />
                            <!--end::Input-->
                        </div>
                        
                        <div class="fv-row mb-10">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Campaign Duration in days</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="number" step="any"class="form-control form-control-solid" name="campaign_days" placeholder="Pick date range" required>
                            <!--end::Input-->
                        </div>
                        <div class="fv-row mb-10">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Selected screen</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="number" step="any"class="form-control form-control-solid" name="campaign_screens" value="0" required>
                            <!--end::Input-->
                        </div>
                        
                        <div class="fv-row mb-10">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Avrage impression per screen per day </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="number" step="any"id="av_cpi" class="form-control form-control-solid" value="<?=round($importation/count($screens),2)?>" required>
                            <!--end::Input-->
                        </div>
                        <div class="fv-row mb-10">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">CPI</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="number" step="any" id="av_cpi_cost" class="form-control form-control-solid" value="<?=round($cpi/count($screens),2)?>" required>
                            <!--end::Input-->
                        </div>
                        
                         <div class="fv-row mb-10">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Total impression</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="number" step="any"class="form-control form-control-solid" name="total_impression" value="<?=$importation?>" required>
                            <!--end::Input-->
                        </div>
    
                         <div class="fv-row mb-10">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Total Cost</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="number" step="any"class="form-control form-control-solid" name="campaign_cpi_cost" value="" required>
                            <!--end::Input-->
                        </div>
                        <input class="btn btn-sm btn-light btn-success" type="submit" value="Proceed" name="CheckOut">

                    </div>
                </div>
            </div>
             <div class="col-md-6" >
                 <div class="row gy-5 g-xl-8"
                 style="
    display: flex;
    justify-content: center;
">
         <div class="row">

    	    <div class="col-md-5">
    	        <lable>Select State</lable>
        	    <select class="form-select select2" data-control="select2"  multiple="multiple" id="state_list" onchange="update_city(this)">
        	        <option data-id="0" >ALL</option>
        	        <?php foreach($states as $row) { ?>
        	        <option id="" data-id="<?=$row['id']?>" value="<?=$row['name']?>"><?=$row['name']?></option>
        	        <?php } ?>
                </select>
	        </div>
	        <div class="col-md-5">
	            <lable>Select City</lable>
	            <select id="city_list" onchange="update_screens(this)" class="form-select select2" data-control="select2"  multiple="multiple">
	                <option data-id="0" value="0">ALL</option>
	                </select>
	            <select id="temp_city_list" style="display:none">
	                <option data-id="0" value="0">ALL</option>
	                <?php foreach($cities as $row) { ?>
	                    <option id="" class="all_city state_id_<?=$row['state_id']?>" value="<?=$row['city_name']?>"><?=$row['city_name']?></option>
	                <?php } ?>
	            </select>
	        <br>

	        </div>
	        <div class="col-md-2 mt-5">
	            <button type="button" class="btn btn-primary me-10" id="showScreens1" onclick="showScreens(this)">
                    <span class="indicator-label">
                        Show
                    </span>
                    <span class="indicator-progress">
                        Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
	        
            </div>
            	        </div>
            	        
            	        
        <div class="row">
            <div class="col-md-5">
    	        <lable>Select Location</lable>
        	    <select class="form-select select2" aria-label="Select Location Type" id="location_type"  class="form-select select2" data-control="select2"  multiple="multiple">
                    <option value="0">Select Location Type</option>
                    <option value="In Door">In Door</option>
                    <option value="Out Door">Out Door</option>
                </select>
	        </div>
	        <div class="col-md-5">
    	        <lable>Select Orientation</lable>
        	   <select class="form-select select2" aria-label="Select orientation" id="orientation"  class="form-select select2" data-control="select2"  multiple="multiple">
                    <option value="0">Select Orientation</option>
                    <option value="Horizontal">Horizontal</option>
                    <option value="Vertical">Vertical</option>
                </select>
	        </div>
        
        <div class="col-md-5">
    	                            <lable>Select resolution</lable>
        	                        <select class="form-select select2" aria-label="Select resolution" id="resolution"  class="form-select select2" data-control="select2"  multiple="multiple">
                                        <option>Select Resolution</option>
                                        <option value="800x600"> 800 x 600 </option>
                                        <option value="1280x720">1280 x 720</option>
                                        <option value="1440x900">1440 x 900</option>
                                        <option value="1600x900">1600 x 900</option>
                                        <option value="1920x1080">1920 x 1080</option>
                                        <option value="1900x1200">1900 x 1200</option>
                                        <option value="2880x1800">2880 x 1800</option>
                                        <option value="3840x2160">3840 x 2160</option>
                                    </select>
        </div>
                   <div class="col-md-5">
    	        <lable>Select aspect ratio</lable>
        	                        <select class="form-select select2" aria-label="Select aspect ratio" id="aspect_ratio"  class="form-select select2" data-control="select2"  multiple="multiple">
                                            <option>Select Aspect Ratio</option>
                                            <option value="720p">720 P</option>
                                            <option value="1080p">1080 P</option>
                                            <option value="2K">2 K</option>
                                            <option value="3K">3 K</option>
                                            <option value="3KUHD">3 K UHD</option>
                                            <option value="4K">4 K</option>
                                            <option value="4KUHD"> 4 K UHD</option>
                                            </select>
                                            </div>
                                            </div>
	        	        <div class="col-md-11 mt-0">
	        <lable class="p-2 pb-10" id="found_count">Screen Found (0)</lable>
	        </div>

	     
	    </div>
	    <div style="
    overflow: auto;
    height: 1100px;
" id="screens_list_dynamic">
	    
                	       
            </div>
           </div>
        </div>
        
    
</form>
<?php $this->endSection();?>

<?php $this->section("javascript_section"); ?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<!--<link href="<?=base_url()?>assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css"/>-->
<!--<script src="<?=base_url()?>assets/plugins/global/plugins.bundle.js"></script>-->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


	<script>

	function showScreens(obj){
	    

	    ajax_call("<?=$screenListAjax?>",{aspect_ratio:$("#aspect_ratio").val().toString(),resolution:$("#resolution").val().toString(),orientation:$("#orientation").val().toString(),location_type:$("#location_type").val().toString(),state_list:$("#state_list").val().toString(),city_list:$("#city_list").val().toString()},$("#showScreens1"),function(res){
	       // console.log(res);
	       $("#screens_list_dynamic").html(res);
	               $("#found_count").html("Screen Found ("+ $('.all_div:not([style*="display: none;"])').length+") ");

	    });
	   // screens_list_dynamic

	}
	// Define form element
	$('input[name="campaign_duration"]').daterangepicker({
          minDate: new Date(),
	},
	function (){
	    setTimeout(function(){
	    update_details();
	    },100);
	   // alert($(this).val()+" ads : -"+getDays());
	} );
	function update_details(){
	        var days =getDays();
	        $('input[name="campaign_days"]').val(days);
	        $('input[name="campaign_cpi_cost"]').val(getCost(days));
	        $('input[name="total_impression"]').val((parseFloat($("#av_cpi_cost").val())*parseFloat($("#av_cpi").val())).toFixed(3));
	}
	function getCost(days){
	  return Math.round((parseFloat($("#av_cpi_cost").val())*parseFloat($("#av_cpi").val()))*days);
	}
        function getDays() {
            var d = $('input[name="campaign_duration"]').val().split(" - ");
            var date1 = new Date(d[0]);
            var date2 = new Date(d[1]);
            var milli_secs = date1.getTime() - date2.getTime();
             
            // Convert the milli seconds to Days 
            var days = milli_secs / (1000 * 3600 * 24);
            // document.getElementById("ans").innerHTML =
            return Math.round(Math.abs(days));
        }



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
    function validate(){
        
        if($("#media_id_arr").val()==""){
			toastr.error("<b>Please select media.</b>");
            return false;
        }else{
            return true;
        }
        
        
        
    }
    function active_this_div(obj){
 	    var checkBoxes =  $(obj).find(".details")[0]; 
 	  
 	  var objJson = JSON.parse($(checkBoxes).val());
 	  console.log(objJson);
 	  //cons
        $(checkBoxes).prop("checked", !$(checkBoxes).prop("checked"));
	    $(obj).toggleClass("active");
	    if( $(checkBoxes).prop("checked")){
	        $("input[name=campaign_screens]").val((Number($("input[name=campaign_screens]").val())+1));
	        
	       // $("#av_cpi_cost").val(Math.round(
	       //     (parseFloat($("#av_cpi_cost").val())+parseFloat(objJson.cost_for_impression))));
	       $("#av_cpi_cost").val((
	            (parseFloat($("#av_cpi_cost").val())+parseFloat(objJson.cost_for_impression))).toFixed(3));
	            $("#av_cpi").val((parseFloat($("#av_cpi").val())+parseFloat(objJson.daily_impression)))
// .toFixed(3)	            
	        
	       // active_this_div
	        }else{
	        $("input[name=campaign_screens]").val((Number($("input[name=campaign_screens]").val())-1));
	        $("#av_cpi_cost").val(Math.round(
	            (parseFloat($("#av_cpi_cost").val())-parseFloat(objJson.cost_for_impression))));
	        }
	        update_details();
	    // $(obj).find(".details").click();
	}
	function update_screens(obj){
	   // console.log($(obj).val());
	   var  t = $(obj).val();
	   $(".all_div").hide();
	   for(val in t){
	    var cname = t[val].replace(/ /g,"_");
	    if(cname==0){
          $('#city_list option:not([style*="display: none;"])').each(function() {
	            var tcname = $(this).val().replace(/ /g,"_");
	            $(".city_"+tcname).show();
          });
    	    }else{
    	        $(".city_"+cname).show();
    	    }
	   }
        $("#found_count").html("Screen Found ("+ $('.all_div:not([style*="display: none;"])').length+") ");

	}
	function update_city(obj){
	       // $(".all_city").hide();
	       $("#city_list").html('<option data-id="0" value="0">ALL</option>');
	       // if($("#state_list").find(':selected').length=0)
        	    $.each($("#state_list").find(':selected'),function(index,obj){
        	        var id = $(obj).data("id");
	                console.log(obj);
	                if(id>0){
	                    $.each($(".state_id_"+id) , function(ind,ob){
	                       // console.log(ob);
	                        $("#city_list").append($(ob).clone());
	                    });
	                }else{
	                    $("#city_list").append( $(".all_city").clone());
	                }
    	    });
    	$("#city_list").select2();
	   // $("#city_list").val().trigger('change');        
    	    
    	
	}
// function update_city() {
//     var selectedStates = $("#state_list").val();

//     $("#city_list").html('<option data-id="0" value="0">ALL</option>');

//     if (selectedStates) {
//         $.each(selectedStates, function(index, id) {
//             // $("#state_list").eq(index).data("id");
//             $.each($(".state_id_" + $("#state_list").eq(index).data("id")), function(ind, ob) {
//                 $("#city_list").append(ob);
//             });
//         });
//     } else {
//         $("#city_list").html('<option data-id="0" value="0">ALL</option>');
//     }

//     $("#city_list").select2();
//     $("#city_list").val(0).trigger('change');
// }

	</script>
	<?php $this->endSection(); ?>
	
	
	

