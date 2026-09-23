<!DOCTYPE html>
<html lang="en">
	<!--begin::Head-->
	<head><base href="<?=base_url()?>">
	<title>Alert System</title>
<meta charset="utf-8" />
<meta name="description" content="Alert APP" />
<meta name="keywords" content="Alert APP" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta property="og:locale" content="en_US" />
<meta property="og:type" content="article" />
<meta property="og:title" content="" />
<meta property="og:url" content="" />
<meta property="og:site_name" content="Alert" />
<link rel=" canonical " href="https://unitglo.com/ " />
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
							<a href="<?=base_url()?>" class="py-1 mb-5">
								<img alt="Logo" src="<?=base_url()?>/assets/media/app-logo-3.jpeg" style="height:150px;" />
							</a>
							<!--end::Logo-->
							<!--begin::Title-->
							<h1 class="fw-bolder fs-2qx pb-5 pb-md-10" style="color: #986923;">Welcome to Alert System</h1>
							
							<!--end::Title-->
							<!--begin::Description-->
							<p class="fw-bold fs-2" style="color: #986923;">Enhance Safety with Controlled Access: Mobile Reporting, Communication, and SDS at Your Fingertips<br><br>
						<!--	<b>Powered by Unitglo Solutions PVT LTD</b>--></p>
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
						<div class="w-lg-500px p-10 p-lg-15 mx-auto">
							<!--begin::Form-->
							<form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" data-kt-redirect-url="" action="<?=$action?>">
								<!--begin::Heading-->
								<div class="text-center mb-10">
									<!--begin::Title-->
									<!--<h1 class="text-dark mb-3">PDOOH DSP For Publisher</h1>-->
									<!--end::Title-->
									<!--begin::Link-->
									<!--<div class=" fw-bold fs-4" style="color: #986923;">New Here? -->
									<!--<a href="<?=base_url()?>/Login/signup" class="link-primary fw-bolder" style="color: #986923;">Create an Account >></a></div>-->
									<!--end::Link-->
								</div>
								<div class="text-center mb-10">
									<!--begin::Title-->
									<!--<h1 class="text-dark mb-3">PDOOH SSP For Advertisers</h1>-->
									<!--end::Title-->
									<!--begin::Link-->
									<!--<div class=" fw-bold fs-4" style="color: #986923;">New Here? -->
									<!--<a href="<?=base_url()?>/Login/signup/3" class="link-primary fw-bolder" style="color: #986923;">Create an Account >></a></div>-->
									<!--end::Link-->
								</div>
								<!--begin::Heading-->
								<!--begin::Input group-->
								<div class="fv-row mb-10">
									<!--begin::Label-->
									<label class="form-label fs-6 fw-bolder text-dark">Email</label>
									<!--end::Label-->
									<!--begin::Input-->
									<input class="form-control form-control-lg form-control-solid" type="text" name="email" autocomplete="off" />
									<!--end::Input-->
								</div>
								<!--end::Input group-->
								<!--begin::Input group-->
								<div class="fv-row mb-10">
									<!--begin::Wrapper-->
									<div class="d-flex flex-stack mb-2">
										<!--begin::Label-->
										<label class="form-label fw-bolder text-dark fs-6 mb-0">Password</label>
										<!--end::Label-->
										<!--begin::Link-->
										<!-- <a href="<?=base_url()?>/Login/reset_password" class="link-primary fs-6 fw-bolder">Forgot Password ?</a> -->
										<!--end::Link-->
									</div>
									<!--end::Wrapper-->
									<!--begin::Input-->
									<input class="form-control form-control-lg form-control-solid" type="password" name="password" autocomplete="off" />
									<!--end::Input-->
								</div>
								<!--end::Input group-->
								<!--begin::Actions-->
								<div class="text-center">
									<!--begin::Submit button-->
									<button type="submit" id="kt_sign_in_submit" class="btn btn-lg btn-primary w-100 mb-5">
										<span class="indicator-label">Continue</span>
										<span class="indicator-progress">Please wait... 
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
									</button>
									<!--end::Submit button-->

									<!--begin::Role Selection Modal-->
									<div class="modal fade" id="roleSelectionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
										<div class="modal-dialog modal-dialog-centered">
											<div class="modal-content">
												<div class="modal-header">
													<h5 class="modal-title">Login As</h5>
												</div>
												<div class="modal-body">
													<p class="text-muted mb-4">Multiple roles found for this email. Please select a role to continue.</p>
									<div id="roleOptionsList" class="d-grid gap-3">
										<!-- Roles will be dynamically inserted here -->
									</div>
								</div>
								<div class="modal-footer justify-content-center border-0 pt-0">
									<button type="button" class="btn btn-light-danger" data-bs-dismiss="modal">Cancel</button>
								</div>
											</div>
										</div>
									</div>
									<!--end::Role Selection Modal-->
									<!--begin::Separator-->
								
									<!--end::Separator-->
									<!--begin::Google link-->
								<!--	<a href="#" class="btn btn-flex flex-center btn-light btn-lg w-100 mb-5">
									<img alt="Logo" src="/metronic8/demo1/assets/media/svg/brand-logos/google-icon.svg" class="h-20px me-3" />Continue with Google</a>
								-->	<!--end::Google link-->
									<!--begin::Google link-->
								<!--	<a href="#" class="btn btn-flex flex-center btn-light btn-lg w-100 mb-5">
									<img alt="Logo" src="/metronic8/demo1/assets/media/svg/brand-logos/facebook-4.svg" class="h-20px me-3" />Continue with Facebook</a>
								-->	<!--end::Google link-->
									<!--begin::Google link-->
								<!--	<a href="#" class="btn btn-flex flex-center btn-light btn-lg w-100">
									<img alt="Logo" src="/metronic8/demo1/assets/media/svg/brand-logos/apple-black.svg" class="h-20px me-3" />Continue with Apple</a>
								-->	<!--end::Google link-->
								</div>
								<!--end::Actions-->
							</form>
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
		<!--<?php  $this->renderSection("javascript_section"); ?>-->
		<script src="<?=base_url()?>/assets/js/custom/authentication/sign-in/general.js"></script>
		<!--end::Page Custom Javascript-->
		<!--end::Javascript-->
		
	</body>
	<!--end::Body-->
</html>