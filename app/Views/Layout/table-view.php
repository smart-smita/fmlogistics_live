<?php $example2 = (isset($example2))?$example2:"data-table-".rand();
$dispay_content_keys_array_for_ajax_string="";

?>

<div class="card mb-5 mb-xl-8">
									<!--begin::Header-->
									<div class="card-header border-0 pt-5">
										<h3 class="card-title align-items-start flex-column">
											<span class="card-label fw-bolder fs-3 mb-1"><?=(isset($title))?$title:"Title Is missing";?></span>
											<!--<span class="text-muted mt-1 fw-bold fs-7">Over 500 members</span>-->
										</h3>
									<div class="d-flex align-items-center gap-2">
									<?php if (isset($download_excel)) echo str_replace('card-toolbar', '', $download_excel); ?>

                                        <?php 
                                        $show_legacy_export = isset($export_csv) && $export_csv;
                                        $show_new_export = isset($enable_export) ? $enable_export : !$show_legacy_export;
                                        if($show_new_export && (!function_exists('canDownloadReports') || canDownloadReports())) { ?>
										<div data-bs-toggle="tooltip" data-bs-placement="top" title="Export Data to CSV">
											<button type="button" id="custom_export_csv_btn" class="btn btn-sm btn-info text-white" onclick="$('.buttons-csv').click();">
												<i class="fas fa-file-csv"></i> Export CSV
											</button>
										</div>
                                        <?php } ?>

										<?php 
										// ACL: Check for write access
										helper('gemba_acl');
										if(isset($button_id) && !isset($hide_add_button) && gemba_can_write()){?>
										<div data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" title="" data-bs-original-title="Click to <?=(isset($button_name))?$button_name:"";?>">
											<button class="btn btn-sm btn-light btn-active-primary" data-bs-toggle="modal" data-bs-target="#<?=$button_id?>" onclick="$('#<?=$button_id?>').find('form')[0].reset();$('#<?=$button_id?>').find('#ajax_click').attr('data-ajax-url',$('#<?=$button_id?>').find('#ajax_click').attr('data-ajax-add-url'));$(this).attr('data-kt-indicator', 'on');$(this).attr('disabled', true);setTimeout(function (obj) {obj.attr('data-kt-indicator', 'off');obj.attr('disabled', false);},500,$(this));">
											<!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
											<span class="indicator-label svg-icon svg-icon-3">
												<?=(isset($button_name))?$button_name:"";?>
												<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
													<rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1" transform="rotate(-90 11.364 20.364)" fill="black"></rect>
													<rect x="4.36396" y="11.364" width="16" height="2" rx="1" fill="black"></rect>
												</svg>
											</span>
                                            <span class="indicator-progress">
                                                Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											<!--end::Svg Icon--></button>
											
										</div>
										
										<?php }?>	
                                        
                                        <?php if(isset($export_csv) && $export_csv && (!function_exists('canDownloadReports') || canDownloadReports())) { ?>
										<div data-bs-toggle="tooltip" data-bs-placement="top" title="Export Data">
											<button type="button" class="btn btn-sm btn-success text-white" onclick="$('.buttons-csv').click();">
												<i class="fa fa-file-excel"></i> Excel / CSV
											</button>
										</div>
                                        <?php } ?>
											
										<?php if (isset($export_button)) echo str_replace('card-toolbar', '', $export_button); ?>
									</div>
										<?php 
										// ACL: Check for write access for perform audit button
										if (isset($perform_audit_button) && gemba_can_write()) echo $perform_audit_button; 
										?>
									</div>
									<!--end::Header-->
									<!--begin::Body-->
								
									<div class="card-body py-3">
									    		<?php if(isset($top_dynamic_content)) echo $top_dynamic_content;?>

										<!--begin::Table container-->

				<?php if(isset($display_contents) ){ ?>
				<div class="table-responsive">
				<!--begin::Table-->
							

				<table id="<?php echo $example2; ?>" class="table table-bordered table-hover" style="width:100%;">

					<thead>
						<tr>
								<?php 
    								$i = 0;
                                    $len = count($display_contents);
                                    foreach ($display_contents as $dkey=>$row) {
								    $dispay_content_keys_array_for_ajax_string .= ' { "data": "'.$dkey.'" }';
    								    if ($i != $len - 1) {
    								        $dispay_content_keys_array_for_ajax_string.=",";
                                        }
                                    $i++;
								?>
									<th class="col-field-<?php echo $dkey; ?> <?php echo (in_array($dkey, ['observation_point', 'action_recommendation', 'nc_remark', 'qhse_remarks', 'rejection_reason', 'risks_details'])) ? 'long-text-column' : ''; ?> <?php echo ($dkey === 'action') ? 'no-export' : ''; ?>"><?php echo $row ?></th>
								<?php } ?>
							</tr>
					</thead>
					<tbody>
    						<?php if(!isset($is_server_side) || $is_server_side != "true"){ 
                                if(isset($table_data)) foreach ($table_data as $row){ ?>
    						<tr
    									<?php if (isset($row['tr_class']))echo "class=".$row['tr_class']; ?>>
							<?php foreach ($display_contents as $key=>$drow) {?>
									<td> <?php echo isset($row[$key]) ? $row[$key] : ''; ?></td>
							<?php } ?>
    						</tr>
    						<?php } } ?>
					</tbody>
					
					<!--<tfoot>-->
					<!--	<tr>-->
					<!--			<?php //foreach ($display_contents as $row) {?>-->
					<!--				<th><?php //echo $row ?></th>-->
					<!--			<?php //} ?>-->
					<!--		</tr>-->
					<!--</tfoot>-->
				</table>
				</div>	
					<?php } ?>
		<!--end::Table-->
		<?php if(isset($other_view_content)) echo $other_view_content;?>
		
		
				</div>
				<!--end::Table container-->
			</div>
			<!--begin::Body-->

<?php $this->section("javascript_section") ?>
<link href="<?=base_url();?>/assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>


<script src="<?=base_url();?>/assets/plugins/custom/datatables/datatables.bundle.js"></script>

<!--<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">-->
<!--<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>-->
<!--	<link href="<?php echo base_url("") ?>/assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>-->
<!--<script src="<?php echo base_url("") ?>/assets/plugins/custom/datatables/datatables.bundle.js"></script>-->
<script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript">
function ExportToExcel(type, fn, dl) {
       var elt = document.getElementById('<?php echo $example2; ?>');
       // Clone the table to preserve original
       var clonedTable = elt.cloneNode(true);
       
       // Format decimal scores as percentages
       var rows = clonedTable.querySelectorAll('tbody tr');
       rows.forEach(function(row) {
           var cells = row.querySelectorAll('td');
           
           // Process each cell to convert decimal scores to percentages
           for (var i = 0; i < cells.length; i++) {
               var val = cells[i].textContent.trim();
               
               // Skip empty cells, non-numeric values, and cells that already have %
               if (!val || val.endsWith('%') || val === '0' || val === '-' || isNaN(parseFloat(val))) continue;
               
               var numVal = parseFloat(val);
               
               // Convert decimal values to percentages
               if (!isNaN(numVal) && numVal >= 0) {
                   // For decimal values between 0 and 1 (like 0.13, 0.07), convert to percentage
                   if (numVal > 0 && numVal < 1) {
                       cells[i].textContent = Math.round(numVal * 100) + '%';
                   }
                   // For values that are exactly 0 or 1, handle appropriately
                   else if (numVal === 0) {
                       cells[i].textContent = '0%';
                   }
                   else if (numVal === 1) {
                       cells[i].textContent = '100%';
                   }
                   // For whole numbers that appear to be scores (0-100), add %
                   else if (numVal >= 0 && numVal <= 100 && Number.isInteger(numVal)) {
                       // Check if this column looks like a score column
                       var headerCell = clonedTable.querySelector('thead tr th:nth-child(' + (i + 1) + ')');
                       var headerText = headerCell ? headerCell.textContent.toLowerCase() : '';
                       
                       if (headerText.includes('score') || headerText.includes('avg') || 
                           headerText.includes('percentage') || headerText.includes('%')) {
                           cells[i].textContent = numVal + '%';
                       }
                   }
               }
           }
       });
       
       // Also format header row to indicate percentages
       var headerRows = clonedTable.querySelectorAll('thead tr');
       headerRows.forEach(function(row) {
           var cells = row.querySelectorAll('th');
           for (var i = 0; i < cells.length; i++) {
               var val = cells[i].textContent.trim();
               // Add % to score-related headers that don't already have it
               if ((val.toLowerCase().includes('score') || 
                    val.toLowerCase().includes('avg') || 
                    val.toLowerCase().includes('percentage')) && 
                   !val.includes('%') && !val.includes('(%)')) {
                   cells[i].textContent = val + ' (%)';
               }
           }
       });
       
       var wb = XLSX.utils.table_to_book(clonedTable, { sheet: "sheet1" });
       return dl ?
         XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' }):
         XLSX.writeFile(wb, fn || ('Audit_Export_<?php echo date("Ymdhis")?>' + '.' + (type || 'xlsx')));
    }
    
    function ExportToCSV(fn) {
       var elt = document.getElementById('<?php echo $example2; ?>');
       var clonedTable = elt.cloneNode(true);
       
       var rows = clonedTable.querySelectorAll('tbody tr');
       rows.forEach(function(row) {
           var cells = row.querySelectorAll('td');
           for (var i = 0; i < cells.length; i++) {
               var val = cells[i].textContent.trim();
               if (!val || val.endsWith('%') || val === '0' || val === '-' || isNaN(parseFloat(val))) continue;
               var numVal = parseFloat(val);
               if (!isNaN(numVal) && numVal >= 0) {
                   if (numVal > 0 && numVal < 1) {
                       cells[i].textContent = Math.round(numVal * 100) + '%';
                   }
                   else if (numVal === 0) {
                       cells[i].textContent = '0%';
                   }
                   else if (numVal === 1) {
                       cells[i].textContent = '100%';
                   }
                   else if (numVal >= 0 && numVal <= 100 && Number.isInteger(numVal)) {
                       var headerCell = clonedTable.querySelector('thead tr th:nth-child(' + (i + 1) + ')');
                       var headerText = headerCell ? headerCell.textContent.toLowerCase() : '';
                       if (headerText.includes('score') || headerText.includes('avg') || 
                           headerText.includes('percentage') || headerText.includes('%')) {
                           cells[i].textContent = numVal + '%';
                       }
                   }
               }
           }
       });
       
       var headerRows = clonedTable.querySelectorAll('thead tr');
       headerRows.forEach(function(row) {
           var cells = row.querySelectorAll('th');
           for (var i = 0; i < cells.length; i++) {
               var val = cells[i].textContent.trim();
               if ((val.toLowerCase().includes('score') || 
                    val.toLowerCase().includes('avg') || 
                    val.toLowerCase().includes('percentage')) && 
                   !val.includes('%') && !val.includes('(%)')) {
                   cells[i].textContent = val + ' (%)';
               }
           }
       });

       var ws = XLSX.utils.table_to_sheet(clonedTable, { raw: true });
       Object.keys(ws).forEach(function (addr) {
           if (addr[0] === '!') return;
           var cell = ws[addr];
           if (cell && typeof cell.w === 'string' && cell.w.indexOf('%') !== -1) {
               cell.t = 's';
               cell.v = cell.w;
               delete cell.z;
           }
       });

       var csv = XLSX.utils.sheet_to_csv(ws, { blankrows: false });
       var blob = new Blob(["\uFEFF" + csv], { type: "text/csv;charset=utf-8;" });
       var url = URL.createObjectURL(blob);
       var link = document.createElement("a");
       link.setAttribute("href", url);
       link.setAttribute("download", fn || ('Audit_Export_<?php echo date("Ymdhis")?>.csv'));
       link.style.visibility = 'hidden';
       document.body.appendChild(link);
       link.click();
       document.body.removeChild(link);
    }
    
    
<?php if (isset($script)) echo $script;?>
(function(h,g,b){function e(d,c){d.unhighlight();c.rows({filter:"applied"}).data().length&&d.highlight(b.trim(c.search()).split(/\s+/))}b(g).on("init.dt.dth",function(d,c){if("dt"===d.namespace){var a=new b.fn.dataTable.Api(c),f=b(a.table().body());if(b(a.table().node()).hasClass("searchHighlight")||c.oInit.searchHighlight||b.fn.dataTable.defaults.searchHighlight)a.on("draw.dt.dth column-visibility.dt.dth column-reorder.dt.dth",function(){e(f,a)}).on("destroy",function(){a.off("draw.dt.dth column-visibility.dt.dth column-reorder.dt.dth")}),
a.search()&&e(f,a)}})})(window,document,jQuery);
jQuery.extend({
    highlight: function (node, re, nodeName, className) {
        if (node.nodeType === 3) {
            var match = node.data.match(re);
            if (match) {
                var highlight = document.createElement(nodeName || 'span');
                highlight.className = className || 'highlight';
                var wordNode = node.splitText(match.index);
                wordNode.splitText(match[0].length);
                var wordClone = wordNode.cloneNode(true);
                highlight.appendChild(wordClone);
                wordNode.parentNode.replaceChild(highlight, wordNode);
                return 1; //skip added node in parent
            }
        } else if ((node.nodeType === 1 && node.childNodes) && // only element nodes that have children
                !/(script|style)/i.test(node.tagName) && // ignore script and style nodes
                !(node.tagName === nodeName.toUpperCase() && node.className === className)) { // skip if already highlighted
            for (var i = 0; i < node.childNodes.length; i++) {
                i += jQuery.highlight(node.childNodes[i], re, nodeName, className);
            }
        }
        return 0;
    }
});

jQuery.fn.unhighlight = function (options) {
    var settings = { className: 'highlight', element: 'span' };
    jQuery.extend(settings, options);

    return this.find(settings.element + "." + settings.className).each(function () {
        var parent = this.parentNode;
        parent.replaceChild(this.firstChild, this);
        parent.normalize();
    }).end();
};

jQuery.fn.highlight = function (words, options) {
    var settings = { className: 'highlight', element: 'span', caseSensitive: false, wordsOnly: false };
    jQuery.extend(settings, options);
    
    if (words.constructor === String) {
        words = [words];
    }
    words = jQuery.grep(words, function(word, i){
      return word != '';
    });
    words = jQuery.map(words, function(word, i) {
      return word.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
    });
    if (words.length == 0) { return this; };

    var flag = settings.caseSensitive ? "" : "i";
    var pattern = "(" + words.join("|") + ")";
    if (settings.wordsOnly) {
        pattern = "\\b" + pattern + "\\b";
    }
    var re = new RegExp(pattern, flag);
    
    return this.each(function () {
        jQuery.highlight(this, re, settings.element, settings.className);
    });
};

function redirectToMasterQuestion() {
    window.location.href = "https://web.unitglo.com/Alert/Demo/Masters/Audit_question_master";
}

function redirectToAuditView() {
    window.location.href = "<?= base_url('/Masters/Hse_audit/audit_view') ?>";
}
</script>


<script type="text/javascript">

$(document).on('click', function (e) {
    $('[data-toggle="popover"],[data-original-title]').each(function () {
        //the 'is' for buttons that trigger popups
        //the 'has' for icons within a button that triggers a popup
        if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {                
            (($(this).popover('hide').data('bs.popover')||{}).inState||{}).click = false  // fix for BS 3.3.6
        }

    });
});


var table = $('#<?php echo $example2; ?>').DataTable({
    <?php if(isset($is_server_side) && $is_server_side == "true"){ ?>
    "serverSide": true,
    "processing": true,
    <?php } ?>
    <?php if(isset($ajax_url_for_data)){ ?>
    <?php if(isset($ajax_type) && strtoupper($ajax_type) === 'POST'){ ?>
    "ajax": {
        "url": '<?=$ajax_url_for_data?>',
        "type": "POST"
        <?php if(isset($ajax_data)){ echo ',"data": function(d) { var customData = ' . json_encode($ajax_data) . '; Object.assign(d, customData); }'; } ?>
    },
    <?php } else { ?>
    "ajax": '<?=$ajax_url_for_data?>',
    <?php } ?>
    "deferRender": true,
    <?php if($dispay_content_keys_array_for_ajax_string!=""){ ?>
    "columns": [
        <?=$dispay_content_keys_array_for_ajax_string?>
    ],
    <?php } } ?>

    "createdRow": function( row, data, dataIndex ) {
        if ( data && data.hasOwnProperty('tr_class')) {
            $(row).addClass(data['tr_class']);
        }
    },

    "paging": true,
    "searching": true,
    "lengthMenu": [[15,25,50,-1],[15,25,50,"All"]],
    "ordering": true,
    <?php if(isset($column_defs)) echo '"columnDefs": '.$column_defs.','; ?>
    <?php if(isset($order)!=""){ ?>
    "order": [<?=$order?>],
    <?php } ?>

    // important scrolling setup
    "scrollY": "400px",
    "scrollX": true,
    "scrollCollapse": true,
    "responsive": false,
    "autoWidth": false,
    "fixedHeader": false,

    "searchHighlight": true,
    "info": true,
    "autoWidth": false,
    <?php if($show_new_export || $show_legacy_export) { ?>
    "dom": "<'row mb-3'<'col-sm-6'l><'col-sm-6'f>>" + "<'d-none'B>" + "<'table-wrapper'tr>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    "buttons": [
        {
            extend: 'csvHtml5',
            className: 'btn btn-sm btn-info text-white',
            text: '<i class="fa fa-file-csv"></i> Export CSV',
            filename: function() {
                var d = new Date();
                var ds = d.getFullYear() + ('0'+(d.getMonth()+1)).slice(-2) + ('0'+d.getDate()).slice(-2) + '_' + ('0'+d.getHours()).slice(-2) + ('0'+d.getMinutes()).slice(-2) + ('0'+d.getSeconds()).slice(-2);
                var moduleName = document.title.replace(/[^a-zA-Z0-9]/g, '_') || 'Export';
                return moduleName + '_' + ds;
            },
            exportOptions: {
                modifier: { search: 'applied' },
                columns: ':not(.no-export)'
            },
            action: function (e, dt, button, config) {
                var self = this;
                var originalText = button.html();
                button.html('<i class="fa fa-spinner fa-spin"></i> Exporting...');
                button.addClass('disabled');

                if (dt.page.info().serverSide) {
                    var oldStart = dt.settings()[0]._iDisplayStart;
                    dt.one('preXhr', function (e, s, data) {
                        data.start = 0;
                        data.length = 2147483647; // Max length to fetch all
                        dt.one('preDraw', function (e, settings) {
                            if (button[0].className.indexOf('buttons-csv') >= 0) {
                                $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button, config);
                            }
                            dt.one('preXhr', function (e, s, data) {
                                settings._iDisplayStart = oldStart;
                                data.start = oldStart;
                            });
                            // Reload original page view
                            setTimeout(function() {
                                dt.ajax.reload(function() {
                                    button.html(originalText);
                                    button.removeClass('disabled');
                                }, false);
                            }, 0);
                            return false; // Prevent rendering of massive dataset
                        });
                    });
                    dt.ajax.reload();
                } else {
                    $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button, config);
                    button.html(originalText);
                    button.removeClass('disabled');
                }
            },
            init: function(api, node, config) {
                $(node).removeClass('btn-secondary');
            }
        }
    ],
    <?php } else { ?>
    "dom": "<'row mb-3'<'col-sm-6'l><'col-sm-6'f>>" + "<'table-wrapper'tr>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    <?php } ?>

    "initComplete": function(settings, json) {
        var api = this.api();
        
        // Auto-apply search from URL parameters (e.g. client)
        var urlParams = new URLSearchParams(window.location.search);
        var searchArr = [];
        if (urlParams.has('client') && urlParams.get('client').trim() !== '') {
            searchArr.push(urlParams.get('client').trim());
        }
        if (searchArr.length > 0) {
            // Wait slightly for DOM to settle, then search
            setTimeout(function() {
                api.search(searchArr.join(' ')).draw();
            }, 100);
        }

        // ensure header/body columns aligned
        setTimeout(function(){ api.columns.adjust(); }, 10);
    }
});

// sync adjustments on redraw/resize/xhr
table.on('draw', function() { table.columns.adjust(); });
table.on('xhr.dt', function () { setTimeout(function(){ table.columns.adjust(); }, 50); });
$(window).on('resize', function(){ setTimeout(function(){ table.columns.adjust().draw(); }, 100); });


	    function reload_data_table(){
	        $('#<?php echo $example2; ?>').DataTable().ajax.reload(null, false);
            $('.tooltip').remove();
	    }
</script>

<style>
table.dataTable span.highlight {
	background-color: #FFFF88;
	border-radius: 0.28571429rem;
	padding : 0 !important;
}

table.dataTable span.column_highlight {
	background-color: #ffcc99;
	border-radius: 0.28571429rem;
}

/* Base table styling for alignment */
table.dataTable {
    width: 100% !important;
    border-collapse: collapse !important;
}

table.dataTable th,
table.dataTable td {
    white-space: nowrap;
    vertical-align: middle;
    padding: 10px;
}

/* Long text columns wrapping */
table.dataTable th.long-text,
table.dataTable td.long-text {
    white-space: normal !important;
    word-break: break-word !important;
    min-width: 250px;
}

/* keep scroll head visible and aligned */
.dataTables_scrollHead {
    overflow: hidden !important;
}

/* header style */
.dataTables_scrollHead table thead th {
    background: #f5f8fa;
    white-space: nowrap;
}

</style>
<?php $this->endSection(); ?>