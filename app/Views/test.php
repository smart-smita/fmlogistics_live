<?php 
$this->extend("Layout/base_admin");

?>

<?php 
					$this->section("breadcrumb_title_li");
?>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="../../demo1/dist/index.html" class="text-muted text-hover-primary">DEMO</a>
	</li>
<!--end::Item-->
<?php 
					$this->endSection();
?>										
<?php 
					$this->section("main_body");
?>
				
	<h1>System setting </h1>
	<input type=email value="<?=$system_setting['default_email']?>">
	<select >
	    <option value="#" <?=($system_setting['currency']=="#")?"selected":""?>>#</option>
	    <option value="$" <?=($system_setting['currency']=="$")?"selected":""?>>$</option>
    </select>
    <h1>Mail setting </h1>

<?php 
					$this->endSection();
?>