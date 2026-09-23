<?php 
$form_id= $button_id;
//data-ajax-url during add/edit 
//data-ajax-add-url during add Important
?>
<?php $this->extend("Layout/base_admin"); ?>
<?php 
					$this->section("breadcrumb_title_li");
?>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?= current_url() ?>" class="text-muted text-hover-primary">Screen</a>
	</li>
<!--end::Item-->
<?php $this->endSection();?>
<?php $this->section("main_body"); ?>
<?=$table?>
<?php $this->endSection(); ?>
