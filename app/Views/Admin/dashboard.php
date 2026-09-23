<?php 
$this->extend("Layout/base_admin");
?>

<?php 
					$this->section("breadcrumb_title_li");
?>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?=base_url("Customer/Audit_dashboard/OE_Audit")?>" class="text-muted text-hover-primary">Dashboard</a>
	</li>
<!--end::Item-->
<?php 
					$this->endSection();
?>										
<?php 
					$this->section("main_body");
?>
				
								<!--begin::Row-->
								<div class="row gy-5 g-xl-8">
								<div class="row gy-5 g-xl-8">
					    
							
										
										

<div class="col-xl-3">
        <!--begin::Card widget 3-->
<div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-xl-100" style="background-color: #F1416C;">
    <!--begin::Header-->
    <div class="card-header pt-5 mb-3">
        <!--begin::Icon-->
        <div class="d-flex flex-center rounded-circle h-80px w-80px" style="border: 1px dashed rgba(255, 255, 255, 0.4);background-color: #F1416C"> 
            <i class="text-white fs-2qx lh-0 fa fa-user"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span></i>             
        </div>
        <!--end::Icon-->         
    </div>
    <!--end::Header-->

    <!--begin::Card body-->
    <div class="card-body d-flex align-items-end mb-3">
        <!--begin::Info-->
        <div class="d-flex align-items-center">
            <span class="fs-4hx text-white fw-bold me-6"><?=$user_count?></span>

            <div class="fw-bold fs-6 text-white">
                <span class="d-block">Total</span>
                <span class="">Client</span>
            </div>            
        </div>
        <!--end::Info-->
    </div>
    <!--end::Card body-->

    <!--begin::Card footer-->
    <div class="card-footer" style="border-top: 1px solid rgba(255, 255, 255, 0.3);background: rgba(0, 0, 0, 0.15);">
        <!--begin::Progress-->
        <div class="fw-bold text-white py-2">
            <span class="fs-1 d-block"><?=$device_count?></span>
            <span class="opacity-50">Active Screens</span>
        </div>          
        <!--end::Progress-->
    </div>
    <!--end::Card footer-->
</div>
<!--end::Card widget 3-->    
</div>

<div class="col-xl-3">
        <!--begin::Card widget 3-->
<div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-xl-100" style="background-color: #7239EA;">
    <!--begin::Header-->
    <div class="card-header pt-5 mb-3">
        <!--begin::Icon-->
        <div class="d-flex flex-center rounded-circle h-80px w-80px" style="border: 1px dashed rgba(255, 255, 255, 0.4);background-color: #7239EA"> 
            <i class="text-white fs-2qx lh-0 fa fa-users"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span></i>             
        </div>
        <!--end::Icon-->         
    </div>
    <!--end::Header-->

    <!--begin::Card body-->
    <div class="card-body d-flex align-items-end mb-3">
        <!--begin::Info-->
        <div class="d-flex align-items-center">
            <span class="fs-4hx text-white fw-bold me-6"><?= $customer_count ?></span>

            <div class="fw-bold fs-6 text-white">
                <span class="d-block">Total</span>
                <span class="">Customer</span>
            </div>            
        </div>
        <!--end::Info-->
    </div>
    <!--end::Card body-->

    <!--begin::Card footer-->
    <div class="card-footer" style="border-top: 1px solid rgba(255, 255, 255, 0.3);background: rgba(0, 0, 0, 0.15);">
        <!--begin::Progress-->
        <div class="fw-bold text-white py-2">
            <span class="fs-1 d-block"><?=$total_campaign?></span>
            <span class="opacity-50">Total campaign</span>
        </div>          
        <!--end::Progress-->
    </div>
    <!--end::Card footer-->
</div>
<!--end::Card widget 3-->    
</div>

<div class="col-xl-3">
<!--begin::Card widget 3-->
<div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-xl-100" style="background-color: #2196f3;">
    <!--begin::Header-->
    <div class="card-header pt-5 mb-3">
        <!--begin::Icon-->
        <div class="d-flex flex-center rounded-circle h-80px w-80px" style="border: 1px dashed rgba(255, 255, 255, 0.4);background-color: #2196f3"> 
            <i class="text-white fs-2qx lh-0 fa fa-magnet"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span></i>             
        </div>
        <!--end::Icon-->         
    </div>
    <!--end::Header-->

    <!--begin::Card body-->
    <div class="card-body d-flex align-items-end mb-3">
        <!--begin::Info-->
        <div class="d-flex align-items-center">
            <span class="fs-4hx text-white fw-bold me-6"><?=$active_campaign?></span>

            <div class="fw-bold fs-6 text-white">
                <span class="d-block">Active</span>
                <span class="">Campaign</span>
            </div>            
        </div>
        <!--end::Info-->
    </div>
    <!--end::Card body-->

    <!--begin::Card footer-->
    <div class="card-footer" style="border-top: 1px solid rgba(255, 255, 255, 0.3);background: rgba(0, 0, 0, 0.15);">
        <!--begin::Progress-->
        <div class="fw-bold text-white py-2">
            <span class="fs-1 d-block"><?=$deliverd_campaign?></span>
            <span class="opacity-50">Delivering campaign</span>
        </div>          
        <!--end::Progress-->
    </div>
    <!--end::Card footer-->
</div>
<!--end::Card widget 3-->    
</div>


<div class="col-xl-3">
<!--begin::Card widget 3-->
<div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-xl-100" style="background-color: #00bcd4;">
    <!--begin::Header-->
    <div class="card-header pt-5 mb-3">
        <!--begin::Icon-->
        <div class="d-flex flex-center rounded-circle h-80px w-80px" style="border: 1px dashed rgba(255, 255, 255, 0.4);background-color: #00bcd4"> 
            <i class="text-white fs-2qx lh-0 fa fa-magic"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span></i>             
        </div>
        <!--end::Icon-->         
    </div>
    <!--end::Header-->

    <!--begin::Card body-->
    <div class="card-body d-flex align-items-end mb-3">
        <!--begin::Info-->
        <div class="d-flex align-items-center">
            <span class="fs-4hx text-white fw-bold me-6"><?=$total_media?></span>

            <div class="fw-bold fs-6 text-white">
                <span class="d-block">Total</span>
                <span class="">Media</span>
            </div>            
        </div>
        <!--end::Info-->
    </div>
    <!--end::Card body-->

    <!--begin::Card footer-->
    <div class="card-footer" style="border-top: 1px solid rgba(255, 255, 255, 0.3);background: rgba(0, 0, 0, 0.15);">
        <!--begin::Progress-->
        <div class="fw-bold text-white py-2">
            <span class="fs-1 d-block"><?=$total_impressions?></span>
            <span class="opacity-50">Screen Impressions</span>
        </div>          
        <!--end::Progress-->
    </div>
    <!--end::Card footer-->
</div>
<!--end::Card widget 3-->    
</div>

<div class="col-xl-3">
<!--begin::Card widget 3-->
<div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-xl-100" style="background-color: #ff9800;">
    <!--begin::Header-->
    <div class="card-header pt-5 mb-3">
        <!--begin::Icon-->
        <div class="d-flex flex-center rounded-circle h-80px w-80px" style="border: 1px dashed rgba(255, 255, 255, 0.4);background-color: #ff9800"> 
            <i class="text-white fs-2qx lh-0 fa fa-envelope"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span></i>             
        </div>
        <!--end::Icon-->         
    </div>
    <!--end::Header-->

    <!--begin::Card body-->
    <div class="card-body d-flex align-items-end mb-3">
        <!--begin::Info-->
        <div class="d-flex align-items-center">
            <span class="fs-2hx text-white fw-bold me-6"><?= isset($email_settings['email_enabled']) && $email_settings['email_enabled'] ? 'Active' : 'Disabled' ?></span>

            <div class="fw-bold fs-6 text-white">
                <span class="d-block">Email</span>
                <span class="">Status</span>
            </div>            
        </div>
        <!--end::Info-->
    </div>
    <!--end::Card body-->

    <!--begin::Card footer-->
    <div class="card-footer" style="border-top: 1px solid rgba(255, 255, 255, 0.3);background: rgba(0, 0, 0, 0.15);">
        <!--begin::Progress-->
        <div class="fw-bold text-white py-2">
            <span class="fs-4 d-block">SMTP: <?= $email_settings['smtp_status'] ?? 'Unknown' ?></span>
            <span class="opacity-50">Last Test: <?= isset($email_settings['last_test_email_date']) && $email_settings['last_test_email_date'] ? date('d M, Y H:i', strtotime($email_settings['last_test_email_date'])) : 'Never' ?></span>
        </div>          
        <!--end::Progress-->
    </div>
    <!--end::Card footer-->
</div>
<!--end::Card widget 3-->    
</div>

										
												<div class="row g-0 pt-10">
													<?=isset($table_details)?$table_details:""?>
												
												</div>

						
										</div>
								</div>
								<!--end::Row-->
						

<?php 
					$this->endSection();
?>