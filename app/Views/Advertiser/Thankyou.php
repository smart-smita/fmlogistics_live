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
<div class=" row">
        <div class=" col-md-12">
        
<!--begin::Engage widget 10-->
<div class="card card-flush h-md-100"> 
    <!--begin::Body-->
    <div class="card-body d-flex flex-column justify-content-between mt-9 bgi-no-repeat bgi-size-cover bgi-position-x-center pb-0" style="background-position: 100% 50%; background-image:url('/metronic8/demo1/assets/media/stock/900x600/42.png')">     
        <!--begin::Wrapper--> 
        <div class="mb-10">
            <!--begin::Title-->      
            <div class="fs-2hx fw-bold text-gray-800 text-center mb-13">
                <span class="me-2">
                    campaign has been successfully established. Click the following link to view information.
                    <br>
                    <span class="position-relative d-inline-block text-danger">
                        <a href="<?=$link?>" class="text-danger opacity-75-hover">Click</a>  

                        <!--begin::Separator-->
                        <span class="position-absolute opacity-15 bottom-0 start-0 border-4 border-danger border-bottom w-100"></span>
                        <!--end::Separator-->                        
                    </span>                     
                </span>                 
                for More details                   
            </div>
            <!--end::Title--> 
            
            <!--begin::Action--> 
            <div class="text-center">
                <a href="<?=$link?>" class="btn btn-sm btn-dark fw-bold" data-bs-toggle="modal" data-bs-target="#kt_modal_upgrade_plan">
                    Check Detaiils
                </a> 
            </div>
            <!--begin::Action--> 
        </div>
        <!--begin::Wrapper-->   

        <!--begin::Illustration-->
        <img class="mx-auto h-150px h-lg-200px  theme-light-show" src="https://preview.keenthemes.com/metronic8/demo1/assets/media/illustrations/misc/upgrade.svg" alt="">   
        <!--end::Illustration-->
    </div>
    <!--end::Body-->
</div>    
<!--end::Engage widget 10-->     </div>
</div>
<?php $this->endSection();?>										

