<?php $this->extend("Layout/base_admin"); ?>
<?php 
					$this->section("breadcrumb_title_li");
?>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?= current_url() ?>" class="text-muted text-hover-primary">PlayList</a>
	</li>
<!--end::Item-->
<?php $this->endSection();?>	

<?php $this->section("main_body"); ?>

<div id="kt_content_container" class="container-xxl">
								
								<!--begin::Toolbar-->
								<div class="d-flex flex-wrap flex-stack mb-6">
									<!--begin::Title-->
									<h3 class="fw-bolder my-2">My PlayList 
									<span class="fs-6 text-gray-400 fw-bold ms-1"><?=isset($table_data)?"Found : ".count($table_data)." PlayList":""?></span></h3>
									<!--end::Title-->
									<!--begin::Controls-->
									<div class="d-flex align-items-center my-2">
										<!--begin::Select wrapper-->
										
										<!--end::Select wrapper-->
									<a href="<?=base_url("Customer/Playlist/add")?>"><button class="btn btn-primary btn-sm">Add PlayList</button></a>
									
									</div>
									<!--end::Controls-->
								</div>
								<!--end::Toolbar-->
								<!--begin::Row-->

								<div class="row g-6 g-xl-9">
								    							
<?php 
if(isset($table_data))
foreach($table_data as $row){
    
?>
									<!--begin::Col-->
									<div class="col-sm-4 col-xl-4">
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
												</div>
												<div class="overlay-layer card-rounded bg-dark bg-opacity-25">
												<a href="<?=base_url("Customer/Playlist/add/".$row['playlist_id'])?>"> <i class="fa fa-edit" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i> </a>
												<a href="javascript:confirm('Do you sure want to delete')?url_call_ajax('<?=base_url("Customer/Playlist/save_details/".$row['playlist_id']."/delete")?>',null,setTimeout(window.location.reload(),3000)):null" onclick=""> <i class="fa fa-trash" style="font-size: 22px; color: white; padding-left: 15px; padding-right: 15px;"></i> </a>
												</div>
												
											</div>
											<!--end::Card body-->
											
												<!--begin::Card header-->
											<div class="card-header flex-nowrap border-0" style="">
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
								<!--end::Row-->
								
							
							</div>
							
							
							
							
	<?php $this->endSection(); ?>
	
	
	<?php $this->section("javascript_section"); ?>

    <?php $this->endSection(); ?>
