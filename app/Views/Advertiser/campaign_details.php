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
        <div class="row mt-5 gy-5 g-xl-8">
            <?=$table?>
        </div>
<?php $this->endSection();?>
