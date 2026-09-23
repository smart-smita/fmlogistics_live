<?php 
$this->extend("Layout/base_admin");
?>

<?php 
					$this->section("breadcrumb_title_li");
?>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?=base_url("Customer/Audit_dashboard/OE_Audit")?>" class="text-muted text-hover-primary">Details</a>
	</li>
<!--end::Item-->
<?php 
					$this->endSection();
?>										
<?php 
					$this->section("main_body");
?>
				
		

<?php 
					$this->endSection();
?>