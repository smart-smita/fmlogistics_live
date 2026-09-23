<?php 
$this->extend("Layout/base_admin");
?>

<?php 
					$this->section("breadcrumb_title_li");
?>
<style>
 
    
  table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-family: Arial, sans-serif;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px 12px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .average-row {
            font-weight: bold;
            text-align: right;
            background-color: #e0e0e0;
        }
          
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .card {
            max-height: 500px;
            overflow-y: auto;
        }
         .table-custom th, .table-custom td {
            padding: 10px;
            text-align: center;
           
        }
        .table-custom .header-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        
       
       
    </style>

<?php 
					$this->endSection();
?>										
<?php 
					$this->section("main_body");
?>
    <div class="row g-5 g-xl-10">
    
        <div class="col-xl-12">
        
                <div class="card h-md-100">
                    
                    <div class="card-header position-relative py-0 border-bottom-1">
                        
                        <h3 class="card-title text-gray-800 fw-bold">Score and Openpoints Reports</h3>
                        
                        <ul class="nav nav-stretch nav-pills nav-pills-custom d-flex mt-4" role="tablist">
                            <!-- <li class="nav-item p-0 ms-0" role="presentation">
                                <a class="nav-link btn btn-color-gray-500 flex-center px-3 active" data-kt-timeline-widget-4="tab" data-bs-toggle="tab" href="#kt_timeline_widget_4_tab_day" aria-selected="true" role="tab"> 
                                   
                                    <span class="nav-text fw-semibold fs-4 mb-3">Normal</span> 
                                    
                                    <span class="bullet-custom position-absolute z-index-2 w-100 h-1px top-100 bottom-n100 bg-primary rounded"></span>
                                   
                                </a>
                            </li> -->
                           
                            <li class="nav-item p-0 ms-0" role="presentation">
                                <a class="nav-link btn btn-color-gray-500 flex-center px-3" data-kt-timeline-widget-4="tab" data-bs-toggle="tab" href="#kt_timeline_widget_4_tab_week" aria-selected="false" role="tab" tabindex="-1">
                               
                                <span class="nav-text fw-semibold fs-4 mb-3">OE</span> 
                                
                                <span class="bullet-custom position-absolute z-index-2 w-100 h-1px top-100 bottom-n100 bg-primary rounded"></span>
                                   
                                </a>
                            </li>
                           
                            <li class="nav-item p-0 ms-0" role="presentation">
                                <a class="nav-link btn btn-color-gray-500 flex-center px-3" data-kt-timeline-widget-4="tab" data-bs-toggle="tab" href="#kt_timeline_widget_4_tab_month" aria-selected="false" role="tab" tabindex="-1">
                                    
                                    <span class="nav-text fw-semibold fs-4 mb-3">HSE</span> 
                                   
                                    <span class="bullet-custom position-absolute z-index-2 w-100 h-1px top-100 bottom-n100 bg-primary rounded"></span>
                                    
                                </a>
                            </li>
                        </ul>       
                    </div>
                <br><br>
                <form method="GET" action="<?= base_url('Dashboard/filter'); ?>" class="row g-3">
                                <div class="row">

                                    <div class="col-md-2 col-lg-2 col-xl-3 mb-4">
                                        <div class="col-md-12 fv-row fv-plugins-icon-container">
                                          <label class="required fs-5 fw-bold mb-2">Region</label>
                                          <select class="form-select select2-filter" id="region" name="region[]" multiple="multiple" data-placeholder="Select Region">
                                        		<?php foreach($region as $row){?>
                                        		<option value="<?=$row['region_name']?>"><?=$row['region_name']?></option>
                                        		<?php }?>
                                        		</select>
                                        </div>
                                    </div>
                                        
                                        <div class="col-md-2 col-lg-2 col-xl-3 mb-4">
                                            <div class="col-md-12 fv-row fv-plugins-icon-container">
                                                <label class="required fs-5 fw-bold mb-2">Month</label>
                                                <select name="financial_month[]" id="financial_month" class="form-select select2-filter" multiple="multiple" data-placeholder="Select Financial Month">
                                                    <?php foreach ($months as $key => $month): ?>
                                                        <option value="<?= $month; ?>"><?= $month; ?></option>
                                                    <?php endforeach; ?>
                                                </select>

                                            </div>
                                        </div>
                                    
                                        
                                        <div class="col-md-2 col-lg-2 col-xl-3 mb-4">
                                            <div class="col-md-12 fv-row fv-plugins-icon-container">
                                                <label class="required fs-5 fw-bold mb-2">Year</label>
                                                 <select class="form-select select2-filter"  id="financial_year" name="financial_year[]" multiple="multiple" data-placeholder="Select Financial Year">
                                             	    <?php foreach($financialYears as $year){?>
                                             		<option value="<?=$year?>"><?=$year?></option>
                                             		<?php }?>
                                             	</select>	
                                            </div>
                                        </div>
                                    
                                        <div class="col-md-2 col-lg-2  col-xl-3 mb-5">
                                            <br>
                                            <div class="col-md-6 d-flex align-items-end">
                                                <button type="submit" class="btn btn-primary">Show</button>
                                            </div>
                                    	</div>	
                                    </div>
                                    </form>
                
                    
                    <div class="card-body pb-0">

                        <div class="tab-content">

                            <div class="tab-pane blockui active show" id="kt_timeline_widget_4_tab_day" role="tabpanel" aria-labelledby="day-tab" data-kt-timeline-widget-4-blockui="true" style="">
                                
                                    <table class="table table-bordered table-custom">
                                        <thead>
                                            <tr class="header-row">
                                                <th>Location</th>
                                                <th>Score</th>
                                                <th>OpenPoints</th>
                                                <th>Remark</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                           
                                            <?php if(isset($total_Normal_score_openpoints)) { foreach ($total_Normal_score_openpoints as $row): ?>
                                                <tr>
                                                    <td><?= $row['location']; ?></td>
                                                    <td><?= $row['avg_score_percentage']; ?></td>
                                                    <td><?= $row['total_openpoints']; ?></td>
                                                    <td><?= $row['remarks_combined']; ?></td>
                                                </tr>
                                            <?php endforeach; } ?>
                                            
                                           
                                        </tbody>
                                    </table>
                                
                            </div>
                            
                            <div class="tab-pane blockui" id="kt_timeline_widget_4_tab_week" role="tabpanel" aria-labelledby="day-tab" data-kt-timeline-widget-4-blockui="true" style="">
                                
                                    <table class="table table-bordered table-custom">
                                        <thead>
                                            <tr class="header-row">
                                                <th>Location</th>
                                                <th>Score</th>
                                                <th>OpenPoints</th>
                                                <th>Remark</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                           
                                            <?php if(isset($total_OE_score_openpoints)) { foreach ($total_OE_score_openpoints as $row): ?>
                                                <tr>
                                                    <td><?= $row['location']; ?></td>
                                                    <td><?= $row['avg_score_percentage']; ?></td>
                                                    <td><?= $row['total_openpoints']; ?></td>
                                                    <td><?= $row['remarks_combined']; ?></td>
                                                </tr>
                                            <?php endforeach; } ?>
                                            
                                           
                                        </tbody>
                                    </table>
                                
                            </div>
                            
                            <div class="tab-pane blockui" id="kt_timeline_widget_4_tab_month" role="tabpanel" aria-labelledby="day-tab" data-kt-timeline-widget-4-blockui="true" style="">
                                
                                    <table class="table table-bordered table-custom">
                                        <thead>
                                            <tr class="header-row">
                                                <th>Location</th>
                                                <th>Client Leased Score</th>
                                                <th>Inplant Score</th>
                                                <th>FM Leased Score</th>
                                                <th>Client Leased OpenPoints</th>
                                                <th>Inplant OpenPoints</th>
                                                <th>FM Leased OpenPoints</th>
                                                <th>Remark</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                           
                                            <?php if(isset($total_HSE_score_openpoints)) { foreach ($total_HSE_score_openpoints as $row): ?>
                                                <tr>
                                                    <td><?= $row['location']; ?></td>
                                                    <td><?= $row['client_leased_avg_score']; ?>%</td>
                                                    <td><?= $row['inplant_avg_score']; ?>%</td>
                                                    <td><?= $row['fm_leased_avg_score']; ?>%</td>
                                                    <td><?= $row['client_leased_openpoints']; ?></td>
                                                    <td><?= $row['inplant_openpoints']; ?></td>
                                                    <td><?= $row['fm_leased_openpoints']; ?></td>
                                                    <td><?= $row['remarks']; ?></td>
                                                </tr>
                                            <?php endforeach; } ?>
                                            
                                           
                                        </tbody>
                                    </table>
                                
                            </div>
                
                        </div>
                        
                    </div>
                    
                </div>
               
        </div>
     
    </div>

    <br><br>
    <div class="row g-5 g-xl-10">
        <div class="col-xl-12">
                <div class="card h-md-100">
                    <div class="card-header position-relative py-0 border-bottom-1">
                        
                        <h3 class="card-title text-gray-800 fw-bold">Month Summary</h3>
                        
                        <ul class="nav nav-stretch nav-pills nav-pills-custom d-flex mt-4" role="tablist">
                            <!-- <li class="nav-item p-0 ms-0" role="presentation">
                                <a class="nav-link btn btn-color-gray-500 flex-center px-3 active" data-kt-timeline-widget-4="tab" data-bs-toggle="tab" href="#normal_year_score" aria-selected="true" role="tab"> 
                                   
                                    <span class="nav-text fw-semibold fs-4 mb-3">Normal</span> 
                                    
                                    <span class="bullet-custom position-absolute z-index-2 w-100 h-1px top-100 bottom-n100 bg-primary rounded"></span>
                                   
                                </a>
                            </li> -->
                            <li class="nav-item p-0 ms-0" role="presentation">
                                <a class="nav-link btn btn-color-gray-500 flex-center px-3" data-kt-timeline-widget-4="tab" data-bs-toggle="tab" href="#OE_year_score" aria-selected="false" role="tab" tabindex="-1">
                               
                                <span class="nav-text fw-semibold fs-4 mb-3">OE</span> 
                                
                                <span class="bullet-custom position-absolute z-index-2 w-100 h-1px top-100 bottom-n100 bg-primary rounded"></span>
                                   
                                </a>
                            </li>
                           
                            <li class="nav-item p-0 ms-0" role="presentation">
                                <a class="nav-link btn btn-color-gray-500 flex-center px-3" data-kt-timeline-widget-4="tab" data-bs-toggle="tab" href="#HSE_year_score" aria-selected="false" role="tab" tabindex="-1">
                                    
                                    <span class="nav-text fw-semibold fs-4 mb-3">HSE</span> 
                                   
                                    <span class="bullet-custom position-absolute z-index-2 w-100 h-1px top-100 bottom-n100 bg-primary rounded"></span>
                                    
                                </a>
                            </li>
                        </ul>       
                    </div>
                <br><br>
                    <form method="GET" action="<?= base_url('Dashboard/filter'); ?>" class="row g-3">
                        <div class="row">
                            <div class="col-md-2 col-lg-2 col-xl-3 mb-4">
                                <div class="col-md-12 fv-row fv-plugins-icon-container">
                                    <label class="required fs-5 fw-bold mb-2">Year</label>
                                     <select class="form-select select2-filter" id="financial_year_months" name="financial_year_months[]" multiple="multiple" data-placeholder="Select Financial Year">
                                	    <?php foreach($financialYears as $year){?>
                                		<option value="<?=$year?>"><?=$year?></option>
                                		<?php }?>
                                	</select>
                                </div>
                            </div>
                        
                            <div class="col-md-2 col-lg-2  col-xl-3 mb-5">
                                <br>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Show</button>
                                </div>
                        	</div>	
                        </div>
                    </form>
                
                    
                    <div class="card-body pb-0">

                        <div class="tab-content">

                            <div class="tab-pane blockui active show" id="normal_year_score" role="tabpanel" aria-labelledby="day-tab" data-kt-timeline-widget-4-blockui="true" style="">
                                
                                   <table class="table table-bordered table-custom">
                            <thead>
                                <tr class="header-row">
                                    <th>Location</th>
                                    <th>Region</th>
                                    <th>Cluster</th>
                                    <?php
                                    // Define all 12 months explicitly
                                    
                                    foreach ($months as $month): ?>
                                        <th><?= esc($month); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // // Group data by client_name for row-wise display
                                // $clients = [];
                                // foreach ($audit as $row) {
                                //     // Group the data by client_name and audit_month
                                //     $clients[$row['client_name']][$row['audit_month']] = $row['yes'];
                                // }
                        
                                // Display each client with data organized by month
                                foreach ($normal_year_score_query as $normal_year_score): ?>
                                    <tr>
                                        <td><?php echo $normal_year_score['location']; ?></td>
                                        <td><?php echo $normal_year_score['region']; ?></td>
                                        <td><?php echo $normal_year_score['cluster_name']; ?></td>
                                        <td><?php echo $normal_year_score['Apr_Score']; ?></td>
                                        <td><?php echo $normal_year_score['May_Score']; ?></td>
                                        <td><?php echo $normal_year_score['Jun_Score']; ?></td>
                                        <td><?php echo $normal_year_score['Jul_Score']; ?></td>
                                        <td><?php echo $normal_year_score['Aug_Score']; ?></td>
                                        <td><?php echo $normal_year_score['Sep_Score']; ?></td>
                                        <td><?php echo $normal_year_score['Oct_Score']; ?></td>
                                        <td><?php echo $normal_year_score['Nov_Score']; ?></td>
                                        <td><?php echo $normal_year_score['Dec_Score']; ?></td>
                                        <td><?php echo $normal_year_score['Jan_Score']; ?></td>
                                        <td><?php echo $normal_year_score['Feb_Score']; ?></td>
                                        <td><?php echo $normal_year_score['Mar_Score']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                            </div>
                            
                            <div class="tab-pane blockui" id="OE_year_score" role="tabpanel" aria-labelledby="day-tab" data-kt-timeline-widget-4-blockui="true" style="">
                                
                                   <table class="table table-bordered table-custom">
                            <thead>
                                <tr class="header-row">
                                    <th>Location</th>
                                    <th>Region</th>
                                    <th>Cluster</th>
                                    <?php
                                    // Define all 12 months explicitly
                                    
                                    foreach ($months as $month): ?>
                                        <th><?= esc($month); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // // Group data by client_name for row-wise display
                                // $clients = [];
                                // foreach ($audit as $row) {
                                //     // Group the data by client_name and audit_month
                                //     $clients[$row['client_name']][$row['audit_month']] = $row['yes'];
                                // }
                        
                                // Display each client with data organized by month
                                foreach ($OE_year_score_query as $OE_year_score): ?>
                                    <tr>
                                        <td><?php echo $OE_year_score['location']; ?></td>
                                        <td><?php echo $OE_year_score['region']; ?></td>
                                        <td><?php echo $OE_year_score['cluster_name']; ?></td>
                                        <td><?php echo $OE_year_score['Apr_Score']; ?></td>
                                        <td><?php echo $OE_year_score['May_Score']; ?></td>
                                        <td><?php echo $OE_year_score['Jun_Score']; ?></td>
                                        <td><?php echo $OE_year_score['Jul_Score']; ?></td>
                                        <td><?php echo $OE_year_score['Aug_Score']; ?></td>
                                        <td><?php echo $OE_year_score['Sep_Score']; ?></td>
                                        <td><?php echo $OE_year_score['Oct_Score']; ?></td>
                                        <td><?php echo $OE_year_score['Nov_Score']; ?></td>
                                        <td><?php echo $OE_year_score['Dec_Score']; ?></td>
                                        <td><?php echo $OE_year_score['Jan_Score']; ?></td>
                                        <td><?php echo $OE_year_score['Feb_Score']; ?></td>
                                        <td><?php echo $OE_year_score['Mar_Score']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                            </div>
                            
                            <div class="tab-pane blockui" id="HSE_year_score" role="tabpanel" aria-labelledby="day-tab" data-kt-timeline-widget-4-blockui="true" style="">
                                
                                   <table class="table table-bordered table-custom">
                            <thead>
                                <tr class="header-row">
                                    <th>Location</th>
                                    <th>Region</th>
                                    <th>Cluster</th>
                                    <th>Audit Month</th>
                                    <th>Client leased Score</th>
                                    <th>Inplant Score</th>
                                    <th>FM leased Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // // Group data by client_name for row-wise display
                                // $clients = [];
                                // foreach ($audit as $row) {
                                //     // Group the data by client_name and audit_month
                                //     $clients[$row['client_name']][$row['audit_month']] = $row['yes'];
                                // }
                        
                                // Display each client with data organized by month
                                foreach ($HSE_year_score_query as $HSE_year_score): ?>
                                    <tr>
                                        <td><?php echo $HSE_year_score['location']; ?></td>
                                        <td><?php echo $HSE_year_score['region']; ?></td>
                                        <td><?php echo $HSE_year_score['cluster_name']; ?></td>
                                        <td><?php echo $HSE_year_score['audit_month']; ?></td>
                                        <td><?php echo $HSE_year_score['client_leased_avg_score']; ?></td>
                                        <td><?php echo $HSE_year_score['inplant_avg_score']; ?></td>
                                        <td><?php echo $HSE_year_score['inplant_avg_score']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                            </div>
                
                        </div>
                        
                    </div>
                    
                </div>
               
        </div>
     
    </div>
   
    <br><br>

    <div class="card pt-2 mb-6 mb-xl-9">
    
    <div class="card-header border-0">
        
        <div class="card-title">
            <h2>Aging</h2>
        </div>
        

        <!--begin::Toolbar-->
        <div class="card-toolbar m-0">
            <!--begin::Tab nav-->
            <ul class="nav nav-stretch fs-5 fw-semibold nav-line-tabs nav-line-tabs-2x border-transparent" role="tablist">
                <li class="nav-item" role="presentation">
                    <a id="kt_referrals_year_tab" class="nav-link text-active-primary active" data-bs-toggle="tab" role="tab" href="#kt_customer_details_invoices_1" aria-selected="true">
                        Normal
                    </a>
                </li>

                <li class="nav-item" role="presentation">
                    <a id="kt_referrals_2019_tab" class="nav-link text-active-primary ms-3" data-bs-toggle="tab" role="tab" href="#kt_customer_details_invoices_2" aria-selected="false" tabindex="-1">
                        OE
                    </a>
                </li>

                <li class="nav-item" role="presentation">
                    <a id="kt_referrals_2018_tab" class="nav-link text-active-primary ms-3" data-bs-toggle="tab" role="tab" href="#kt_customer_details_invoices_3" aria-selected="false" tabindex="-1">
                        HSE
                    </a>
                </li>

            </ul>
            <!--end::Tab nav-->
        </div>
        <!--end::Toolbar-->
    </div>
    
     <div class="card-body pt-0">
        <div class="card-toolbar" data-select2-id="select2-data-124-5t5f">
            <form method="GET" action="<?= base_url('Dashboard/filter'); ?>" class="row g-3">
                <!--begin::Filters-->
                <div class="d-flex flex-stack flex-wrap gap-4" data-select2-id="select2-data-123-h8fu">
                    <!--begin::Destination-->
                    <div class="d-flex align-items-center fw-bold" data-select2-id="select2-data-122-xtxd">
                       
                        <div class="text-gray-500 fs-7 me-2">Region</div>
                        
                        
                        <select name="aging_reason" class="form-select form-select-transparent text-graY-800 fs-base lh-1 fw-bold py-0 ps-3 w-auto select2-hidden-accessible" data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px" data-placeholder="Select an option" data-select2-id="select2-data-7-baii" tabindex="-1" aria-hidden="true" data-kt-initialized="1"> 
                            <option value="Show All" selected="" data-select2-id="select2-data-9-6azc">Show All</option>
                            <?php if(isset($region)) { foreach ($region as $zone): ?>
                            <option value="<?= $zone['region_name'] ?>" data-select2-id="select2-data-<?= $zone['region_name'] ?>"><?= $zone['region_name'] ?></option>
                           <?php endforeach; } ?>
                            
                        </select>
                        
                    <button type="submit" class="btn btn-primary">Show</button>
                    </div>
                    
                </div>
                <!--begin::Filters-->
            </form>
        </div>
    </div>
    
    <div class="card-body pt-0">
        <!--begin::Tab Content-->
        <div id="kt_referred_users_tab_content" class="tab-content">
                                            <!--begin::Tab panel-->
                <div id="kt_customer_details_invoices_1" class="py-0 tab-pane fade active show" role="tabpanel" aria-labelledby="kt_referrals_year_tab">
                    <!--begin::Table-->
                    
                    <div id="kt_customer_details_invoices_table_1_wrapper" class="dt-container dt-bootstrap5 dt-empty-footer"><div id="" class="table-responsive">
                        
                        <table id="kt_customer_details_invoices_table_2" class="table align-middle table-row-dashed fs-6 fw-bold gy-5 dataTable" style="width: 100%;"><colgroup><col data-dt-column="0" style="width: 0px;"><col data-dt-column="1" style="width: 0px;"><col data-dt-column="2" style="width: 0px;"><col data-dt-column="3" style="width: 0px;"><col data-dt-column="4" style="width: 0px;"></colgroup>
                        <thead class="border-bottom border-gray-200 fs-7 text-uppercase fw-bold">
                           
                            <tr class="text-start text-muted gs-0">
                                
                                    <th class="min-w-100px dt-orderable-asc dt-orderable-desc" data-dt-column="0" aria-label="Audit No: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Audit No</span><span class="dt-column-order"></span></th>
                                    <th class="min-w-100px dt-type-numeric dt-orderable-asc dt-orderable-desc" data-dt-column="2" aria-label="Name: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Name</span><span class="dt-column-order"></span></th>
                                    <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="3" aria-label="Region: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Region</span><span class="dt-column-order"></span></th>
                                    <?php if(isset($category_normal)) { foreach ($category_normal as $cat): ?>
                                        <th class="min-w-100px dt-orderable-asc dt-orderable-desc" ><span class="dt-column-title" role="button"><?= $cat['category']; ?></span><span class="dt-column-order"></span></th>
                                    <?php endforeach; } ?>
                                
                                    <th class="min-w-100px dt-orderable-asc dt-orderable-desc"><span class="dt-column-title" role="button">OE Score</span><span class="dt-column-order"></span></th>
                                </tr>
                        </thead>
                        <tbody class="fs-6 fw-semibold text-gray-600">
                                <?php if (!empty($aging_normal_score)):
                               // $old_category = "";
                                
                                ?>
                                    <?php foreach ($aging_normal_score as $finding): 
                                    //$old_category = $finding['audit_no'];
                                    //if($old_category!=$finding['audit_no']){
                                    ?>
                                        <tr>
                                            <td><?php echo $finding['audit_no']; ?></td>
                                            <td><?php echo $finding['audit_name']; ?></td>
                                            <td><?php echo $finding['region']; ?></td>
                                            <?php //}else{ ?>
                                            <?php foreach ($category_normal as $category): ?>
                                                <td>
                                                    <?php 
                                                        // Check if the current category matches the category for this row
                                                        echo ($finding['category'] == $category['category']) ? esc($finding['openpoints']) : '0';
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <?php //} ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="<?php echo count($category_normal) + 3; ?>">No data available.</td>
                                </tr>
                            <?php endif; ?>
                                </tbody>
                    </table></div><div id="" class="row"><div id="" class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start dt-toolbar"></div><div id="" class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end"><div class="dt-paging paging_simple_numbers"><nav aria-label="pagination"><ul class="pagination"><li class="dt-paging-button page-item disabled"><button class="page-link previous" role="link" type="button" aria-controls="kt_customer_details_invoices_table_1" aria-disabled="true" aria-label="Previous" data-dt-idx="previous" tabindex="-1"><i class="previous"></i></button></li><li class="dt-paging-button page-item active"><button class="page-link" role="link" type="button" aria-controls="kt_customer_details_invoices_table_1" aria-current="page" data-dt-idx="0">1</button></li><li class="dt-paging-button page-item"><button class="page-link" role="link" type="button" aria-controls="kt_customer_details_invoices_table_1" data-dt-idx="1">2</button></li><li class="dt-paging-button page-item"><button class="page-link next" role="link" type="button" aria-controls="kt_customer_details_invoices_table_1" aria-label="Next" data-dt-idx="next"><i class="next"></i></button></li></ul></nav></div></div></div></div>
                    <!--end::Table-->
                </div>
                <!--end::Tab panel-->
                                            <!--begin::Tab panel-->
                <div id="kt_customer_details_invoices_2" class="py-0 tab-pane fade" role="tabpanel" aria-labelledby="kt_referrals_2019_tab">
                                
                    <!--begin::Table-->
                    <div id="kt_customer_details_invoices_table_2_wrapper" class="dt-container dt-bootstrap5 dt-empty-footer"><div id="" class="table-responsive">
                                <table id="kt_customer_details_invoices_table_2" class="table align-middle table-row-dashed fs-6 fw-bold gy-5 dataTable" style="width: 100%;"><colgroup><col data-dt-column="0" style="width: 0px;"><col data-dt-column="1" style="width: 0px;"><col data-dt-column="2" style="width: 0px;"><col data-dt-column="3" style="width: 0px;"><col data-dt-column="4" style="width: 0px;"></colgroup>
                                <thead class="border-bottom border-gray-200 fs-7 text-uppercase fw-bold">
                                    <tr class="text-start text-muted gs-0">
                                            <th class="min-w-100px dt-orderable-asc dt-orderable-desc" data-dt-column="0" aria-label="Audit No: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Audit No</span><span class="dt-column-order"></span></th>
                                    <th class="min-w-100px dt-type-numeric dt-orderable-asc dt-orderable-desc" data-dt-column="2" aria-label="Name: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Name</span><span class="dt-column-order"></span></th>
                                    <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="3" aria-label="Region: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Region</span><span class="dt-column-order"></span></th>
                                    <?php if($category_OE) { foreach ($category_OE as $cat): ?>
                                        <th class="min-w-100px dt-orderable-asc dt-orderable-desc" ><span class="dt-column-title" role="button"><?= $cat['category']; ?></span><span class="dt-column-order"></span></th>
                                    <?php endforeach; } ?>
                                        </tr>
                                </thead>
                                <tbody class="fs-6 fw-semibold text-gray-600">
                                        
                                      <?php if (!empty($aging_OE_score)):
                                            $old_category = "";
                                            
                                            ?>
                                                <?php foreach ($aging_OE_score as $finding): 
                                                $old_category = esc($finding['audit_no']);
                                                //if($old_category!=esc($finding['audit_no'])){
                                                ?>
                                                    <tr>
                                                        <td><?php echo esc($finding['audit_no']); ?></td>
                                                        <td><?php echo esc($finding['audit_name']); ?></td>
                                                        <td><?php echo esc($finding['region']); ?></td>
                                                        <?php //}else{ ?>
                                                        <?php foreach ($category_OE as $category): ?>
                                                            <td>
                                                                <?php 
                                                                    // Check if the current category matches the category for this row
                                                                    echo ($finding['category'] == $category['category']) ? esc($finding['openpoints']) : '0';
                                                                ?>
                                                            </td>
                                                        <?php endforeach; ?>
                                                        <?php //} ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                            <tr>
                                                <td colspan="<?php echo count($category_OE) + 3; ?>">No data available.</td>
                                            </tr>
                                        <?php endif; ?>
                                    
                               
                                        
                                        </tbody>
                            </table>
                    </div><div id="" class="row"><div id="" class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start dt-toolbar"></div><div id="" class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end"><div class="dt-paging paging_simple_numbers"><nav aria-label="pagination"><ul class="pagination"><li class="dt-paging-button page-item disabled"><button class="page-link previous" role="link" type="button" aria-controls="kt_customer_details_invoices_table_2" aria-disabled="true" aria-label="Previous" data-dt-idx="previous" tabindex="-1"><i class="previous"></i></button></li><li class="dt-paging-button page-item active"><button class="page-link" role="link" type="button" aria-controls="kt_customer_details_invoices_table_2" aria-current="page" data-dt-idx="0">1</button></li><li class="dt-paging-button page-item"><button class="page-link next" role="link" type="button" aria-controls="kt_customer_details_invoices_table_2" aria-label="Next" data-dt-idx="next"><i class="next"></i></button></li></ul></nav></div></div></div></div>
                    <!--end::Table-->
                </div>
                <!--end::Tab panel-->
                                            <!--begin::Tab panel-->
                <div id="kt_customer_details_invoices_3" class="py-0 tab-pane fade" role="tabpanel" aria-labelledby="kt_referrals_2018_tab">
                        <!--<div class="card-toolbar" data-select2-id="select2-data-124-5t5f">-->

                        <!--            <div class="d-flex flex-stack flex-wrap gap-4" data-select2-id="select2-data-123-h8fu">-->

                        <!--                <div class="d-flex align-items-center fw-bold" data-select2-id="select2-data-122-xtxd">-->
                                           
                        <!--                    <div class="text-gray-500 fs-7 me-2">Region</div>-->
                                            
                        
                                            
                        <!--                    <select class="form-select form-select-transparent text-graY-800 fs-base lh-1 fw-bold py-0 ps-3 w-auto select2-hidden-accessible" data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px" data-placeholder="Select an option" data-select2-id="select2-data-7-baii" tabindex="-1" aria-hidden="true" data-kt-initialized="1">-->
                                                
                        <!--                        <option value="Show All" selected="" data-select2-id="select2-data-9-6azc">Show All</option>-->
                        <!--                        <?php  if(isset($region)) { foreach ($region as $zone): ?>-->
                        <!--                        <option value="<?= $zone['region_name'] ?>" data-select2-id="select2-data-<?= $zone['region_name'] ?>"><?= $zone['region_name'] ?></option>-->
                        <!--                       <?php endforeach; } ?>-->
                                                
                        <!--                    </select>-->
                                            
                        <!--                </div>-->
                                        
                        
                        <!--            </div>-->

                        <!--        </div>-->
                    <!--begin::Table-->
                    <div id="kt_customer_details_invoices_table_3_wrapper" class="dt-container dt-bootstrap5 dt-empty-footer"><div id="" class="table-responsive">
                    <table id="kt_customer_details_invoices_table_2" class="table align-middle table-row-dashed fs-6 fw-bold gy-5 dataTable" style="width: 100%;"><colgroup><col data-dt-column="0" style="width: 0px;"><col data-dt-column="1" style="width: 0px;"><col data-dt-column="2" style="width: 0px;"><col data-dt-column="3" style="width: 0px;"><col data-dt-column="4" style="width: 0px;"></colgroup>
                        <thead class="border-bottom border-gray-200 fs-7 text-uppercase fw-bold">
                            <tr class="text-start text-muted gs-0">
                                    <th class="min-w-100px dt-orderable-asc dt-orderable-desc" data-dt-column="0" aria-label="Audit No: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Audit No</span><span class="dt-column-order"></span></th>
                                    <th class="min-w-100px dt-type-numeric dt-orderable-asc dt-orderable-desc" data-dt-column="2" aria-label="Name: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Name</span><span class="dt-column-order"></span></th>
                                    <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="3" aria-label="Category: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Category</span><span class="dt-column-order"></span></th>
                                    <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="3" aria-label="Client Leased: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Client Leased Openpoint</span><span class="dt-column-order"></span></th>
                                    <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="3" aria-label="Inplant : Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Inplant Openpoint</span><span class="dt-column-order"></span></th>
                                    <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="3" aria-label="FM leased: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">FM leased Openpoint</span><span class="dt-column-order"></span></th>
                                </tr>
                        </thead>
                        <tbody class="fs-6 fw-semibold text-gray-600">
                                <?php //print_r($aging_HSE_score); ?>
                                <?php  if(isset($aging_HSE_score)) { foreach ($aging_HSE_score as $row): ?>
                                    <tr>
                                        <td data-order="Invalid date"><a href="#" class="text-gray-600 text-hover-primary"><?= $row['audit_no']; ?></a></td>
                                        <td class="text-danger dt-type-numeric"><?=  $row['audit_name']; ?></td>
                                        <td><?= $row['question_name']; ?></span></td>
                                       <td class="text-danger dt-type-numeric"><?=  $row['total_client_leased_no']; ?></td>
                                       <td class="text-danger dt-type-numeric"><?=  $row['total_inplant_no']; ?></td>
                                       <td class="text-danger dt-type-numeric"><?=  $row['total_fm_leased_no']; ?></td>
                                    </tr>
                                <?php endforeach; } ?>
                                
                                </tbody>
                    </table>
                    </div><div id="" class="row"><div id="" class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start dt-toolbar"></div><div id="" class="col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end"><div class="dt-paging paging_simple_numbers"><nav aria-label="pagination"><ul class="pagination"><li class="dt-paging-button page-item disabled"><button class="page-link previous" role="link" type="button" aria-controls="kt_customer_details_invoices_table_3" aria-disabled="true" aria-label="Previous" data-dt-idx="previous" tabindex="-1"><i class="previous"></i></button></li><li class="dt-paging-button page-item active"><button class="page-link" role="link" type="button" aria-controls="kt_customer_details_invoices_table_3" aria-current="page" data-dt-idx="0">1</button></li><li class="dt-paging-button page-item"><button class="page-link next" role="link" type="button" aria-controls="kt_customer_details_invoices_table_3" aria-label="Next" data-dt-idx="next"><i class="next"></i></button></li></ul></nav></div></div></div></div>
                    <!--end::Table-->
                </div>
                <!--end::Tab panel-->
              
                    </div>
        
    </div>
    
</div>

    <div class="card pt-2 mb-6 mb-xl-9">
    
    <div class="card-header border-0">
        
        <div class="card-title">
            <h2>Upcoming Audit</h2>
        </div>
        

        <!--begin::Toolbar-->
        <div class="card-toolbar m-0">
            <!--begin::Tab nav-->
            <ul class="nav nav-stretch fs-5 fw-semibold nav-line-tabs nav-line-tabs-2x border-transparent" role="tablist">
                <li class="nav-item" role="presentation">
                    <a id="kt_normal_tab" class="nav-link text-active-primary active" data-bs-toggle="tab" role="tab" href="#kt_normal_upcoming_audit" aria-selected="true">
                        Normal
                    </a>
                </li>

                <li class="nav-item" role="presentation">
                    <a id="kt_OE_tab" class="nav-link text-active-primary ms-3" data-bs-toggle="tab" role="tab" href="#kt_OE_upcoming_audit" aria-selected="false" tabindex="-1">
                        OE
                    </a>
                </li>

                <li class="nav-item" role="presentation">
                    <a id="kt_HSE_tab" class="nav-link text-active-primary ms-3" data-bs-toggle="tab" role="tab" href="#kt_HSE_upcoming_audit" aria-selected="false" tabindex="-1">
                        HSE
                    </a>
                </li>

            </ul>
            <!--end::Tab nav-->
        </div>
        <!--end::Toolbar-->
    </div>
    

    
    <div class="card-body pt-0">
        <!--begin::Tab Content-->
        <div id="kt_referred_users_tab_content" class="tab-content">
                                            <!--begin::Tab panel-->
                <div id="kt_normal_upcoming_audit" class="py-0 tab-pane fade active show" role="tabpanel" aria-labelledby="kt_normal_tab">
                    <!--begin::Table-->
                    
                    <div id="kt_customer_details_invoices_table_1_wrapper" class="dt-container dt-bootstrap5 dt-empty-footer">
                        <div id="" class="table-responsive">
                        
                        <table id="kt_customer_details_invoices_table_2" class="table align-middle table-row-dashed fs-6 fw-bold gy-5 dataTable" style="width: 100%;"><colgroup><col data-dt-column="0" style="width: 0px;"><col data-dt-column="1" style="width: 0px;"><col data-dt-column="2" style="width: 0px;"><col data-dt-column="3" style="width: 0px;"><col data-dt-column="4" style="width: 0px;"></colgroup>
                        <thead class="border-bottom border-gray-200 fs-7 text-uppercase fw-bold">
                            <tr class="text-start text-muted gs-0">
                                    <th class="min-w-100px dt-orderable-asc dt-orderable-desc" data-dt-column="0" rowspan="1" colspan="1" aria-label="Order ID: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Audit No</span><span class="dt-column-order"></span></th>
                                    <th class="min-w-100px dt-type-numeric dt-orderable-asc dt-orderable-desc" data-dt-column="1" rowspan="1" colspan="1" aria-label="Amount: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Name</span><span class="dt-column-order"></span></th>
                                    <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="3" rowspan="1" colspan="1" aria-label="Date: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Audit Date</span><span class="dt-column-order"></span></th>
                                </tr>
                        </thead>
                        <tbody class="fs-6 fw-semibold text-gray-600">
                                <?php if(isset($Normal_audit)) { foreach ($Normal_audit as $row): ?>
                                    <tr>
                                        <td data-order="Invalid date"><a href="#" class="text-gray-600 text-hover-primary"><?= $row['audit_no']; ?></a></td>
                                        <td class="text-danger dt-type-numeric"><?= $row['audit_name']; ?></td>
                                        <td><?= $row['audit_date']; ?></td>
                                    </tr>
                                <?php endforeach; } ?>
                                </tbody>
                    </table>
                    </div>
                    <!--end::Table-->
                </div>
                <!--end::Tab panel-->
                </div>                          
                <!--begin::Tab panel-->
                
                <div id="kt_OE_upcoming_audit" class="py-0 tab-pane fade" role="tabpanel" aria-labelledby="kt_OE_tab">
                             
                    <!--begin::Table-->
                    <div id="kt_customer_details_invoices_table_2_wrapper" class="dt-container dt-bootstrap5 dt-empty-footer">
                        <div id="" class="table-responsive">
                                <table id="kt_customer_details_invoices_table_2" class="table align-middle table-row-dashed fs-6 fw-bold gy-5 dataTable" style="width: 100%;"><colgroup><col data-dt-column="0" style="width: 0px;"><col data-dt-column="1" style="width: 0px;"><col data-dt-column="2" style="width: 0px;"><col data-dt-column="3" style="width: 0px;"><col data-dt-column="4" style="width: 0px;"></colgroup>
                                <thead class="border-bottom border-gray-200 fs-7 text-uppercase fw-bold">
                                    <tr class="text-start text-muted gs-0">
                                            <th class="min-w-100px dt-orderable-asc dt-orderable-desc" data-dt-column="0" rowspan="1" colspan="1" aria-label="Order ID: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Audit No</span><span class="dt-column-order"></span></th>
                                            <th class="min-w-100px dt-type-numeric dt-orderable-asc dt-orderable-desc" data-dt-column="1" rowspan="1" colspan="1" aria-label="Amount: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Name</span><span class="dt-column-order"></span></th>
                                            <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="3" rowspan="1" colspan="1" aria-label="Date: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Audit Date</span><span class="dt-column-order"></span></th>
                                        </tr>
                                </thead>
                                <tbody class="fs-6 fw-semibold text-gray-600">
                                        
                                        <?php if(isset($OE_audit)) { foreach ($OE_audit as $row): ?>
                                    <tr>
                                        <td data-order="Invalid date"><a href="#" class="text-gray-600 text-hover-primary"><?= $row['audit_no']; ?></a></td>
                                        <td class="text-danger dt-type-numeric"><?= $row['audit_name']; ?></td>
                                         <td><?= $row['audit_date']; ?></td>
                                    </tr>
                                <?php endforeach; } ?>
                                        
                                        </tbody>
                            </table>
                    </div>
                    </div>
                    <!--end::Table-->
                </div>
                <!--end::Tab panel-->
                                            <!--begin::Tab panel-->
                <div id="kt_HSE_upcoming_audit" class="py-0 tab-pane fade" role="tabpanel" aria-labelledby="kt_HSE_tab">
                        
                    <!--begin::Table-->
                    <div id="kt_customer_details_invoices_table_3_wrapper" class="dt-container dt-bootstrap5 dt-empty-footer">
                        <div id="" class="table-responsive">
                    <table id="kt_customer_details_invoices_table_2" class="table align-middle table-row-dashed fs-6 fw-bold gy-5 dataTable" style="width: 100%;"><colgroup><col data-dt-column="0" style="width: 0px;"><col data-dt-column="1" style="width: 0px;"><col data-dt-column="2" style="width: 0px;"><col data-dt-column="3" style="width: 0px;"><col data-dt-column="4" style="width: 0px;"></colgroup>
                        <thead class="border-bottom border-gray-200 fs-7 text-uppercase fw-bold">
                            <tr class="text-start text-muted gs-0">
                                    <th class="min-w-100px dt-orderable-asc dt-orderable-desc" data-dt-column="0" rowspan="1" colspan="1" aria-label="Order ID: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Audit No</span><span class="dt-column-order"></span></th>
                                    <th class="min-w-100px dt-type-numeric dt-orderable-asc dt-orderable-desc" data-dt-column="1" rowspan="1" colspan="1" aria-label="Amount: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Name</span><span class="dt-column-order"></span></th>
                                    <th class="min-w-125px dt-orderable-asc dt-orderable-desc" data-dt-column="3" rowspan="1" colspan="1" aria-label="Date: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Audit Date</span><span class="dt-column-order"></span></th>
                                </tr>
                        </thead>
                        <tbody class="fs-6 fw-semibold text-gray-600">
                                
                                <?php if(isset($HSE_audit)) { foreach ($HSE_audit as $row): ?>
                                    <tr>
                                        <td data-order="Invalid date"><a href="#" class="text-gray-600 text-hover-primary"><?= $row['audit_no']; ?></a></td>
                                        <td class="text-danger dt-type-numeric"><?= $row['audit_name']; ?></td>
                                        <td><?= $row['audit_date']; ?></td>
                                    </tr>
                                <?php endforeach; } ?>
                                
                                </tbody>
                    </table>
                    </div></div>
                    <!--end::Table-->
                </div>
                <!--end::Tab panel-->
              
                    </div>
        
    </div>
    
</div>



    <!--<div class="row g-5 g-xl-10">-->
    <!--    <div class="col-xl-12">-->
    <!--            <div class="card h-md-100">-->
    <!--                <div class="card-header position-relative py-0 border-bottom-1">-->
    <!--                    <h3 class="card-title text-gray-800 fw-bold">Audit Percentage</h3>-->
    <!--                    <ul class="nav nav-stretch nav-pills nav-pills-custom d-flex mt-4" role="tablist">-->
    <!--                        <li class="nav-item p-0 ms-0" role="presentation">-->
    <!--                            <a class="nav-link btn btn-color-gray-500 flex-center px-3 active" data-kt-timeline-widget-4="tab" data-bs-toggle="tab" href="#kt_timeline_widget_4_tab_day" aria-selected="true" role="tab"> -->
                                    
    <!--                                <span class="nav-text fw-semibold fs-4 mb-3">Normal</span> -->
    <!--                                <span class="bullet-custom position-absolute z-index-2 w-100 h-1px top-100 bottom-n100 bg-primary rounded"></span>-->
                                    
    <!--                            </a>-->
    <!--                        </li>-->
                            
    <!--                        <li class="nav-item p-0 ms-0" role="presentation">-->
    <!--                            <a class="nav-link btn btn-color-gray-500 flex-center px-3" data-kt-timeline-widget-4="tab" data-bs-toggle="tab" href="#kt_timeline_widget_4_tab_week" aria-selected="false" role="tab" tabindex="-1">-->
    <!--                            <span class="nav-text fw-semibold fs-4 mb-3">OE</span> -->
    <!--                            <span class="bullet-custom position-absolute z-index-2 w-100 h-1px top-100 bottom-n100 bg-primary rounded"></span>-->
                                    
    <!--                            </a>-->
    <!--                        </li>-->
                            
                
                            
    <!--                        <li class="nav-item p-0 ms-0" role="presentation">-->
    <!--                            <a class="nav-link btn btn-color-gray-500 flex-center px-3" data-kt-timeline-widget-4="tab" data-bs-toggle="tab" href="#kt_timeline_widget_4_tab_month" aria-selected="false" role="tab" tabindex="-1">-->
    <!--                               <span class="nav-text fw-semibold fs-4 mb-3">HSE</span> -->
    <!--                                <span class="bullet-custom position-absolute z-index-2 w-100 h-1px top-100 bottom-n100 bg-primary rounded"></span>-->
    <!--                            </a>-->
    <!--                        </li>-->
    <!--                    </ul>-->
    <!--                </div>-->
    <!--                    <div class="card-body pt-0">-->
    <!--                        <div class="card-toolbar" data-select2-id="select2-data-124-5t5f">-->

    <!--                                <div class="d-flex flex-stack flex-wrap gap-4" data-select2-id="select2-data-123-h8fu">-->

    <!--                                    <div class="d-flex align-items-center fw-bold" data-select2-id="select2-data-122-xtxd">-->
                                           
    <!--                                        <div class="text-gray-500 fs-7 me-2">Country</div>-->
    <!--                                        <select class="form-select form-select-transparent text-graY-800 fs-base lh-1 fw-bold py-0 ps-3 w-auto select2-hidden-accessible" data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px" data-placeholder="Select an option" data-select2-id="select2-data-7-baii" tabindex="-1" aria-hidden="true" data-kt-initialized="1">-->
    <!--                                            <option value="Show All" selected="" data-select2-id="select2-data-9-6azc">Show All</option>-->
                                                <?php //if(isset($country)) { foreach ($country as $coun): ?>
                                               <!-- <option value="<?php //echo $coun['country_name'] ?>" data-select2-id="select2-data-<?php //echo $coun['country_name'] ?>"><?php //echo $coun['country_name'] ?></option>-->
                                              <?php // endforeach; } ?>
    <!--                                        </select>-->
    <!--                                    </div>-->
    <!--                                </div>-->
    <!--                            </div>-->
    <!--                        </div>-->
                    
    <!--                <div class="card-body pb-0">-->
    <!--                    <div class="tab-content">-->
    <!--                        <div class="tab-pane blockui active show" id="kt_timeline_widget_4_tab_day" role="tabpanel" aria-labelledby="day-tab" data-kt-timeline-widget-4-blockui="true" style="">-->
    <!--                           <table class="table table-bordered table-custom">-->
    <!--                                <thead>-->
    <!--                                    <tr class="header-row">-->
    <!--                                        <th>Region</th>-->
    <!--                                        <th>Client Name</th>-->
    <!--                                        <th>Percentage</th>-->
    <!--                                    </tr>-->
    <!--                                </thead>-->
    <!--                                <tbody>-->
                                       
                                        <?php //if(isset($total_score)) { foreach ($total_score as $row): ?>
                                            <!--<tr>-->
                                            <!--    <td><?php //echo esc($row['zone']); ?></td>-->
                                            <!--    <td><?php //echo esc($row['client_name']); ?></td>-->
                                                <?php 
                                                
                                                  // $yes_value = ($row['yes'] != 0) ? floor(100*$row['audit_count']/$row['yes']) . '%' : '0%';
                                             ?>
                                            <!--        <td><?php //echo $yes_value; ?></td>-->
                                            <!--</tr>-->
                                        <?php //endforeach; } ?>
    <!--                                </tbody>-->
    <!--                            </table>-->
    <!--                        </div>-->
    <!--                        <div class="tab-pane blockui" id="kt_timeline_widget_4_tab_week" role="tabpanel" aria-labelledby="week-tab" data-kt-timeline-widget-4-blockui="true" style="">-->
    <!--                            <table class="table table-bordered table-custom">-->
    <!--                                <thead>-->
    <!--                                    <tr class="header-row">-->
    <!--                                        <th>Region</th>-->
    <!--                                        <th>Client Name</th>-->
    <!--                                        <th>Percentage</th>-->
    <!--                                    </tr>-->
    <!--                                </thead>-->
    <!--                                <tbody>-->
    <!--                                    <?php //if(isset($total_score)) { foreach ($total_score as $row): ?>-->
    <!--                                        <tr>-->
    <!--                                            <td><?php //echo $row['zone']; ?></td>-->
    <!--                                            <td><?php //echo $row['client_name']; ?></td>-->
    <!--                                            <?php 
                                                
                                                   // $yes_value = ($row['yes'] != 0) ? floor(100*$row['audit_count']/$row['yes']) . '%' : '0%';-->
                                              ?>
                                                  <td><?php //echo $yes_value; ?></td>-->
    <!--                                        </tr>-->
                                       <?php //endforeach; } ?>
    <!--                                </tbody>-->
    <!--                            </table>-->
    <!--                        </div>-->
    <!--                        <div class="tab-pane blockui" id="kt_timeline_widget_4_tab_month" role="tabpanel" aria-labelledby="month-tab" data-kt-timeline-widget-4-blockui="true" style="">-->
    <!--                           <table class="table table-bordered table-custom">-->
    <!--                                <thead>-->
    <!--                                    <tr class="header-row">-->
    <!--                                        <th>Region</th>-->
    <!--                                        <th>Client Name</th>-->
    <!--                                        <th>Percentage</th>-->
    <!--                                    </tr>-->
    <!--                                </thead>-->
    <!--                                <tbody>-->
                                        <?php //if(isset($total_score)) { foreach ($total_score as $row): ?>
    <!--                                        <tr>-->
    <!--                                            <td><?php //echo esc($row['zone']); ?></td>-->
    <!--                                            <td><?php //echo esc($row['client_name']); ?></td>-->
                                              <?php 
                                                 // $yes_value = ($row['yes'] != 0) ? floor(100*$row['audit_count']/$row['yes']) . '%' : '0%';-->
                                              ?>
    <!--                                                <td><?php //echo $yes_value; ?></td>-->
    <!--                                        </tr>-->
                                       <?php //endforeach; } ?>
    <!--                                </tbody>-->
    <!--                            </table>-->
    <!--                        </div>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--    </div>-->
    <!--</div>-->

<?php $this->endSection(); ?>
<?php $this->section("javascript_section"); ?>


 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.52.0/apexcharts.min.css" integrity="sha512-w3pXofOHrtYzBYpJwC6TzPH6SxD6HLAbT/rffdkA759nCQvYi5AHy5trNWFboZnj4xtdyK0AFMBtck9eTmwybg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.52.0/apexcharts.min.js" integrity="sha512-piY4QAXPoG2xLdUZZbcc5klXzMxckrQKY9A2o6nKDRt9inolvvLbvGPC+z9IZ29b28UJlD05B7CjxxPaxh4bjQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<?php $this->endSection(); ?>