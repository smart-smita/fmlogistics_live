<?php 
$this->extend("Layout/base_admin");
?>

<?php 
					$this->section("breadcrumb_title_li");
?>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?= current_url() ?>" class="text-muted text-hover-primary">DASHBOARD</a>
	</li>
<!--end::Item-->
<?php 
					$this->endSection();
?>										
<?php 
					$this->section("main_body");
?>
	
					<div class="row gy-5 g-xl-8">
					    
								  
				<?php 
				if(isset($tiles)){
				    foreach($tiles as $rows){
				
				?>						
					 <div class="col-md-4">
	                    <div class="card card-flush mb-5 mb-xl-10">
											<!--begin::Header-->
											<div class="card-header pt-5" style="min-height: 135px !important; <?=isset($rows['style'])?$rows['style']:""?>">
												<!--begin::Title-->
												<div class="card-title d-flex flex-column" style="">
												    <span class="bi bi-bar-chart-line-fill" ></span>
                                                    
                                                    <!--begin::Amount-->
													<span class="fs-2hx fw-bolder me-2 lh-1 ls-n2" style="color:white;"><?=$rows['count']?></span>
													<!--end::Amount-->
													<!--begin::Subtitle-->
													<span class="pt-1 fw-bold fs-6" style="color:white;"><?=$rows['title']?></span>
													<!--end::Subtitle-->
												</div>
												<!--end::Title-->
											</div>
											<!--end::Header-->
										</div>
								</div>
								<?php } } ?>
										</div>
										<?=isset($table_details)?$table_details:""?>
										

<?php 
					$this->endSection();
?>
	<?php $this->section("javascript_section"); ?>
	<script>
	
	</script>
	<?php $this->endSection(); ?>

