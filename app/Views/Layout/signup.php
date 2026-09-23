<!DOCTYPE html>
<html lang="en">
	<!--begin::Head-->
	<head><base href="<?=base_url()?>">
	<title>Alert System</title>
<meta charset="utf-8" />
<meta name="description" content="ALERT APP" />
<meta name="keywords" content="ALERT APP" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta property="og:locale" content="en_US" />
<meta property="og:type" content="article" />
<meta property="og:title" content="" />
<meta property="og:url" content="" />
<meta property="og:site_name" content="ALERT APP" />
<link rel=" canonical " href="" />
<link rel="shortcut icon " href="<?=base_url()?>/assets/media/app-logo-3.jpeg" />
		<!--begin::Fonts-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
		<!--end::Fonts-->
		<!--begin::Page Vendor Stylesheets(used by this page)-->
		<link href="<?=base_url()?>/assets/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
		<!--end::Page Vendor Stylesheets-->
		<!--begin::Global Stylesheets Bundle(used by all pages)-->
		<link href="<?=base_url()?>/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
		<link href="<?=base_url()?>/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
		<!--end::Global Stylesheets Bundle-->
	</head>
	<!--end::Head-->
	<!--begin::Body-->
	<body id="kt_body" class="bg-body">
		<!--begin::Main-->
		<!--begin::Main-->
		<!--begin::Root-->
		<div class="d-flex flex-column flex-root">
			<!--begin::Authentication - Sign-in -->
			<div class="d-flex flex-column flex-lg-row flex-column-fluid">
				<!--begin::Aside-->
				<div class="d-flex flex-column flex-lg-row-auto w-xl-600px positon-xl-relative" style="background-color: #FFFFFF">
					<!--begin::Wrapper-->
					<div class="d-flex flex-column position-xl-fixed top-0 bottom-0 w-xl-600px scroll-y">
						<!--begin::Content-->
						<div class="d-flex flex-row-fluid flex-column text-center p-10 pt-lg-20">
							<!--begin::Logo-->
							<a href="<?=base_url()?>" class="py-9 mb-5">
								<img alt="Logo" src="<?=base_url()?>/assets/media/app-logo-3.jpeg" style="height:150px" />
							</a>
							<!--end::Logo-->
							<!--begin::Title-->
							<h1 class="fw-bolder fs-2qx pb-5 pb-md-10" style="color: #986923;">Welcome to Alert System</h1>
							<!--end::Title-->
							<!--begin::Description-->
							<p class="fw-bold fs-2" style="color: #986923;">Enhance Safety with Controlled Access: Mobile Reporting, Communication, and SDS at Your Fingertips
							<!--<br />with great Advertising Content Management App</p>-->
							<!--end::Description-->
						</div>
						<!--end::Content-->
						<!--begin::Illustration-->
						<div class="d-flex flex-row-auto bgi-no-repeat bgi-position-x-center bgi-size-contain bgi-position-y-bottom min-h-100px min-h-lg-350px" style="background-image: url(https://www.pngall.com/wp-content/uploads/8/Campaign-PNG-Clipart.png"></div>
						<!--end::Illustration-->
					</div>
					<!--end::Wrapper-->
				</div>
				<!--end::Aside-->
				<!--begin::Body-->
				<div class="d-flex flex-column flex-lg-row-fluid py-10" style="background-color: #F2C98A">
					<!--begin::Content-->
					<div class="d-flex flex-center flex-column flex-column-fluid">
						<!--begin::Wrapper-->
					<div class="w-lg-600px p-10 p-lg-15 mx-auto">
							<!--begin::Form-->
							<form class="form w-100 fv-plugins-bootstrap5 fv-plugins-framework" method="post" action="<?=$action?>" novalidate="novalidate" id="kt_sign_up_form">
								<!--begin::Heading-->
								<div class="mb-10 text-center">
									<!--begin::Title-->
									<h1 class="text-dark mb-3">Create an Account <?=($admin_flag=="2")?"For Publisher":"For Alert System"?></h1>
									<!--end::Title-->
									<!--begin::Link-->
									<div class="fw-bold fs-4" style="color: #986923;">Already have an account? 
									<a href="<?=base_url()?>/Login" class="link-primary fw-bolder" style="color: #986923;">Sign in here</a></div>
									<!--end::Link-->
								</div>
								<!--end::Heading-->
								<!--begin::Action-->
							<!--	<button type="button" class="btn btn-light-primary fw-bolder w-100 mb-10">
								<img alt="Logo" src="/metronic8/demo1/assets/media/svg/brand-logos/google-icon.svg" class="h-20px me-3">Sign in with Google</button>
							-->	<!--end::Action-->
								<!--begin::Separator-->
								<!--<div class="d-flex align-items-center mb-10">
									<div class="border-bottom border-gray-300 mw-50 w-100"></div>
									<span class="fw-bold text-gray-400 fs-7 mx-2">OR</span>
									<div class="border-bottom border-gray-300 mw-50 w-100"></div>
								</div>-->
								<!--end::Separator-->
								<!--begin::Input group-->
								<div class="row fv-row mb-7 fv-plugins-icon-container">
									<!--begin::Col-->
									<div class="col-xl-6">
										<label class="form-label fw-bolder text-dark fs-6">First Name</label>
										<input class="form-control form-control-lg form-control-solid" type="text" placeholder="" name="first_name" autocomplete="off">
									<div class="fv-plugins-message-container invalid-feedback"></div></div>
									<!--end::Col-->
									<!--begin::Col-->
									<div class="col-xl-6">
										<label class="form-label fw-bolder text-dark fs-6">Last Name</label>
										<input class="form-control form-control-lg form-control-solid" type="text" placeholder="" name="last_name" autocomplete="off">
									<div class="fv-plugins-message-container invalid-feedback"></div></div>
									<!--end::Col-->
								</div>
								<!--end::Input group-->
								<!--begin::Input group-->
								<div class="fv-row mb-7 fv-plugins-icon-container">
									<label class="form-label fw-bolder text-dark fs-6">Email</label>
									<input class="form-control form-control-lg form-control-solid" type="email" placeholder="" name="email" autocomplete="off">
								<div class="fv-plugins-message-container invalid-feedback"></div></div>
								<!--end::Input group-->
								<!--begin::Input group-->
								<div class="mb-10 fv-row fv-plugins-icon-container" data-kt-password-meter="true">
									<!--begin::Wrapper-->
									<div class="mb-1">
										<!--begin::Label-->
										<label class="form-label fw-bolder text-dark fs-6">Password</label>
										<!--end::Label-->
										<!--begin::Input wrapper-->
										<div class="position-relative mb-3">
											<input class="form-control form-control-lg form-control-solid" type="password" placeholder="" name="password" autocomplete="off">
											<span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" data-kt-password-meter-control="visibility">
												<i class="bi bi-eye-slash fs-2"></i>
												<i class="bi bi-eye fs-2 d-none"></i>
											</span>
										</div>
										<!--end::Input wrapper-->
										<!--begin::Meter-->
										<div class="d-flex align-items-center mb-3" data-kt-password-meter-control="highlight">
											<div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
											<div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
											<div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
											<div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px"></div>
										</div>
										<!--end::Meter-->
									</div>
									<!--end::Wrapper-->
									<!--begin::Hint-->
									<div class="text-muted">Use 8 or more characters with a mix of letters, numbers &amp; symbols.</div>
									<!--end::Hint-->
								<div class="fv-plugins-message-container invalid-feedback"></div></div>
								<!--end::Input group=-->
								<!--begin::Input group-->
								<div class="fv-row mb-5 fv-plugins-icon-container">
									<label class="form-label fw-bolder text-dark fs-6">Confirm Password</label>
									<input class="form-control form-control-lg form-control-solid" type="password" placeholder="" name="confirm-password" autocomplete="off">
								<div class="fv-plugins-message-container invalid-feedback"></div></div>
								<!--end::Input group-->
								<!--begin::Input group-->
								<div class="fv-row mb-10 fv-plugins-icon-container fv-plugins-bootstrap5-row-invalid">
									<label class="form-check form-check-custom form-check-solid form-check-inline">
										<input class="form-check-input" type="checkbox" name="toc" value="1">
										<span class="form-check-label fw-bold text-gray-700 fs-6" style="color: #986923;">I Agree 
										<a href="#" class="ms-1 link-primary" style="color: #986923;">Terms and conditions</a>.</span>
									</label>
								<!--<div class="fv-plugins-message-container invalid-feedback"><div data-field="toc" data-validator="notEmpty">You must accept the terms and conditions</div></div>-->
								</div>
								<!--end::Input group-->
								<!--begin::Actions-->
								<div class="text-center">
									<button type="button" id="kt_sign_up_submit" class="btn btn-lg btn-primary">
										<span class="indicator-label">Submit</span>
										<span class="indicator-progress">Please wait... 
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
									</button>
								</div>
								<!--end::Actions-->
							<div></div></form>
							<!--end::Form-->
						</div>
						<!--end::Wrapper-->
					</div>
					<!--end::Content-->
					<!--begin::Footer-->
					<div class="d-flex flex-center flex-wrap fs-6 p-5 pb-0">
						<!--begin::Links-->
						<div class="d-flex flex-center fw-bold fs-6">
							Design and Developed By : <a href="https://unitglo.com" target="_blank" style="color: #986923; padding-left:12px; padding-right:12px;"> Unitglo Solutions </a>
							
						</div>
						<!--end::Links-->
					</div>
					<!--end::Footer-->
				</div>
				<!--end::Body-->
			</div>
			<!--end::Authentication - Sign-in-->
		</div>
		<!--end::Root-->
		<!--end::Main-->		<!--end::Main-->
		<script>var hostUrl = "<?=base_url()?>/assets/";</script>
		<!--begin::Javascript-->
		<!--begin::Global Javascript Bundle(used by all pages)-->
		<script src="<?=base_url()?>/assets/plugins/global/plugins.bundle.js"></script>
		<script src="<?=base_url()?>/assets/js/scripts.bundle.js"></script>
		<!--end::Global Javascript Bundle-->
		<!--begin::Page Custom Javascript(used by this page)-->
		<!--end::Page Custom Javascript-->
		<!--end::Javascript-->
		<!--<?php  $this->renderSection("javascript_section"); ?>-->
		<script src="<?=base_url()?>/assets/js/custom/authentication/sign-up/general.js"></script>
		
	</body>
	<!--end::Body-->
</html>