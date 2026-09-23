	<!DOCTYPE html>

	<html lang="en">
	<!--begin::Head-->

	<head>
		<base href="<?= base_url() ?>">
		<title>Alert System</title>
		<meta charset="utf-8" />
		<meta name="description" content="DG PLAY ADVERTISING APP" />
		<meta name="keywords" content="DG PLAY ADVERTISING APP" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta property="og:locale" content="en_US" />
		<meta property="og:type" content="DG PLAY ADVERTISING APP" />
		<meta property="og:title" content="DG PLAY ADVERTISING APP" />
		<meta property="og:url" content="DG PLAY ADVERTISING APP" />
		<meta property="og:site_name" content="DG PLAY ADVERTISING APP" />
		<link rel="canonical" href="" />
		<!--<link rel="shortcut icon " href="<?= base_url("uploads/site/favicon.ico") ?>" />-->
		<link rel="shortcut icon " href="<?= base_url() ?>/assets/media/app-logo-3.jpeg">
		<!--begin::Fonts-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
		<!--end::Fonts-->
		<!--begin::Page Vendor Stylesheets(used by this page)-->
		<link href="<?= base_url() ?>/assets/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet"
			type="text/css" />
		<!--end::Page Vendor Stylesheets-->
		<!--begin::Global Stylesheets Bundle(used by all pages)-->
		<link href="<?= base_url() ?>/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
		<link href="<?= base_url() ?>/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
		<link href="<?= base_url() ?>/assets/css/custom-typography.css" rel="stylesheet" type="text/css" />
		<!--end::Global Stylesheets Bundle-->
		<link href="<?= base_url() ?>/assets/css/custom/common.css" rel="stylesheet" type="text/css" />
		<link href="<?= base_url() ?>/assets/css/custom/forms.css" rel="stylesheet" type="text/css" />
		<link href="<?= base_url() ?>/assets/css/custom/cards.css" rel="stylesheet" type="text/css" />
		<link href="<?= base_url() ?>/assets/css/custom/select2.css" rel="stylesheet" type="text/css" />
		<link href="<?= base_url() ?>/assets/css/custom/utilities.css" rel="stylesheet" type="text/css" />
		<link href="<?= base_url() ?>/assets/css/custom/buttons.css" rel="stylesheet" type="text/css" />
	</head>
	<!--end::Head-->

	<!--begin::Body-->

	<body id="kt_body"
		class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed aside-enabled aside-fixed"
		style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
		<!--begin::Main-->
		<!--begin::Root-->
		<div class="d-flex flex-column flex-root">
			<!--begin::Page-->
			<div class="page d-flex flex-row flex-column-fluid">
				<!--begin::Aside-->
				<div id="kt_aside" class="aside aside-dark aside-hoverable" data-kt-drawer="true"
					data-kt-drawer-name="aside" data-kt-drawer-activate="{default: true, lg: false}"
					data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'200px', '300px': '250px'}"
					data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_aside_mobile_toggle">
					<!--begin::Brand-->
					<div class="aside-logo flex-column-auto" id="kt_aside_logo"
						style="border-bottom: 1px solid white; height:165px !important; padding:0px !important; ">
						<!--begin::Logo-->
						<img alt="Logo" src="<?= base_url() ?>/assets/media/app-logo-3.jpeg" class="h-70px logo"
							style="width:100%; height:100% !important;" />

						<!--	<img alt="Logo" src="<?= base_url() ?>/assets/media/logos/ASTI-logo.png" class="h-50px logo" />-->

						<!--end::Logo-->
						<!--begin::Aside toggler-->
						<div id="kt_aside_toggle" class="btn btn-icon w-auto px-0 btn-active-color-primary aside-toggle"
							data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
							data-kt-toggle-name="aside-minimize">
							<!--begin::Svg Icon | path: icons/duotune/arrows/arr079.svg-->
							<span class="svg-icon svg-icon-1 rotate-180">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
									fill="none">
									<path opacity="0.5"
										d="M14.2657 11.4343L18.45 7.25C18.8642 6.83579 18.8642 6.16421 18.45 5.75C18.0358 5.33579 17.3642 5.33579 16.95 5.75L11.4071 11.2929C11.0166 11.6834 11.0166 12.3166 11.4071 12.7071L16.95 18.25C17.3642 18.6642 18.0358 18.6642 18.45 18.25C18.8642 17.8358 18.8642 17.1642 18.45 16.75L14.2657 12.5657C13.9533 12.2533 13.9533 11.7467 14.2657 11.4343Z"
										fill="black" />
									<path
										d="M8.2657 11.4343L12.45 7.25C12.8642 6.83579 12.8642 6.16421 12.45 5.75C12.0358 5.33579 11.3642 5.33579 10.95 5.75L5.40712 11.2929C5.01659 11.6834 5.01659 12.3166 5.40712 12.7071L10.95 18.25C11.3642 18.6642 12.0358 18.6642 12.45 18.25C12.8642 17.8358 12.8642 17.1642 12.45 16.75L8.2657 12.5657C7.95328 12.2533 7.95328 11.7467 8.2657 11.4343Z"
										fill="black" />
								</svg>
							</span>
							<!--end::Svg Icon-->
						</div>
						<!--end::Aside toggler-->
					</div>
					<!--end::Brand-->
					<!--begin::Aside menu-->
					<!--<div class="aside-menu flex-column-fluid" style="background-color: #efede6ab;">-->
					<div class="aside-menu flex-column-fluid">
						<!--begin::Aside Menu-->
						<div class="hover-scroll-overlay-y my-5 my-lg-5" id="kt_aside_menu_wrapper" data-kt-scroll="true"
							data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
							data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer"
							data-kt-scroll-wrappers="#kt_aside_menu" data-kt-scroll-offset="0">
							<!--begin::Menu-->
							<div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500"
								id="#kt_aside_menu" data-kt-menu="true">

								<!-- <div class="menu-item">
									<a class="menu-link" href="<?= base_url('Customer/Audit_dashboard/Normal_Audit') ?>">
										<span class="menu-icon">
											<span class="fas fa-chart-pie"></span>
										</span>
										<span class="menu-title">Normal Audit Dashboard</span></span>
									</a>
								</div> -->
								<div class="menu-item">
									<a class="menu-link" href="<?= base_url('Customer/Audit_dashboard/OE_Audit') ?>">
										<span class="menu-icon">
											<span class="fas fa-chart-bar"></span>
										</span>
										<span class="menu-title">OE Audit Dashboard</span></span>
									</a>
								</div>
								<!-- <div class="menu-item">
									<a class="menu-link" href="<?= base_url('Customer/Audit_Dashboard_HSE/HSE_Audit') ?>">
										<span class="menu-icon">
											<span class="fas fa-shield-alt"></span>
										</span>
										<span class="menu-title">HSE Audit Dashboard</span></span>
									</a>
								</div> -->
								<div class="menu-item">
									<a class="menu-link" href="<?= base_url('Customer/Gemba_Dashboard') ?>">
										<span class="menu-icon">
											<span class="fas fa-chart-line"></span>
										</span>
										<span class="menu-title">Gemba HSE Audit Dashboard</span>
									</a>
								</div>

								<?php if (($_SESSION['admin_flag'] ?? 0) == 1 || $_SESSION['role'] == "admin") { ?>
									<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
										<span class="menu-link">
											<span class="menu-icon">
												<!--begin::Svg Icon | path: icons/duotune/abstract/abs042.svg-->
												<span class="fas fa-database"></span>
												<!--end::Svg Icon-->
											</span>
											<span class="menu-title">Master</span>
											<span class="menu-arrow"></span>
										</span>
										<div class="menu-sub menu-sub-accordion menu-active-bg">
										
											<div class="menu-item">
												<div class="menu-link">
													<span class="menu-title"> - User Master</span>
												</div>
											</div>

											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/User') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">User Master</span>
												</a>
											</div>
											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/Location_master') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">Location Master</span>
												</a>
											</div>
											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/Cluster_master') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">Cluster Master</span>
												</a>
											</div>
											
											
											<hr style="color: white;opacity: 100%;">

											<div class="menu-item">
												<div class="menu-link">
													<span class="menu-title"> - Audit Master</span>
												</div>
											</div>

											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/Audit_template') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">Audit Template</span>
												</a>
											</div>

											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/Audit_question_master') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">Audit Question</span>
												</a>
											</div>

											<!-- <div class="menu-item">
													<a class="menu-link" href="<?= base_url('Masters/Sixs_category') ?>">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
														<span class="menu-title">6s Category</span>
													</a>
												</div> -->

											<hr style="color: white;opacity: 100%;">

											<div class="menu-item">
												<div class="menu-link">
													<span class="menu-title"> - OE Master</span>
												</div>
											</div>

											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/Region') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">Region Master</span>
												</a>
											</div>
											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/Client') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">Client Master</span>
												</a>
											</div>
											

											<!--	<div class="menu-item">-->
											<!--	<a class="menu-link" href="<?= base_url('Masters/Department_master') ?>">-->
											<!--		<span class="menu-bullet">-->
											<!--			<span class="bullet bullet-dot"></span>-->
											<!--		</span>-->
											<!--		<span class="menu-title">Department Master</span>-->
											<!--	</a>-->
											<!--</div>-->
											<hr style="color: white;opacity: 100%;">

											<div class="menu-item">
												<div class="menu-link">
													<span class="menu-title"> - HSE Master</span>
												</div>
											</div>

											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/Hse_site_category') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">Site Category Master</span>
												</a>
											</div>

											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/Hse_sub_category') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">Sub Category Master</span>
												</a>
											</div>
											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/Hse_region') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">Region Master</span>
												</a>
											</div>
											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/Hse_client') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">Client Master</span>
												</a>
											</div>



											<!-- <hr style="color: white;opacity: 100%;">

											<div class="menu-item">
												<div class="menu-link">
													<span class="menu-title"> - LMRA Master</span>
												</div>
											</div>

											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/Lmra_controle') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">LMRA Control</span>
												</a>
											</div>

											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/Lmra_category') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">LMRA Category Control</span>
												</a>
											</div>

											<hr style="color: white;opacity: 100%;">

											<div class="menu-item">
												<div class="menu-link">
													<span class="menu-title"> - Near Miss Master</span>
												</div>
											</div>

											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/Incident') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">Injury</span>
												</a>
											</div>
											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/Body_part') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">Body Part</span>
												</a>
											</div> -->
										
										</div>
									</div>
								<?php } ?>

								<?php
									$role = strtolower($_SESSION['role'] ?? '');
									$allowedRoles = ['super admin', 'super_admin', 'admin', 'auditor', 'account manager', 'account_manager', 'cluster manager', 'cluster_manager'];
									if (in_array($role, $allowedRoles) || ($_SESSION['admin_flag'] ?? 0) == 1) {
								?>
								<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
									<span class="menu-link">
										<span class="menu-icon">
											<span class="fas fa-industry"></span>
										</span>
										<span class="menu-title">Gemba Master</span>
										<span class="menu-arrow"></span>
									</span>
									<div class="menu-sub menu-sub-accordion menu-active-bg">
										<div class="menu-item">
											<a class="menu-link" href="<?= base_url('gemba-audit') ?>">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Gemba Audit</span>
											</a>
										</div>
										<div class="menu-item">
											<a class="menu-link" href="<?= base_url('gemba-sites') ?>">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Gemba Sites</span>
											</a>
										</div>
									</div>
								</div>
								<?php } ?>

								<!-- <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
									<span class="menu-link">
										<span class="menu-icon">
											<span class="fas fa-random"></span>
										</span>
										<span class="menu-title">Random Audit</span>
										<span class="menu-arrow"></span>
									</span>
									<div class="menu-sub menu-sub-accordion menu-active-bg">

										<div class="menu-item">
											<div class="menu-link">
												<span class="menu-title"> - Safety Audit</span>
											</div>
										</div>
										<div class="menu-item">
											<a class="menu-link" href="<?= base_url('Reports/Lmra_reports') ?>">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">LMRA Reports</span>
											</a>
										</div>

										<div class="menu-item">
											<a class="menu-link" href="<?= base_url('Reports/Near_miss_reports') ?>">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Near Miss Report</span>
											</a>
										</div>

										<hr style="color: white;opacity: 100%;">
										
									<?php if (($_SESSION['admin_flag'] ?? 0) != 1) { ?>
										<div class="menu-item">
											<div class="menu-link">
												<span class="menu-title"> - Other Audit</span>
											</div>
										</div>


										
										<div class="menu-item">
											<a class="menu-link" href="<?= base_url('Masters/Sixs_audit') ?>">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">6s Audit Report</span>
											</a>
										</div>
										<?php } ?>


									</div>
								</div> -->



								<?php //if (strtolower($_SESSION['role'] ?? '') !== 'higher authority') { ?>
								<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
									<span class="menu-link">
										<span class="menu-icon">
											<!--begin::Svg Icon | path: icons/duotune/abstract/abs042.svg-->
											<span class="fas fa-sitemap"></span>
											<!--end::Svg Icon-->
										</span>
										<span class="menu-title">Structure Audit</span>
										<span class="menu-arrow"></span>
									</span>
									<div class="menu-sub menu-sub-accordion menu-active-bg">

										<div class="menu-item">
											<a class="menu-link"
												href="<?= base_url('Masters/Audit_template/index/view') ?>">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Audit Template</span>
											</a>
										</div>
										<!-- <div class="menu-item">
											<a class="menu-link"
												href="<?= base_url('Masters/Audit_final_structure/normal_audit_structure') ?>">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Perform Normal Audit</span>
											</a>
										</div> -->
										<div class="menu-item">
											<a class="menu-link" href="<?= base_url('Masters/Hse_audit') ?>">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Perform HSE Audit</span>
											</a>
										</div>



										<div class="menu-item">
											<a class="menu-link" href="<?= base_url('Masters/Audit_final_structure') ?>">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Perform OE Audit</span>
											</a>
										</div>

										<div class="menu-item">
											<a class="menu-link" href="<?= base_url('Masters/Reaudit') ?>">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">All Old Audits</span>
											</a>
										</div>


									</div>
								</div>
								<?php //} ?>
								<!--<div class="menu-item">-->
								<!--	<a class="menu-link" href="<?= base_url('Masters/Audit_template/index/view') ?>">-->
								<!--		<span class="menu-bullet">-->
								<!--			<span class="bullet bullet-dot"></span>-->
								<!--		</span>-->
								<!--		<span class="menu-title">Audit Template</span>-->
								<!--	</a>-->
								<!--</div>-->
								<!--   <div class="menu-item">-->
								<!--	<a class="menu-link" href="<?= base_url('Masters/Audit_final_structure/normal_audit_structure') ?>">-->
								<!--		<span class="menu-bullet">-->
								<!--			<span class="bullet bullet-dot"></span>-->
								<!--		</span>-->
								<!--		<span class="menu-title">Perform Normal Audit</span>-->
								<!--	</a>-->
								<!--</div>-->
								<!--<div class="menu-item">-->
								<!--	<a class="menu-link" href="<?= base_url('Masters/Reaudit/index/HSE') ?>">-->
								<!--		<span class="menu-bullet">-->
								<!--			<span class="bullet bullet-dot"></span>-->
								<!--		</span>-->
								<!--		<span class="menu-title">Perform HSE Audit</span>-->
								<!--	</a>-->
								<!--</div>-->

								<!--<div class="menu-item">-->
								<!--	<a class="menu-link" href="<?= base_url('Masters/Reaudit/index/OE') ?>">-->
								<!--		<span class="menu-bullet">-->
								<!--			<span class="bullet bullet-dot"></span>-->
								<!--		</span>-->
								<!--		<span class="menu-title">Perform OE Audit</span>-->
								<!--	</a>-->
								<!--</div>-->

								<!--<div class="menu-item">-->
								<!--	<a class="menu-link" href="<?= base_url('Masters/Audit_final_structure') ?>">-->
								<!--		<span class="menu-bullet">-->
								<!--			<span class="bullet bullet-dot"></span>-->
								<!--		</span>-->
								<!--		<span class="menu-title">OE Reaudit</span>-->
								<!--	</a>-->
								<!--</div>-->
								<!--<div class="menu-item">-->
								<!--	<a class="menu-link" href="<?= base_url('Masters/Hse_audit') ?>">-->
								<!--		<span class="menu-bullet">-->
								<!--			<span class="bullet bullet-dot"></span>-->
								<!--		</span>-->
								<!--		<span class="menu-title">HSE Reaudit</span>-->
								<!--	</a>-->
								<!--</div>-->
								<!--<div class="menu-item">-->
								<!--	<a class="menu-link" href="<?= base_url('Masters/Audit_final_structure/normal_audit_structure') ?>">-->
								<!--		<span class="menu-bullet">-->
								<!--			<span class="bullet bullet-dot"></span>-->
								<!--		</span>-->
								<!--		<span class="menu-title">Normal Reaudit</span>-->
								<!--	</a>-->
								<!--</div>-->

								<!--</div>-->
								<!--</div>-->
								<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
									<span class="menu-link">
										<span class="menu-icon">
											<span class="fas fa-exclamation-triangle"></span>
										</span>
										<span class="menu-title">NC Tracker</span>
										<span class="menu-arrow"></span>
									</span>
									<div class="menu-sub menu-sub-accordion menu-active-bg">
										
											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/Oe_nc_tracker') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">OE NC Tracker</span>
												</a>
											</div>
											
											<!-- <div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/Normal_nc_tracker') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">Normal NC Tracker</span>
												</a>
											</div> -->

											<!-- <div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/Hse_nc_tracker') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">HSE NC Tracker</span>
												</a>
											</div> -->
											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Masters/GembaNcTracker') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">Gemba HSE NC Tracker</span>
												</a>
											</div>
									</div>
								</div>

								<!-- Reports Section -->
								<?php if (in_array($_SESSION['role'], ['admin', 'super_admin', 'Super admin', 'Auditor', 'Higher authority', 'highter_authority'])): ?>
								<div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= ($_SESSION['active_btn'] ?? '') == 'Reports' ? 'here show' : '' ?>">
									<span class="menu-link">
										<span class="menu-icon">
											<span class="fas fa-chart-bar"></span>
										</span>
										<span class="menu-title">Reports</span>
										<span class="menu-arrow"></span>
									</span>
									<div class="menu-sub menu-sub-accordion menu-active-bg">
										<div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= ($_SESSION['active_tag'] ?? '') == 'RtWeeklyMl' ? 'here show' : '' ?>">
											<span class="menu-link">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Weekly Reports</span>
												<span class="menu-arrow"></span>
											</span>
											<div class="menu-sub menu-sub-accordion menu-active-bg">
												<div class="menu-item">
													<a class="menu-link <?= ($_SESSION['active_tag'] ?? '') == 'RtWeeklyMl' ? 'active' : '' ?>" href="<?= base_url('Reports/RtWeeklyMl') ?>">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
														<span class="menu-title">RT-WeeklyML</span>
													</a>
												</div>
											</div>
										</div>
									</div>
								</div>
								<?php endif; ?>


								<!--<div data-kt-menu-trigger="click" class="menu-item menu-accordion">-->
								<!--	<span class="menu-link">-->
								<!--		<span class="menu-icon">-->
								<!--begin::Svg Icon | path: icons/duotune/abstract/abs042.svg-->
								<!--			<span class="fa fa-tablet"></span>-->
								<!--end::Svg Icon-->
								<!--		</span>-->
								<!--		<span class="menu-title">HSE Nc Tracker</span>-->
								<!--		<span class="menu-arrow"></span>-->
								<!--	</span>-->
								<!--	<div class="menu-sub menu-sub-accordion menu-active-bg">-->
								<!--		<div class="menu-item">-->
								<!--			<a class="menu-link" href="<?= base_url('Masters/Client_nc_tracker') ?>">-->
								<!--				<span class="menu-bullet">-->
								<!--					<span class="bullet bullet-dot"></span>-->
								<!--				</span>-->
								<!--				<span class="menu-title">Client Nc Tracker</span></span>-->
								<!--			</a>-->
								<!--		</div>-->
								<!--		<div class="menu-item">-->
								<!--			<a class="menu-link" href="<?= base_url('Masters/Inplant_nc_tracker') ?>">-->
								<!--				<span class="menu-bullet">-->
								<!--					<span class="bullet bullet-dot"></span>-->
								<!--				</span>-->
								<!--				<span class="menu-title">Inplant Nc Tracker</span></span>-->
								<!--			</a>-->
								<!--		</div>-->
								<!--		<div class="menu-item">-->
								<!--			<a class="menu-link" href="<?= base_url('Masters/Fm_nc_tracker') ?>">-->
								<!--				<span class="menu-bullet">-->
								<!--					<span class="bullet bullet-dot"></span>-->
								<!--				</span>-->
								<!--				<span class="menu-title">Fm Nc Tracker</span></span>-->
								<!--			</a>-->
								<!--		</div>-->
								<!--	</div>-->
								<!--</div>-->

								<?php 
								$sd_role = strtolower($_SESSION['role'] ?? '');
								if (true): ?>
								<div data-kt-menu-trigger="click" class="menu-item menu-accordion <?= ($_SESSION['active_btn'] ?? '') == 'Software Documentation' ? 'here show' : '' ?>">
									<span class="menu-link">
										<span class="menu-icon">
											<span class="fas fa-book-open"></span>
										</span>
										<span class="menu-title">Software Documentation</span>
										<span class="menu-arrow"></span>
									</span>
									<div class="menu-sub menu-sub-accordion menu-active-bg">
										<div class="menu-item">
											<a class="menu-link <?= ($_SESSION['active_tag'] ?? '') == 'Definitions' ? 'active' : '' ?>" href="<?= base_url('software-definitions') ?>">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title">Definitions</span>
											</a>
										</div>
										<?php 
											$sd_cats = ['SOPs', 'Protocols', 'OE Documents', 'Policies', 'Manuals', 'HSE Grid Checklist'];
											foreach($sd_cats as $scat): 
										?>
										<div class="menu-item">
											<a class="menu-link <?= ($_SESSION['active_tag'] ?? '') == $scat ? 'active' : '' ?>" href="<?= base_url('software-documentation?category='.urlencode($scat)) ?>">
												<span class="menu-bullet">
													<span class="bullet bullet-dot"></span>
												</span>
												<span class="menu-title"><?= $scat ?></span>
											</a>
										</div>
										<?php endforeach; ?>
									</div>
								</div>
								<?php endif; ?>
								<script>
									// Fix for Metronic's auto-highlighting matching the first matching URL incorrectly
									document.addEventListener("DOMContentLoaded", function() {
										setTimeout(function() {
											if (window.location.pathname.indexOf('software-documentation') !== -1) {
												var urlParams = new URLSearchParams(window.location.search);
												var category = urlParams.get('category');
												document.querySelectorAll('a.menu-link[href*="software-documentation?category="]').forEach(function(link) {
													try {
														var linkCat = new URL(link.href).searchParams.get('category');
														if (linkCat !== category) {
															link.classList.remove('active');
														}
													} catch(e) {}
												});
											}
										}, 100);
									});
								</script>
								
								<?php 
								helper('designation_acl');
								$role = strtolower(trim($_SESSION['role'] ?? ''));
								$isAllowedOnlineUsers = (($_SESSION['admin_flag'] ?? 0) == 1) 
									|| isSuperAdmin() 
									|| isAdmin() 
									|| isHigherAuthority() 
									|| isClusterManager()
									|| in_array($role, ['super admin', 'super_admin', 'admin', 'higher authority', 'higher_authority', 'highter_authority', 'cluster manager', 'cluster_manager']);
								if ($isAllowedOnlineUsers) { 
								?>
								<div class="menu-item">
									<a class="menu-link" href="<?= base_url('Admin/OnlineUsers') ?>">
										<span class="menu-icon">
											<span class="fas fa-users"></span>
										</span>
										<span class="menu-title">Online Users</span>
									</a>
								</div>
								<?php } ?>
								
								<?php if (($_SESSION['admin_flag'] ?? 0) == 1 || strtolower($_SESSION['role'] ?? '') == "super admin" || strtolower($_SESSION['role'] ?? '') == "admin") { ?>
									<div data-kt-menu-trigger="click" class="menu-item menu-accordion">
										<span class="menu-link">
											<span class="menu-icon">
												<span class="fas fa-cog"></span>
											</span>
											<span class="menu-title">Settings</span>
											<span class="menu-arrow"></span>
										</span>
										<div class="menu-sub menu-sub-accordion menu-active-bg">
											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('admin/settings/email-settings') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">Email Settings</span>
												</a>
											</div>
											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('admin/settings/cron-settings') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">Cron Settings</span>
												</a>
											</div>
											<div class="menu-item">
												<a class="menu-link" href="<?= base_url('Reports/Mailing_report') ?>">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
													<span class="menu-title">Mailing Reports</span></span>
												</a>
											</div>
										</div>
									</div>
								<?php } ?>



								<div class="menu-item">
									<div class="menu-content">
										<div class="separator mx-1 my-4"></div>
									</div>
								</div>


							</div>
							<!--end::Menu-->
						</div>
						<!--end::Aside Menu-->
					</div>
					<!--end::Aside menu-->
					<!--begin::Footer-->
					<div class="aside-footer flex-column-auto pt-5 pb-7 px-5" id="kt_aside_footer">
						<!--<a href="https://unitglo.com/" class="btn btn-custom btn-primary w-100" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss-="click" title="Unitglo Solutions Private Limited">
									<span class="btn-label">Unitglo Solutions</span>
									<span class="svg-icon btn-icon svg-icon-2">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
											<path opacity="0.3" d="M19 22H5C4.4 22 4 21.6 4 21V3C4 2.4 4.4 2 5 2H14L20 8V21C20 21.6 19.6 22 19 22ZM15 17C15 16.4 14.6 16 14 16H8C7.4 16 7 16.4 7 17C7 17.6 7.4 18 8 18H14C14.6 18 15 17.6 15 17ZM17 12C17 11.4 16.6 11 16 11H8C7.4 11 7 11.4 7 12C7 12.6 7.4 13 8 13H16C16.6 13 17 12.6 17 12ZM17 7C17 6.4 16.6 6 16 6H8C7.4 6 7 6.4 7 7C7 7.6 7.4 8 8 8H16C16.6 8 17 7.6 17 7Z" fill="black" />
											<path d="M15 8H20L14 2V7C14 7.6 14.4 8 15 8Z" fill="black" />
										</svg>
									</span>
								</a>-->
					</div>
					<!--end::Footer-->
				</div>
				<!--end::Aside-->
				<!--begin::Wrapper-->
				<div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
					<!--begin::Header-->
					<!--<div id="kt_header" style="background-color: #efede6ab; border-bottom: 1px solid black;" class="header align-items-stretch">-->
					<div id="kt_header" style="border-bottom: 1px solid black;" class="header align-items-stretch">
						<!--begin::Container-->
						<div class="container-fluid d-flex align-items-stretch justify-content-between">
							<!--begin::Aside mobile toggle-->
							<div class="d-flex align-items-center d-lg-none ms-n3 me-1" title="Show aside menu">
								<div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px"
									id="kt_aside_mobile_toggle">
									<!--begin::Svg Icon | path: icons/duotune/abstract/abs015.svg-->
									<span class="svg-icon svg-icon-2x mt-1">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
											fill="none">
											<path
												d="M21 7H3C2.4 7 2 6.6 2 6V4C2 3.4 2.4 3 3 3H21C21.6 3 22 3.4 22 4V6C22 6.6 21.6 7 21 7Z"
												fill="black" />
											<path opacity="0.3"
												d="M21 14H3C2.4 14 2 13.6 2 13V11C2 10.4 2.4 10 3 10H21C21.6 10 22 10.4 22 11V13C22 13.6 21.6 14 21 14ZM22 20V18C22 17.4 21.6 17 21 17H3C2.4 17 2 17.4 2 18V20C2 20.6 2.4 21 3 21H21C21.6 21 22 20.6 22 20Z"
												fill="black" />
										</svg>
									</span>
									<!--end::Svg Icon-->
								</div>
							</div>
							<!--end::Aside mobile toggle-->
							<!--begin::Mobile logo-->
							<div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
								<a href="#" class="d-lg-none">
									<img alt="Logo" src="<?= base_url() ?>/assets/media/app-logo-3.jpeg" class="h-30px" />
								</a>
							</div>
							<!--end::Mobile logo-->
							<!--begin::Wrapper-->
							<div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1">
								<!--begin::Navbar-->
								<div class="d-flex align-items-stretch" id="kt_header_nav">
									<!--begin::Menu wrapper-->
									<div class="header-menu align-items-stretch" data-kt-drawer="true"
										data-kt-drawer-name="header-menu"
										data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
										data-kt-drawer-width="{default:'200px', '300px': '250px'}"
										data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_header_menu_mobile_toggle"
										data-kt-swapper="true" data-kt-swapper-mode="prepend"
										data-kt-swapper-parent="{default: '#kt_body', lg: '#kt_header_nav'}">
										<!--begin::Menu-->

										<div class="menu menu-lg-rounded menu-column menu-lg-row menu-state-bg menu-title-gray-700 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-400 fw-bold my-5 my-lg-0 align-items-stretch"
											id="#kt_header_menu" data-kt-menu="true">



										</div>
										<!--end::Menu-->
									</div>
									<!--end::Menu wrapper-->
								</div>
								<!--end::Navbar-->
								<!--begin::Topbar-->
								<div class="d-flex align-items-stretch flex-shrink-0">

									<!--begin::Sidebar toggle arrow (desktop)-->
									<div class="d-none d-lg-flex align-items-center ms-3 me-2">
										<button id="sidebar_toggle_arrow" class="btn btn-sm btn-light">
											<i class="fa fa-angle-left"></i>
										</button>
									</div>
									<!--end::Sidebar toggle arrow (desktop)-->

									<!--begin::Toolbar wrapper-->
									<div class="d-flex align-items-stretch flex-shrink-0">
										<!--begin::Search-->
										<div class="d-flex align-items-stretch ms-1 ms-lg-3">
											<!--begin::Search-->
											<!-- (search code commented out) -->
										</div>
										<!--end::Search-->

										<!--begin::Client Selector-->
										<?php 
										$sessionAcl = $_SESSION['user_acl'] ?? [];
										$isRestricted = $sessionAcl['is_restricted'] ?? false;
										$oeSites = $sessionAcl['oe_site_names'] ?? [];
										$hseSites = $sessionAcl['hse_site_names'] ?? [];
										$allSites = array_values(array_unique(array_merge($oeSites, $hseSites)));
										$activeClient = $_SESSION['selected_active_client'] ?? 'ALL';
										
										if ($isRestricted && count($allSites) > 0) { 
										?>
										<div class="d-flex align-items-center ms-1 ms-lg-3">
											<select class="form-select form-select-sm form-select-solid" id="global_client_selector" onchange="changeGlobalClient(this.value)" style="min-width: 150px;">
												<option value="ALL" <?= $activeClient === 'ALL' ? 'selected' : '' ?>>All Clients</option>
												<?php foreach ($allSites as $site): ?>
													<option value="<?= esc($site) ?>" <?= $activeClient === $site ? 'selected' : '' ?>><?= esc($site) ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<script>
										function changeGlobalClient(client) {
											$.ajax({
												url: '<?= base_url("BaseController/set_active_client") ?>',
												type: 'POST',
												data: { client: client },
												success: function(res) {
													window.location.reload();
												}
											});
										}
										</script>
										<?php } ?>
										<!--end::Client Selector-->
										<!--begin::User-->
										<div class="d-flex align-items-center ms-1 ms-lg-3" id="kt_header_user_menu_toggle">
											<!--begin::Menu wrapper-->
											<div class="cursor-pointer symbol symbol-30px symbol-md-40px"
												data-kt-menu-trigger="click" data-kt-menu-attach="parent"
												data-kt-menu-placement="bottom-end">

												<!--<img src="<?= base_url() ?>/assets/media/Mobiyoung-Logo.jpeg" style="width:100% !important;" alt="DG PLAY" />-->
												<div class="fa fa-user" style="font-size: 25px;"></div>
											</div>
											<!--begin::Menu-->
											<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-primary fw-bold py-4 fs-6 w-325px"
												data-kt-menu="true">
												<!--begin::Menu item-->
												<div class="menu-item px-3">
													<div class="menu-content d-flex align-items-center px-3">
														<!--begin::Avatar-->
														<div class="symbol symbol-50px me-5">
															<!--	<img alt="Logo" src="<?= base_url() ?>/assets/media/Mobiyoung-Logo.jpeg" />-->
															<div class="fa fa-user"></div>
														</div>
														<!--end::Avatar-->
														<!--begin::Username-->

														<div class="d-flex flex-column">
															<div class="fw-bolder d-flex align-items-center flex-wrap fs-5"><?php
															// changes on 8/10/25 by Darsh - show logged-in user's name in account menu
															if (isset($_SESSION['user_name']) && $_SESSION['user_name'] != "") {
																echo $_SESSION['user_name'];
															} else if (isset($_SESSION['details']) && $_SESSION['details'] != "") {
																if (isset($_SESSION['details']['first_name'])) {
																	echo $_SESSION['details']['first_name'] . " " . (isset($_SESSION['details']['last_name']) ? $_SESSION['details']['last_name'] : '');
																	if (isset($_SESSION['details']['company_name']))
																		echo " ( " . $_SESSION['details']['company_name'] . " )";
																} else if (isset($_SESSION['details']['customer_name'])) {
																	echo $_SESSION['details']['customer_name'];
																} else {
																	echo "User";
																}
															} else {
																echo "User";
															}
															?>
																<span
																	class="badge badge-light-success fw-bolder fs-8 px-2 py-1 ms-2 mt-1">
																	<?php
																	// changes on 8/10/25 by Darsh - show role label instead of generic 'User'
																	if (isset($_SESSION['role']) && $_SESSION['role'] != "") {
																		$role = $_SESSION['role'];
																		switch ($role) {
																			case 'super_admin':
																				echo 'SUPER ADMIN';
																				break;
																			case 'Higher authority':
																				echo 'HIGHER AUTHORITY';
																				break;
																			case 'Cluster manager':
																				echo 'CLUSTER MANAGER';
																				break;
																			// case 'admin': echo 'ADMIN'; break;
																			case 'Account Manager':
																				echo 'ACCOUNT MANAGER';
																				break;
																			case 'Engineer':
																				echo 'ACCOUNT MANAGER';
																				break; // Backward compatibility - display as ACCOUNT MANAGER
																			// case 'customer': echo 'CUSTOMER'; break;
																			// case 'student': echo 'STUDENT'; break;
																			default:
																				echo ucwords($role);
																				break;
																		}
																	}
																	?>
																</span>
															</div>
														</div>
														<!--end::Username-->
													</div>
												</div>
												<!--end::Menu item-->
												<!--begin::Menu separator-->
												<div class="separator my-2"></div>
												<!--end::Menu separator-->
												<!--begin::Menu item-->
												<?php if ($_SESSION['role'] != 'admin' && ($_SESSION['admin_flag'] ?? 0) != 1) { ?>
													<div class="menu-item px-5">
														<a href="<?= base_url("/profile") ?>" class="menu-link px-5">Change
															Password</a>
													</div>
													<div class="separator my-2"></div>
												<?php } ?>
												<!--end::Menu item-->

												<!--begin::Menu item-->
												<div class="menu-item px-5">
													<a href="<?= base_url("Login/log_out") ?>" class="menu-link px-5">Sign
														Out</a>
												</div>
												<!--end::Menu item-->
												<!--begin::Menu separator-->
												<div class="separator my-2"></div>
												<!--end::Menu separator-->

											</div>
											<!--end::Menu-->
											<!--end::Menu wrapper-->
										</div>
										<!--end::User -->
										<!-- begin::Heaeder menu toggle-->
										<div class="d-flex align-items-center d-lg-none ms-2 me-n3"
											title="Show header menu">
											<div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px"
												id="kt_header_menu_mobile_toggle">
												<!-- (icon commented out) -->
											</div>
										</div>
										<!--end::Heaeder menu toggle -->
									</div>
									<!--end::Toolbar wrapper-->
								</div>
								<!--end::Topbar-->
							</div>
							<!--end::Wrapper-->
						</div>
						<!--end::Container-->
					</div>
					<!--end::Header-->
					<!--begin::Content-->
					<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
						<!--begin::Toolbar-->
						<div class="toolbar" id="kt_toolbar">
							<!--begin::Container-->
							<div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
								<!--begin::Page title-->
								<div data-kt-swapper="true" data-kt-swapper-mode="prepend"
									data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
									class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
									<!--begin::Title-->
									<?php
										$dashboardUrl = base_url('Customer/Audit_dashboard/OE_Audit'); // Default to OE Dashboard
										if (isset($audit_template_type)) {
											if (strcasecmp($audit_template_type, 'Normal') === 0) {
												$dashboardUrl = base_url('Customer/Audit_dashboard/Normal_Audit');
											} elseif (strcasecmp($audit_template_type, 'HSE') === 0) {
												$dashboardUrl = base_url('Customer/Gemba_Dashboard');
											}
										} else {
											// Fallback for pages where template type isn't passed (e.g. HSE NC Tracker, Normal Audit)
											$currentUri = uri_string();
											if (stripos($currentUri, 'normal') !== false) {
												$dashboardUrl = base_url('Customer/Audit_dashboard/Normal_Audit');
											} elseif (stripos($currentUri, 'hse') !== false) {
												$dashboardUrl = base_url('Customer/Gemba_Dashboard');
											} elseif (stripos($currentUri, 'gemba') !== false) {
												$dashboardUrl = base_url('Customer/Gemba_Dashboard');
											}
										}
									?>
									<h1 class="d-flex align-items-center text-dark fw-bolder fs-3 my-1"> <a
											href="<?= $dashboardUrl ?>"> Dashboard </a></h1>
									<!--end::Title-->
									<!--begin::Separator-->
									<span class="h-20px border-gray-200 border-start mx-4"></span>
									<!--end::Separator-->
									<!--begin::Breadcrumb-->
									<ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
										<?php
										$this->renderSection("breadcrumb_title_li");
										?>
									</ul>
									<!--end::Breadcrumb-->
								</div>
								<!--end::Page title-->
								<!--begin::Actions-->

								<!--end::Actions-->
							</div>
							<!--end::Container-->
						</div>
						<!--end::Toolbar-->
						<!--begin::Post-->
						<div class="post d-flex flex-column-fluid" id="kt_post">
							<!--begin::Container-->
							<div id="kt_content_container" class="container-xxl">
								<?php
								$this->renderSection("main_body");
								?>

							</div>
						</div>
					</div>
					<!--end::Content-->
					<!--begin::Footer-->
					<div class="footer py-4 d-flex flex-lg-column" id="kt_footer">
						<div class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-between">
							<div class="text-dark order-2 order-md-1">
								<span class="text-muted fw-bold me-1">&copy; <?= date('Y'); ?></span>
								<a href="#" class="text-gray-800 text-hover-primary">Unitglo Solutions</a>.
								All Rights Reserved.
							</div>

							<div class="menu menu-gray-600 menu-hover-primary fw-bold order-1">
								Version 1.0
							</div>
						</div>
					</div>
					<!--end::Footer-->
				</div>
				<!--end::Wrapper-->
			</div>
			<!--end::Page-->
		</div>
		<!--end::Root-->


		<!--end::Drawers-->
		<!--begin::Modals-->
		<?php $this->renderSection("modals_section"); ?>
		<!--end::Modals-->

		<!-- Global Dynamic Modal for View Attendance etc -->
		<div class="modal fade" id="globalAjaxModal" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-xl">
				<div class="modal-content" id="globalAjaxModalContent">
					<!-- Content gets loaded here via AJAX -->
				</div>
			</div>
		</div>
		<!--begin::Scrolltop-->
		<div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
			<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
			<span class="svg-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
					<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)"
						fill="black" />
					<path
						d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z"
						fill="black" />
				</svg>
			</span>
			<!--end::Svg Icon-->
		</div>
		<!--end::Scrolltop-->
		<!--end::Main-->
		<script>var hostUrl = "<?= base_url() ?>/assets/";</script>
		<!--begin::Javascript-->
		<!--begin::Global Javascript Bundle(used by all pages)-->
		<script src="<?= base_url() ?>/assets/plugins/global/plugins.bundle.js"></script>
		<script src="<?= base_url() ?>/assets/js/scripts.bundle.js"></script>
		<!--end::Global Javascript Bundle-->
		<!--begin::Page Vendors Javascript(used by this page)-->
		<script src="<?= base_url() ?>/assets/plugins/custom/fullcalendar/fullcalendar.bundle.js"></script>
		<!--end::Page Vendors Javascript-->
		<!--begin::Page Custom Javascript(used by this page)-->
		<script src="<?= base_url() ?>/assets/js/custom/widgets.js"></script>
		<script src="<?= base_url() ?>/assets/js/custom/apps/chat/chat.js"></script>
		<script src="<?= base_url() ?>/assets/js/custom/modals/create-app.js"></script>
		<script src="<?= base_url() ?>/assets/js/custom/modals/upgrade-plan.js"></script>
		<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
		<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
		<!--end::Page Custom Javascript-->
		<script>
			toastr.options = {
				"closeButton": true,
				"debug": false,
				"newestOnTop": false,
				"progressBar": true,
				"positionClass": "toastr-top-right",
				"preventDuplicates": false,
				"onclick": null,
				"showDuration": "300",
				"hideDuration": "1000",
				"timeOut": "5000",
				"extendedTimeOut": "1000",
				"showEasing": "swing",
				"hideEasing": "linear",
				"showMethod": "fadeIn",
				"hideMethod": "fadeOut"
			};

			function ajax_call_img(url, data_to_send, button, success_function) {
				$.ajax({
					url: url,
					processData: false, // This ensures that jQuery doesn't process the data
					contentType: false, // This ensures that jQuery doesn't set a content type
					async: true,
					data: data_to_send,

					type: 'post',
					statusCode: {
						500: function () {
							toastr.error("<b>Server Error Contact To System Admin.</b>");
						}
					},
					beforeSend: function () {
						//if(iss)
						button.attr('data-kt-indicator', 'on');
						button.attr('disabled', true);
					},
					success: success_function,
					complete: function (data) {
						button.attr('data-kt-indicator', 'off');
						button.attr('disabled', false);
					}
				});
			}



			function ajax_call_html(url, data_to_send, button, success_function) {
				$.ajax({
					url: url,
					dataType: 'html',
					async: true,
					data: data_to_send,

					type: 'post',
					statusCode: {
						500: function () {
							toastr.error("<b>Server Error Contact To System Admin.</b>");
						}
					},
					beforeSend: function () {
						//if(iss)
						button.attr('data-kt-indicator', 'on');
						button.attr('disabled', true);
					},
					success: success_function,
					complete: function (data) {
						button.attr('data-kt-indicator', 'off');
						button.attr('disabled', false);
					}
				});
			}
			function ajax_call(url, data_to_send, button, success_function) {
				$.ajax({
					url: url,
					data: data_to_send,

					type: 'post',
					statusCode: {
						500: function () {
							toastr.error("<b>Server Error Contact To System Admin.</b>");
						}
					},
					beforeSend: function () {
						//if(iss)
						button.attr('data-kt-indicator', 'on');
						button.attr('disabled', true);
					},
					success: success_function,
					complete: function (data) {
						button.attr('data-kt-indicator', 'off');
						button.attr('disabled', false);
					}
				});
			}
			function url_call_ajax(url, button, callback) {
				$.ajax({
					url: url,
					async: true,
					// 	data : data_to_send,
					type: 'post',
					statusCode: {
						500: function () {
							toastr.error("<b>Server Error Contact To System Admin.</b>");
						}
					},
					beforeSend: function () {
						//if(iss)
						if (button) {
							button.attr('data-kt-indicator', 'on');
							button.attr('disabled', true);
						}
					},
					success: function (responce) {
						try {
                            if (typeof responce === 'string') {
							    responce = JSON.parse(responce);
                            }
							if (responce.status == 1) {
								toastr.success(responce.message);
								//success_function;
								//                                    reload_data_table();
							} else {
								toastr.warning(responce.message);
							}
							if (responce.url) {
								setTimeout(window.location.href = responce.url, 2000);
							}
						} catch (error) {
							toastr.error(error);
						}
						if (callback)
							callback;
						else
							reload_data_table();

					},
					complete: function (data) {
						button.attr('data-kt-indicator', 'off');
						button.attr('disabled', false);

					}
				});
			}
		</script>
		<script>
			// changes on 1/10/25 by darsh: keep parent menu expanded and highlight active link based on current URL
			(function () {
				try {
					var current = window.location.pathname.replace(/\/+$/, '');
					var links = Array.prototype.slice.call(document.querySelectorAll('#kt_aside a.menu-link[href]'));

					// changes on 31/10/25 by darsh: explicit route mapping to avoid OE catching Normal routes
					function pickExplicit() {
						// Normal audit perform/reaudit pages
						if (/\/Masters\/Audit_final_structure\/normal_/i.test(current) || /\/Masters\/Audit_final_structure\/normal_audit_structure/i.test(current)) {
							var normalLink = document.querySelector('#kt_aside a.menu-link[href*="Masters/Audit_final_structure/normal_audit_structure"]');
							if (normalLink) return normalLink;
						}
						return null;
					}

					var best = pickExplicit();
					var bestLen = best ? (new URL(best.href, window.location.origin)).pathname.length : -1;

					// Fallback: choose the most specific (longest) matching path
					links.forEach(function (a) {
						var href = a.getAttribute('href');
						if (!href) return;
						var url = document.createElement('a');
						url.href = href;
						var path = url.pathname.replace(/\/+$/, '');
						if (!path) return;
						if (current === path && path.length > bestLen) { best = a; bestLen = path.length; return; }
						if (current.indexOf(path + '/') === 0 && path.length > bestLen) { best = a; bestLen = path.length; }
					});

					if (best) {
						best.classList.add('active');
						var acc = best.closest('.menu-accordion');
						if (acc) {
							acc.classList.add('here', 'show');
							var sub = acc.querySelector('.menu-sub');
							if (sub) { sub.style.display = 'block'; }
						}
					}
				} catch (e) {/* no-op */ }
			})();
		</script>

		<!-- ACL: Global JavaScript for Higher Authority - 12/11/25 -->
		<script>
			<?php
			helper('designation_acl');
			// Set global variable for filter scripts to detect Higher Authority role
			?>
			window.isHigherAuthorityUser = <?= isHigherAuthority() ? 'true' : 'false' ?>;
			<?php if (isHigherAuthority()): ?>
					// COMPLETE READ-ONLY MODE: Hide and disable ALL action buttons for Higher Authority
					(function () {
						// Wait for DOM to be ready
						if (document.readyState === 'loading') {
							document.addEventListener('DOMContentLoaded', makeCompletelyReadOnly);
						} else {
							makeCompletelyReadOnly();
						}

						function makeCompletelyReadOnly() {
							// 1. Visually disable Action Icons (Edit, Delete, Upload) instead of hiding to prevent weird blank spaces
							var actionIcons = document.querySelectorAll('.fa-edit, .fa-pencil, .fa-pen, .fa-trash, .fa-delete, .fa-remove, .fa-upload, .fa-check, .fa-times, .fa-ban');
							actionIcons.forEach(function (icon) {
								var link = icon.closest('a, button, .card');
								if (link) {
									link.style.pointerEvents = 'none';
									link.style.opacity = '0.4';
									link.style.cursor = 'not-allowed';
									link.classList.add('disabled-action');
									link.onclick = function(e) { e.preventDefault(); return false; };
								}
							});

							// 2. Visually disable specific Action Buttons (Blacklist approach)
							var allButtons = document.querySelectorAll('button, a.btn, a[href], input[type="submit"], input[type="button"], .card');
							allButtons.forEach(function (btn) {
								var text = btn.textContent.toLowerCase().trim();
								var html = btn.innerHTML.toLowerCase();
								var id = (btn.id || '').toLowerCase();
								var href = (btn.getAttribute('href') || '').toLowerCase();
								var onclick = (btn.getAttribute('onclick') || '').toLowerCase();
								var title = (btn.getAttribute('title') || btn.getAttribute('data-bs-original-title') || '').toLowerCase();

								// Specifically blacklist destructive/modification actions
								var isActionBtn = 
									text === 'add' || text === 'new' || text.includes('add ') || text.includes('edit ') || text === 'edit' ||
									text.includes('delete') || text.includes('upload') || 
									text.includes('save') || text.includes('submit') || text.includes('update') ||
									text.includes('approve') || text.includes('reject') || text.includes('force close') || text.includes('reaudit') || text.includes('perform audit') ||
									id.includes('add') || id.includes('edit') || id.includes('delete') || id.includes('upload') ||
									href.includes('create') || href.includes('edit') || href.includes('delete') || href.includes('reaudit') || href.includes('perform') ||
									onclick.includes('delete') || onclick.includes('remove') || onclick.includes('approve') || onclick.includes('reject') || onclick.includes('reaudit') || onclick.includes('perform') ||
									title.includes('reaudit') || title.includes('perform audit');
								
								// Ensure we don't accidentally disable exports, downloads, views, or filters
								var isSafeBtn = text.includes('export') || text.includes('download') || text.includes('view') || text.includes('filter') || text.includes('search') || html.includes('fa-eye') || html.includes('fa-file') || html.includes('fa-download');

								if (isActionBtn && !isSafeBtn) {
									btn.style.pointerEvents = 'none';
									btn.style.opacity = '0.4';
									btn.style.cursor = 'not-allowed';
									btn.classList.add('disabled-action');
									btn.disabled = true;
									btn.onclick = function(e) { e.preventDefault(); return false; };
								}
							});

							// 3. Disable Inputs inside Add/Edit Modals only
							var modals = document.querySelectorAll('.modal');
							modals.forEach(function (modal) {
								var modalId = modal.id.toLowerCase();
								// If it is an Add/Edit modal, disable its inputs
								if (modalId.includes('add') || modalId.includes('edit') || modalId.includes('create') || modalId.includes('update')) {
									var inputs = modal.querySelectorAll('input, textarea, select');
									inputs.forEach(function (input) {
										input.disabled = true;
										input.style.cursor = 'not-allowed';
									});
								}
							});
							
							// 4. Aggressively protect specific backend routes for modifications by hooking forms
							var forms = document.querySelectorAll('form');
							forms.forEach(function (form) {
								var action = (form.getAttribute('action') || '').toLowerCase();
								var formId = (form.id || '').toLowerCase();
								
								var isModForm = action.includes('save') || action.includes('create') || action.includes('update') || action.includes('delete') || action.includes('upload') || action.includes('approve') || action.includes('reject') ||
												formId.includes('add') || formId.includes('edit') || formId.includes('upload');
								
								if (isModForm) {
									form.onsubmit = function(e) {
										e.preventDefault();
										alert('You have READ-ONLY access. Modifications are not allowed.');
										return false;
									};
									var submitBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
									submitBtns.forEach(function(b) { b.style.display = 'none'; });
								}
							});

							// console.log('ACL: Safe READ-ONLY MODE activated for Higher Authority');
						}

						// Re-apply after AJAX updates and page changes
						if (typeof $ !== 'undefined') {
							$(document).ajaxComplete(function () {
								setTimeout(makeCompletelyReadOnly, 100);
							});

							// Also re-apply on any DOM changes
							var observer = new MutationObserver(function (mutations) {
								setTimeout(makeCompletelyReadOnly, 100);
							});

							observer.observe(document.body, {
								childList: true,
								subtree: true
							});
						}
					})();
			<?php endif; ?>
		</script>

		<!-- Sidebar arrow JS: sync with Metronic aside toggle -->
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				var headerArrow = document.getElementById('sidebar_toggle_arrow');
				var asideToggle = document.getElementById('kt_aside_toggle');

				if (headerArrow && asideToggle) {
					// Set initial direction based on current state
					if (document.body.classList.contains('aside-minimize')) {
						headerArrow.innerHTML = '<i class="fa fa-angle-right"></i>';
					} else {
						headerArrow.innerHTML = '<i class="fa fa-angle-left"></i>';
					}

					headerArrow.addEventListener('click', function () {
						// Trigger Metronic's original aside toggle
						asideToggle.click();

						// Wait a bit for Metronic to update body classes
						setTimeout(function () {
							if (document.body.classList.contains('aside-minimize')) {
								headerArrow.innerHTML = '<i class="fa fa-angle-right"></i>';
							} else {
								headerArrow.innerHTML = '<i class="fa fa-angle-left"></i>';
							}
						}, 150);
					});
				}
			});
		</script>
		<script src="<?= base_url() ?>/assets/js/custom-multiselect.js"></script>

		<!--end::Javascript-->
		<script>
			function viewAttendanceModal(hseAuditId) {
				$.ajax({
					url: '<?= base_url('Masters/Hse_audit/view_attendance_modal') ?>/' + hseAuditId,
					type: 'GET',
					success: function(response) {
						$('#globalAjaxModalContent').html(response);
						$('#globalAjaxModal').modal('show');
					},
					error: function() {
						alert('Failed to fetch attendance details.');
					}
				});
			}
		</script>

		<!-- Global Tooltips Injector -->
		<script>
		document.addEventListener("DOMContentLoaded", function() {
			function applyTooltips(rootNode) {
				const iconMap = {
					'.fa-eye': 'View',
					'.fa-edit': 'Edit',
					'.fa-trash': 'Delete',
					'.fa-trash-alt': 'Delete',
					'.fa-file-excel': 'Export Excel',
					'.fa-file-csv': 'Export CSV',
					'.fa-download': 'Download',
					'.fa-upload': 'Upload',
					'.fa-print': 'Print',
					'.fa-sync': 'Refresh',
					'.fa-sync-alt': 'Refresh',
					'.fa-undo': 'Reset',
					'.fa-times': 'Close',
					'.fa-check': 'Approve',
					'.fa-check-circle': 'Approve',
					'.fa-times-circle': 'Reject',
					'.fa-ban': 'Reject',
					'.fa-search': 'Search',
					'.fa-lock': 'Deactivate',
					'.fa-unlock': 'Activate'
				};

				let elementsModified = false;

				for (const [selector, title] of Object.entries(iconMap)) {
					const elements = rootNode.querySelectorAll ? rootNode.querySelectorAll(selector) : [];
					elements.forEach(icon => {
						const parent = icon.closest('a, button');
						const target = parent || icon;
						if (!target.hasAttribute('title') && !target.hasAttribute('data-bs-original-title')) {
							target.setAttribute('title', title);
							target.setAttribute('data-bs-toggle', 'tooltip');
							elementsModified = true;
						}
					});
				}

				// Optional: Text-based buttons
				const buttons = rootNode.querySelectorAll ? rootNode.querySelectorAll('button:not([title]):not([data-bs-original-title])') : [];
				buttons.forEach(btn => {
					const text = btn.textContent.trim();
					if (text && text.length < 20) { // arbitrary limit so we don't tooltip long phrases
						btn.setAttribute('title', text);
						btn.setAttribute('data-bs-toggle', 'tooltip');
						elementsModified = true;
					}
				});

				// Tooltip initialization is now handled via delegation globally.
			}

			// Apply initially
			applyTooltips(document.body);

			// Initialize Bootstrap tooltips globally using event delegation
			if (typeof jQuery !== 'undefined' && typeof jQuery.fn.tooltip !== 'undefined') {
				jQuery('body').tooltip({
					selector: '[data-bs-toggle="tooltip"]',
					trigger: 'hover'
				});
			}

			// MutationObserver to handle DataTables and AJAX loaded content
			const observer = new MutationObserver(mutations => {
				let shouldApply = false;
				for (const mutation of mutations) {
					if (mutation.addedNodes.length) {
						shouldApply = true;
						break;
					}
				}
				if (shouldApply) {
					applyTooltips(document.body);
				}
			});
			
			observer.observe(document.body, { childList: true, subtree: true });
		});

		// Session Heartbeat
		setInterval(function() {
			$.ajax({
				url: '<?= base_url('Login/heartbeat') ?>',
				type: 'POST',
				dataType: 'json',
				success: function(response) {
					// Heartbeat sent successfully
				},
				error: function(xhr, status, error) {
					// Optionally handle error, e.g., if session expired, reload page
					if(xhr.status === 403) {
						// window.location.reload(); 
					}
				}
			});
		}, 60000); // 1 minute
		</script>

		<?php $this->renderSection("javascript_section"); ?>

	</body>
	<!--end::Body-->

	</html>