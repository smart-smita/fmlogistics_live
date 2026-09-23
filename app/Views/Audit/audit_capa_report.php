
<?php $this->extend("Layout/base_admin"); ?>
<?php 
					$this->section("breadcrumb_title_li");
?>
<style>
    label {
    display: inline-block;
    padding-left: 10px !important;
    padding-right: 10px !important;
}
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
}
</style>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?= current_url() ?>" class="text-muted text-hover-primary"><?=isset($title)?$title:"Client"?></a>
	</li>
<!--end::Item-->
<?php $this->endSection();?>	

<?php $this->section("main_body"); ?>

<?php

?>

<div class="card">
        <div class="card-body">
            <div class="col-md-12 table-responsive"  >
                <label class=" fs-5 fw-bold mb-2">Perform Audit By</label>

                       <select class="form-select form-select-solid" name="perform_audit_by" id="perform_audit_by" onchange="toggleAuditTable()">
                    <option value="">Please Select</option>
                    <option value="client_leased">Client Leased</option>
                    <option value="fm_leased">FM Leased</option>
                    <option value="office">Office</option>
                </select>
   
   
                    </div>
                    </div>
                </div><br>

<div class="card">
    <div class="card-body">
        <div class="col-md-12 table-responsive" id="auditTableContainer" style="display: none;">
            <table border="1" id="auditTable">
                <tr>
                    <th>ID</th>

                    <th class="client_leased_col">Client Leased</th>
                    <th class="client_leased_col">Findings</th>
                    <th class="client_leased_col">Risk</th>
                    <th class="client_leased_col">Actions</th>
                    <th class="client_leased_col">Action Category</th>
                    <th class="client_leased_col">UA-UC</th>
                    <th class="client_leased_col">Risk Severity</th>
                    <th class="client_leased_col">Risk Probability</th>
                    <th class="client_leased_col">Color Code</th>
                    <th class="client_leased_col">Cost Type</th>
                    <th class="client_leased_col">Combined Risk Rating</th>

                    <th class="fm_leased_col">FM Leased</th>
                    <th class="fm_leased_col">Findings</th>
                    <th class="fm_leased_col">Risk</th>
                    <th class="fm_leased_col">Actions</th>
                    <th class="fm_leased_col">Action Category</th>
                    <th class="fm_leased_col">UA-UC</th>
                    <th class="fm_leased_col">Risk Severity</th>
                    <th class="fm_leased_col">Risk Probability</th>
                    <th class="fm_leased_col">Color Code</th>
                    <th class="fm_leased_col">Cost Type</th>
                    <th class="fm_leased_col">Combined Risk Rating</th>

                    <th class="office_col">Office</th>
                    <th class="office_col">Findings</th>
                    <th class="office_col">Risk</th>
                    <th class="office_col">Actions</th>
                    <th class="office_col">Action Category</th>
                    <th class="office_col">UA-UC</th>
                    <th class="office_col">Risk Severity</th>
                    <th class="office_col">Risk Probability</th>
                    <th class="office_col">Color Code</th>
                    <th class="office_col">Cost Type</th>
                    <th class="office_col">Combined Risk Rating</th>
                </tr>
                
                <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?= $result['id'] ?></td>

                       
                        <td class="client_leased_col"><?= $result['client_leased_value'] ?></td>
                        <?php foreach ($result['client_leased'] as $value): ?>
                            <td class="client_leased_col"><?= $value ?></td>
                        <?php endforeach; ?>

                        <td class="fm_leased_col"><?= $result['fm_leased_value'] ?></td>
                        <?php foreach ($result['fm_leased'] as $value): ?>
                            <td class="fm_leased_col"><?= $value ?></td>
                        <?php endforeach; ?>

                        <td class="office_col"><?= $result['office_value'] ?? '' ?></td>
                        <?php foreach ($result['office'] as $value): ?>
                            <td class="office_col"><?= $value ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>


                       
                         
<?php 				$this->endSection();?>
	

<?php 					$this->section("javascript_section");?>
<script>

function toggleAuditTable() {
    // Show table container
    document.getElementById("auditTableContainer").style.display = "block";

    // Get the selected value
    const selectedValue = document.getElementById("perform_audit_by").value;

    // Hide all column types initially
    document.querySelectorAll('.client_leased_col').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.fm_leased_col').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.office_col').forEach(el => el.style.display = 'none');

    // Show columns based on selected value
    if (selectedValue === "client_leased") {
        document.querySelectorAll('.client_leased_col').forEach(el => el.style.display = 'table-cell');
    } else if (selectedValue === "fm_leased") {
        document.querySelectorAll('.fm_leased_col').forEach(el => el.style.display = 'table-cell');
    } else if (selectedValue === "office") {
        document.querySelectorAll('.office_col').forEach(el => el.style.display = 'table-cell');
    } else {
        // Hide table if no valid selection is made
        document.getElementById("auditTableContainer").style.display = "none";
    }
}


    function singleSelect(checkbox, groupName) {
        // Get all checkboxes with the same group name
        let checkboxes = document.querySelectorAll("input[type='checkbox'][onclick*='" + groupName + "']");
        
        // Uncheck all checkboxes in the group
        checkboxes.forEach(function(box) {
            if (box !== checkbox) box.checked = false;
        });
    }


var final_score = 0;

 


   function toggleCheckbox(selectedCheckbox, groupName) {
      // Get all checkboxes in the same group
      const checkboxes = document.querySelectorAll(`input[name="${groupName}"]`);
      
      // Uncheck all checkboxes except the selected one
      checkboxes.forEach((checkbox) => {
         if (checkbox !== selectedCheckbox) {
            checkbox.checked = false;
         }
      });
   }

(function() {
    // Set the prefix and starting number
    const prefix = 'AUD';
    let auditNumber = 1;

    // Check if a previous audit number exists in localStorage, and if so, increment it
    if(localStorage.getItem('auditNumber')) {
        auditNumber = parseInt(localStorage.getItem('auditNumber')) + 1;
    }

    // Generate the Audit No. with the prefix
    const auditNo = prefix + auditNumber;

    // Set the value to the input field with ID 'audit_no'
    document.getElementById('audit_no').value = auditNo;

    // Save the current audit number in localStorage for future increments
    localStorage.setItem('auditNumber', auditNumber);
})();

</script>          
<?php $this->endSection();?>	

