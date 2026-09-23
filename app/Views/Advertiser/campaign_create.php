<?php 
$this->extend("Layout/base_admin");
$this->section("breadcrumb_title_li");
?>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?= current_url() ?>" class="text-muted text-hover-primary"><?=isset($title)?$title:"Please Set Title from CI"?></a>
	</li>
<!--end::Item-->
<?php $this->endSection();?>										
<?php $this->section("main_body");?>
<form action="<?=$action?>" method="post">
<div class="row mt-5 gy-5 g-xl-8">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="col-md-12">
                    <div class="card mb-xl-6 mx-sm-4 p-10">
                        <h1>Add Campaign Details</h1>
                        <div class="fv-row mb-10 fv-plugins-icon-container">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Enter Campaign Name</label>
                            <!--end::Label-->
        
                            <!--begin::Input-->
                            <input type="text" class="form-control form-control-solid" name="campaign_name" value="<?=isset($campaign_name)?$campaign_name:""?>" placeholder="Campaign name" required="">
                            <!--end::Input-->
                            
                        </div>
                        
                        <div class="fv-row mb-10">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Enter Campaign Duration</label>
                            <!--end::Label-->
                    
                            <!--begin::Input-->
                            <input class="form-control form-control-solid" name="campaign_duration" value="<?=isset($campaign_duration)?$campaign_duration:""?>" placeholder="Pick date range" id="kt_daterangepicker">
                            <!--end::Input-->
                        </div>
                        
                        <div class="fv-row mb-10">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Campaign Duration in Days</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="number" step="any" class="form-control form-control-solid" name="campaign_days" placeholder="Duration in Days" required="" value="<?=isset($campaign_days)?$campaign_days:""?>">
                            <!--end::Input-->
                        </div>
                        <!--<div class="fv-row mb-10">-->
                            <!--begin::Label-->
                        <!--    <label class="required fw-semibold fs-6 mb-2">Selected screen</label>-->
                            <!--end::Label-->
                            <!--begin::Input-->
                        <!--    <input type="number" step="any" class="form-control form-control-solid" name="campaign_screens" value="0" required="">-->
                            <!--end::Input-->
                        <!--</div>-->
                        
                        <!--<div class="fv-row mb-10">-->
                            <!--begin::Label-->
                        <!--    <label class="required fw-semibold fs-6 mb-2">Avrage impression per screen per day </label>-->
                            <!--end::Label-->
                            <!--begin::Input-->
                        <!--    <input type="number" step="any" id="av_cpi" class="form-control form-control-solid" value="0" required="">-->
                            <!--end::Input-->
                        <!--</div>-->
                        <!--<div class="fv-row mb-10">-->
                            <!--begin::Label-->
                        <!--    <label class="required fw-semibold fs-6 mb-2">CPI</label>-->
                            <!--end::Label-->
                            <!--begin::Input-->
                        <!--    <input type="number" step="any" id="av_cpi_cost" class="form-control form-control-solid" value="0" required="">-->
                            <!--end::Input-->
                        <!--</div>-->
                        
                        <!-- <div class="fv-row mb-10">-->
                            <!--begin::Label-->
                        <!--    <label class="required fw-semibold fs-6 mb-2">Total impression</label>-->
                            <!--end::Label-->
                            <!--begin::Input-->
                        <!--    <input type="number" step="any" class="form-control form-control-solid" name="total_impression" value="0" required="">-->
                            <!--end::Input-->
                        <!--</div>-->
    
                         <div class="fv-row mb-10">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Total Budget Cost</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="number" step="any" class="form-control form-control-solid" name="campaign_budget_cost" value="<?=isset($campaign_budget_cost)?$campaign_budget_cost:""?>" required="">
                            <!--end::Input-->
                        </div>
                        <input class="btn btn-primary" type="submit" value="Next" name="CheckOut">

                    </div>
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>
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

	</script>
<?php $this->endSection();?>
