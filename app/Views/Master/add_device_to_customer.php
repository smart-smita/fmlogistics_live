<?php 
//data-ajax-url during add/edit 
//data-ajax-add-url during add Important
?>
<?php $this->extend("Layout/base_admin"); ?>
<?php 
					$this->section("breadcrumb_title_li");
?>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?= current_url() ?>" class="text-muted text-hover-primary"><?=isset($title)?$title:"Please set \$title"?></a>
	</li>
<!--end::Item-->
<?php $this->endSection();?>
<?php $this->section("main_body"); ?>
<?=$table?>
<?php $this->endSection(); ?>


<?php 					$this->section("javascript_section");?>
<script>
function assign_to(obj){
    Swal.fire({
  title: 'Select field Client',
  input: 'select',
  inputOptions: <?=json_encode($client_details)?>,
  inputPlaceholder: 'Select a client',
  showCancelButton: true,
  inputValidator: (value) => {
    return new Promise((resolve) => {
      if (value >0) {
        // resolve("Seleted "+value)
        url_call_ajax($(obj).attr("data-ajax-url")+"/"+value,$(obj));
      } else {
        resolve('You need to select any one client :)')
      }
    })
  }
})

    // url_call_ajax($(obj).attr("data-ajax-url"),$(obj));
}
</script>

<?php $this->endSection(); ?>


