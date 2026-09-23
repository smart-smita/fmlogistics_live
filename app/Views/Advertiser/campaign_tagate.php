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

<form action="<?=$action?>" method="post">


<div class="row mt-5 gy-5 g-xl-8">
<div class="col-md-3"> </div>
<div class="col-md-6" >
                 <div class="row gy-5 g-xl-8" style="display: flex; justify-content: center;">
<div class="card mb-xl-6 mx-sm-4 p-10">
            <h1>Target Details</h1>

         <div class="row">
    	    <div class="col-md-6 mt-5">
    	        <lable>State</lable>
        	    <select class="form-select select2" data-control="select2"  multiple="multiple" id="state_list" name="state_list[]" onchange="update_city(this)">
        	        <option data-id="0" >ALL</option>
        	        <?php foreach($states as $row) { ?>
        	        <option id="" data-id="<?=$row['id']?>" value="<?=$row['name']?>"><?=$row['name']?></option>
        	        <?php } ?>
                </select>
	        </div>
	        <div class="col-md-6 mt-5">
	            <lable>Select City</lable>
	            <!--<select id="city_list" name="city_list[]" onchange="update_screens(this)" class="form-select select2" data-control="select2"  multiple="multiple">-->
	            <select id="city_list" name="city_list[]" class="form-select select2" data-control="select2"  multiple="multiple">
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
	        
            	        </div>
            	        
            	        
        <div class="row">
            <div class="col-md-6 mt-5">
    	        <lable>Select Location</lable>
        	    <select class="form-select select2" aria-label="Select Location Type" id="location_type" name="location_type[]"  class="form-select select2" data-control="select2"  multiple="multiple">
                    <option value="0">Select Location Type</option>
                    <option value="In Door">In Door</option>
                    <option value="Out Door">Out Door</option>
                </select>
	        </div>
	        <div class="col-md-6 mt-5">
    	        <lable>Select Orientation</lable>
        	   <select class="form-select select2" aria-label="Select orientation" name="orientation[]"  class="form-select select2" data-control="select2"  multiple="multiple">
                    <option value="0">Select Orientation</option>
                    <option value="Horizontal">Horizontal</option>
                    <option value="Vertical">Vertical</option>
                </select>
	        </div>
        
        <div class="col-md-6 mt-5">
    	                            <lable>Select resolution</lable>
        	                        <select class="form-select select2" aria-label="Select resolution" name="resolution[]"  class="form-select select2" data-control="select2"  multiple="multiple">
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
                   <div class="col-md-6 mt-5">
    	        <lable>Select aspect ratio</lable>
        	                        <select class="form-select select2" aria-label="Select aspect ratio" name="aspect_ratio[]"  class="form-select select2" data-control="select2"  multiple="multiple">
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
	        	        
<div class="col-md-12 mt-5">
    <input type="submit" class="btn btn-primary" style="width:100%;" value="Next" >
	            <!--<button type="button" class="btn btn-primary me-10" id="showScreens1" onclick="showScreens(this)">-->
             <!--       <span class="indicator-label">-->
                        
             <!--       </span>-->
             <!--       <span class="indicator-progress">-->
             <!--           Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>-->
             <!--       </span>-->
             <!--   </button>-->
	        
            </div>
	     
	    </div>
	    </div>
	    </div>
<div class="col-md-3"></div>
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
	</script>
<?php $this->endSection();?>
