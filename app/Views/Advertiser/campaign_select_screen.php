<?php 
$this->extend("Layout/base_admin");
?>

<?php $this->section("breadcrumb_title_li");?>

<style>
    table, tbody, tr, td {
        border:1px solid black;
        padding:5px;
    }
    
</style>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?= current_url() ?>" class="text-muted text-hover-primary"><?=isset($title)?$title:"Please Set Title from CI"?></a>
	</li>
<!--end::Item-->
<?php $this->endSection();?>										
<?php $this->section("main_body");?>
<form action="<?=$action?>" method="post">
<div class=" row">
<h1>Found Screens (<?=count($screens)?>)</h1>


<div class="row">
    <div class="card mb-xl-3 mx-sm-2 p-5">
    <table style="width:100%; margin-bottom: 20px; margin-top: 10px;">
        <tr>
            <td style="width:13%; background-color: #00a1f7; color: white; border-bottom: 1px solid white !important;">Selected States :</td>
            <td style="width:37%;"><?=$state_list?></td>
            <td style="width:13%;  background-color: #00a1f7; color: white; border-bottom: 1px solid white !important;">Selected City :</td>
            <td style="width:37%;"><?=$city_list?></td>
        </tr>
        
        <tr>
            <td style="width:13%;  background-color: #00a1f7; color: white; border-bottom: 1px solid white !important;">Selected Orientation :</td>
            <td style="width:37%;"><?=$orientation?></td>
            <td style="width:13%;  background-color: #00a1f7; color: white; border-bottom: 1px solid white !important;">Selected Location :</td>
            <td style="width:37%;"><?=$location_type?></td>
        </tr>
        
        <tr>
            <td style="width:13%;  background-color: #00a1f7; color: white;"> Selected Aspect Ratio :</td>
            <td style="width:37%;"><?=$aspect_ratio?></td>
            <td style="width:13%;  background-color: #00a1f7; color: white;">Selected Resolution :</td>
            <td style="width:37%;"><?=$resolution?></td>
        </tr>
        
    </table>
    
  <!--  <lable >
        Selected States :<?=$state_list?> &nbsp; &nbsp;
        Selected City :<?=$city_list?> &nbsp; &nbsp;
        Selected Orientation :<?=$orientation?> &nbsp; &nbsp;
        Selected Location :<?=$location_type?> &nbsp; &nbsp;
        Selected Aspect Ratio :<?=$aspect_ratio?> &nbsp; &nbsp;
        Selected Resolution :<?=$resolution?> <br>
    </lable>-->
    <lable style="margin-bottom: 20px; text-align:center;">
        <input class="btn btn-primary" type="button" onclick="window.history.go(-1);" value="Previous" name="CheckOut">
        <input class="btn btn-primary" style="width:100px;" type="submit" value="Next" name="CheckOut">
        
        </lable>
        </div>
    </div>
<?php 
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

foreach($screens as $row) {
                	            echo "<input type=hidden name='screen_details[]' value='".json_encode($row)."'>";
                	           // $row  = json_decode($row,true);
                	            $importation = $importation+$row['daily_impression'];
                	            $cpi = $cpi+$row['cost_for_impression'];
                	        ?>
<div class="col-md-4 all_div city_<?=str_replace(' ', '_', $row['city'])?>" >
<div class="card mb-xl-6 mx-sm-4">
    <div class="card-body card-body border border-3 border-gray-100 border-active-primary btn-active-light-primary" style="padding: 0.5rem 0.5rem !important;" onclick="active_this_div(this)" >
        <input type="checkbox" value='<?=json_encode($row)?>' name="screens[]" class="details" style="display: none;">
        <div class="d-flex flex-wrap flex-sm-nowrap">
            <div class="me-7 mb-4">
                <div class="symbol symbol-100px symbol-lg-125px symbol-fixed position-relative">
                    <img src="<?=($row['device_image']!="")?base_url($row['device_image']):"https://cdn.pixabay.com/photo/2019/06/19/07/13/email-4284157_1280.png"?>" alt="image">
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                    <div class="d-flex flex-column">
                        <!--<input type="checkbox" style="position: absolute; top: 0; right: 0;">-->
                        <div class="d-flex align-items-center mb-2">
                            <h3  class="text-gray-900 text-hover-primary fs-3 fw-bold me-1"><?=$row['device_name']." (".$row['resolution'].")"?></h3>
                        </div>
                        <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                            <b  class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                               Location: <?=$row['screen_location']?>
                            </b>
                            <b  class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                               <?=$row['orientation'].", ".$row['screen_size']?>
                            </b>
                        </div>
                    </div>
                </div>
                <!--<div class="d-flex flex-wrap flex-stack">-->
                <!--    <div class="d-flex flex-column flex-grow-1 ">-->
                <!--        <div class="d-flex flex-wrap">-->
                <!--            <div class="border border-gray-300 border-dashed rounded min-w-120px py-3 px-4 me-3 mb-3">-->
                <!--                <div class="d-flex align-items-center">-->
                <!--                    <i class="ki-duotone ki-arrow-up fs-3 text-success me-2"><span class="path1"></span><span class="path2"></span></i>                                    <div class="fs-2 fw-bold counted" data-kt-countup="true" data-kt-countup-value="4500" data-kt-countup-prefix="$" data-kt-initialized="1"><?=$row['daily_impression']?></div>-->
                <!--                </div>-->
                <!--                <div class="fw-semibold fs-6 text-gray-400">Impression</div>-->
                <!--            </div>-->
                <!--            <div class="border border-gray-300 border-dashed rounded min-w-120px py-3 px-4 me-3 mb-3" title="Cost per impression">-->
                <!--                <div class="d-flex align-items-center">-->
                <!--                    <i class="ki-duotone ki-arrow-up fs-3 text-success me-2"><span class="path1"></span><span class="path2"></span></i>                                    <div class="fs-2 fw-bold counted" data-kt-countup="true" data-kt-countup-value="4500" data-kt-countup-prefix="$" data-kt-initialized="1">₹<?=$row['cost_for_impression']?></div>-->
                <!--                </div>-->
                <!--                <div class="fw-semibold fs-6 text-gray-400" title="Cost per impression">CPI</div>-->
                <!--            </div>-->
                <!--            <div class="border border-gray-300 border-dashed rounded min-w-120px py-3 px-4 me-3 mb-3">-->
                <!--                <div class="d-flex align-items-center">-->
                <!--                    <i class="ki-duotone ki-arrow-down fs-3 text-danger me-2"><span class="path1"></span><span class="path2"></span></i>                                    <div class="fs-2 fw-bold counted" data-kt-countup="true" data-kt-countup-value="80" data-kt-initialized="1"><?=$row['monthly_footfall']?></div>-->
                <!--                </div>-->
                <!--                <div class="fw-semibold fs-6 text-gray-400">Footfall</div>-->
                <!--            </div>-->
                           
                <!--        </div>-->
                <!--        </div>-->
                <!--    </div>-->
                </div>
            </div>
            <div class="row">
                <div class="d-flex flex-wrap flex-stack">
                    <div class="d-flex flex-column flex-grow-1 ">
                        <div class="d-flex flex-wrap">
                            <div class="border border-gray-300 border-dashed rounded min-w-100px py-3 px-4 mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="ki-duotone ki-arrow-up fs-3 text-success me-2"><span class="path1"></span><span class="path2"></span></i>                                    <div class="fs-2 fw-bold counted" data-kt-countup="true" data-kt-countup-value="4500" data-kt-countup-prefix="$" data-kt-initialized="1"><?=$row['daily_impression']?></div>
                                </div>
                                <div class="fw-semibold fs-6 text-gray-400">Impression</div>
                            </div>
                            <div class="border border-gray-300 border-dashed rounded min-w-100px py-3 px-4 mb-2" title="Cost per impression">
                                <div class="d-flex align-items-center">
                                    <i class="ki-duotone ki-arrow-up fs-3 text-success me-2"><span class="path1"></span><span class="path2"></span></i>                                    <div class="fs-2 fw-bold counted" data-kt-countup="true" data-kt-countup-value="4500" data-kt-countup-prefix="$" data-kt-initialized="1">₹<?=$row['cost_for_impression']?></div>
                                </div>
                                <div class="fw-semibold fs-6 text-gray-400" title="Cost per impression">CPI</div>
                            </div>
                            <div class="border border-gray-300 border-dashed rounded min-w-100px py-3 px-4 mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="ki-duotone ki-arrow-down fs-3 text-danger me-2"><span class="path1"></span><span class="path2"></span></i>                                    <div class="fs-2 fw-bold counted" data-kt-countup="true" data-kt-countup-value="80" data-kt-initialized="1"><?=$row['monthly_footfall']?></div>
                                </div>
                                <div class="fw-semibold fs-6 text-gray-400">Footfall</div>
                            </div>
                           
                        </div>
                        </div>
                    </div>
                </div>
            <div class="row">
                <hr>
                <div class="flex-grow-1">
                
                <div class="d-flex flex-wrap flex-stack">
                    <div class="d-flex flex-column flex-grow-1 ">
                        <lable>Each day for hours, there is screen activity.</lable>
                        <div class="d-flex flex-wrap">
                            
                            <?php 
                            foreach($dayNames as $day){
                            ?>
                            <div class="border border-gray-300 border-dashed rounded min-w-120px py-3 px-2 me-3 mb-3">
                                <div class="d-flex align-items-center">
                               <div class="fs-2 fw-bold counted" data-kt-countup="true" data-kt-countup-value="90"  data-kt-initialized="1">
                               <?php
                                $time1 = new DateTime($row[$day.'_start_time']);
                                $time2 = new DateTime($row[$day.'_end_time']);
                                $interval = $time1->diff($time2);
                                echo $interval->format('%H');
                               
                               ?></div>
                                </div>
                                <div class="fw-semibold fs-6 text-gray-400"><?=strtoupper(substr($day, 0, 3));?></div>
                            </div>
                            
                            <?php } ?>
                            
                         
                        </div>
                        </div>
                    </div>
                </div>
                
            </div>
            
         </div>
         
    </div>
            
            </div>
            <?php } ?>
            
            
            
            </div>
        </form>
	    <?php $this->endSection();?>
	    
	       <?php $this->section("javascript_section"); ?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

	<script>
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
	       // update_details();
	    // $(obj).find(".details").click();
	}
	</script>
<?php $this->endSection();?>
