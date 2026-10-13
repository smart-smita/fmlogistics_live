<!DOCTYPE html>
<html lang="en">
<head>
    <base href="<?=base_url()?>">
    <title>Report Near Miss | Alert System</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="<?=base_url()?>/assets/media/app-logo-3.jpeg" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link href="<?=base_url()?>/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="<?=base_url()?>/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
</head>
<body id="kt_body" class="bg-body">
    <div class="d-flex flex-column flex-root">
        <div class="d-flex flex-column flex-lg-row flex-column-fluid">
            <!-- Sidebar / Branding -->
            <div class="d-flex flex-column flex-lg-row-auto w-xl-400px positon-xl-relative" style="background-color: #FFFFFF">
                <div class="d-flex flex-column position-xl-fixed top-0 bottom-0 w-xl-400px scroll-y">
                    <div class="d-flex flex-row-fluid flex-column text-center p-10 pt-lg-20">
                        <a href="<?=base_url()?>" class="py-1 mb-5">
                            <img alt="Logo" src="<?=base_url()?>/assets/media/app-logo-3.jpeg" style="height:100px;" />
                        </a>
                        <h1 class="fw-bolder fs-2qx pb-5" style="color: #986923;">Near Miss Reporting</h1>
                        <p class="fw-bold fs-3 text-muted">Scan, Report, and ensure a safer workplace for everyone.</p>
                    </div>
                </div>
            </div>

            <!-- Form Body -->
            <div class="d-flex flex-column flex-lg-row-fluid py-10" style="background-color: #F2C98A">
                <div class="d-flex flex-center flex-column flex-column-fluid">
                    <div class="w-100 p-10 p-lg-15 mx-auto bg-white rounded shadow-sm" style="max-width: 800px;">
                        
                        <div id="success_message" class="alert alert-success d-none"></div>
                        <div id="error_message" class="alert alert-danger d-none"></div>

                        <form class="form w-100" id="public_nearmiss_form" enctype="multipart/form-data">
                            
                            <h2 class="text-dark mb-8">Submit a Near Miss Report</h2>

                            <div class="row g-5">
                                <div class="col-12 mb-5">
                                    <h3 class="text-dark mb-4 border-bottom pb-2">A. Reporter Information</h3>
                                </div>
                                <div class="col-md-6 mb-5">
                                    <label class="required form-label">Reporter Name</label>
                                    <input type="text" name="public_reporter_name" class="form-control form-control-solid" required />
                                </div>
                                <div class="col-md-6 mb-5">
                                    <label class="form-label">Employee Code</label>
                                    <input type="text" name="public_reporter_emp_code" class="form-control form-control-solid" />
                                </div>
                                <div class="col-md-6 mb-5">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="public_reporter_email" class="form-control form-control-solid" />
                                </div>
                                <div class="col-md-6 mb-5">
                                    <label class="form-label">Mobile Number</label>
                                    <input type="text" name="public_reporter_mobile" class="form-control form-control-solid" />
                                </div>

                                <div class="col-12 mb-5 mt-8">
                                    <h3 class="text-dark mb-4 border-bottom pb-2">B. Incident Information</h3>
                                </div>
                                <div class="col-md-6 mb-5">
                                    <label class="required form-label">Category of Incidence</label>
                                    <select name="near_miss_category_text" class="form-select form-select-solid" required>
                                        <option value="Near Miss">Near Miss</option>
                                        <option value="Minor Injury">Minor Injury</option>
                                        <option value="Major Injury">Major Injury</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-5">
                                    <label class="required form-label">Customer / Project Name</label>
                                    <input type="text" name="project_name" class="form-control form-control-solid" required />
                                </div>

                                <div class="col-md-6 mb-5">
                                    <label class="required form-label">Job No</label>
                                    <input type="text" name="job_no" class="form-control form-control-solid" required />
                                </div>
                                <div class="col-md-6 mb-5">
                                    <label class="required form-label">Location</label>
                                    <select name="location" class="form-select form-select-solid" required>
                                        <option value="">Select Location</option>
                                        <?php if(isset($locations)) { foreach($locations as $loc) { ?>
                                            <option value="<?=htmlspecialchars($loc['location_name'])?>"><?=htmlspecialchars($loc['location_name'])?></option>
                                        <?php } } ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-5">
                                    <label class="required form-label">GPS Location</label>
                                    <div class="input-group">
                                        <input type="text" name="gps_location" id="gps_location" class="form-control form-control-solid" required />
                                        <button class="btn btn-secondary" type="button" onclick="getLocation()">Get Location</button>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-5">
                                    <label class="required form-label">Exact Area / Room</label>
                                    <input type="text" name="manual_location" class="form-control form-control-solid" required />
                                </div>

                                <div class="col-md-6 mb-5">
                                    <label class="required form-label">Date</label>
                                    <input type="date" name="near_miss_date" class="form-control form-control-solid" value="<?=date('Y-m-d')?>" required />
                                </div>
                                <div class="col-md-6 mb-5">
                                    <label class="required form-label">Time</label>
                                    <input type="time" name="near_miss_time" class="form-control form-control-solid" required />
                                </div>

                                <div class="col-md-6 mb-5">
                                    <label class="required form-label">Task Activity</label>
                                    <select name="mode_of_work" class="form-select form-select-solid" required>
                                        <option value="">Select Task Activity</option>
                                        <?php if(isset($task_activity)) { foreach($task_activity as $task) { ?>
                                        <option value="<?=$task['category_name']?>"><?=$task['category_name']?></option>
                                        <?php } } ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-5">
                                    <label class="form-label">Name of equipment involved</label>
                                    <input type="text" name="involved_equipment_name" class="form-control form-control-solid" />
                                </div>

                                <div class="col-12 mb-5 mt-5">
                                    <h3 class="text-dark mb-4 border-bottom pb-2">C. Incident Details</h3>
                                </div>

                                <div class="col-12 mb-5">
                                    <label class="required form-label">Brief Description of Observation / What Occurred</label>
                                    <textarea name="description_of_occurred" class="form-control form-control-solid" rows="3" required></textarea>
                                </div>
                                
                                <div class="col-12 mb-5">
                                    <label class="required form-label">What could have happened</label>
                                    <textarea name="what_could_have_happened" class="form-control form-control-solid" rows="2" required></textarea>
                                </div>
                                
                                <div class="col-12 mb-5">
                                    <label class="form-label">Immediate Action Taken</label>
                                    <textarea name="immediate_action" class="form-control form-control-solid" rows="2"></textarea>
                                </div>

                                <!-- Conditional fields for minor/major injury -->
                                <div id="Injury_Details" style="display:none; width:100%;">
                                    <h3 class="text-dark mb-5 mt-5 border-bottom pb-3">D. Involved Person / Injury Details</h3>
                                    <div class="row g-5">
                                        <div class="col-md-6 mb-5">
                                            <label class="required form-label">Name of person</label>
                                            <input type="text" name="incident_person_name" id="incident_person_name" class="form-control form-control-solid" />
                                        </div>
                                        <div class="col-md-6 mb-5">
                                            <label class="required form-label">Designation</label>
                                            <input type="text" name="incident_person_designation" id="incident_person_designation" class="form-control form-control-solid" />
                                        </div>
                                        <div class="col-md-4 mb-5">
                                            <label class="required form-label">Age</label>
                                            <input type="number" name="incident_person_age" id="incident_person_age" class="form-control form-control-solid" />
                                        </div>
                                        <div class="col-md-4 mb-5">
                                            <label class="required form-label">Company</label>
                                            <input type="text" name="incident_person_company" id="incident_person_company" class="form-control form-control-solid" />
                                        </div>
                                        <div class="col-md-4 mb-5">
                                            <label class="required form-label">Shift</label>
                                            <input type="text" name="incident_person_sift" id="incident_person_sift" class="form-control form-control-solid" />
                                        </div>
                                        
                                        <div class="col-md-4 mb-5">
                                            <label class="required form-label">Damage Nature</label>
                                            <select name="nature_of_damage" id="nature_of_damage" class="form-select form-select-solid">
                                                <option value="">Select Damage Nature</option>
                                                <?php if(isset($nature_incident)) { foreach($nature_incident as $nature){?>
                                                <option value="<?=$nature['incident_name'] ?>"><?=$nature['incident_name'] ?></option>
                                                <?php } } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-5">
                                            <label class="required form-label">Is Body Part Affected?</label>
                                            <select name="body_part_affect" id="body_part_affect" class="form-select form-select-solid">
                                                <option value="0">No</option>
                                                <option value="1">Yes</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-5">
                                            <label class="form-label">Body Part Details</label>
                                            <select name="body_part_affect_details" id="body_part_affect_details" class="form-select form-select-solid">
                                                <option value="">Select Body Part</option>
                                                <?php if(isset($body_part)) { foreach($body_part as $part){?>
                                                <option value="<?=$part['body_part_name'] ?>"><?=$part['body_part_name'] ?></option>
                                                <?php } } ?>
                                            </select>
                                        </div>
                                        <div class="col-12 mb-5">
                                            <label class="form-label">Causes of Incident</label>
                                            <input type="text" name="causes_of_incident" class="form-control form-control-solid" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mb-5 mt-5">
                                    <h3 class="text-dark mb-4 border-bottom pb-2">E. Before Proof / Evidence</h3>
                                </div>
                                <div class="col-12 mb-5">
                                    <label class="form-label">Upload Before Proof / Evidence (Photos/Documents)</label>
                                    <input type="file" name="before_images[]" class="form-control form-control-solid" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" multiple />
                                    <div class="form-text">You can select multiple files to upload as proof.</div>
                                </div>
                            </div>

                            <div class="text-center mt-10">
                                <button type="submit" id="submit_btn" class="btn btn-lg btn-primary w-100">
                                    <span class="indicator-label">Submit Report</span>
                                    <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?=base_url()?>/assets/plugins/global/plugins.bundle.js"></script>
    <script src="<?=base_url()?>/assets/js/scripts.bundle.js"></script>
    
    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            } else { 
                alert("Geolocation is not supported by this browser.");
            }
        }
        function showPosition(position) {
            document.getElementById("gps_location").value = position.coords.latitude + "," + position.coords.longitude;
        }
        function showError(error) {
            alert("Error getting location. Please enter it manually.");
        }

        document.getElementById('public_nearmiss_form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            let submitBtn = document.getElementById('submit_btn');
            submitBtn.setAttribute('data-kt-indicator', 'on');
            submitBtn.disabled = true;
            
            let formData = new FormData(this);
            
            fetch("<?=base_url('public/near-miss')?>", {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.removeAttribute('data-kt-indicator');
                submitBtn.disabled = false;
                
                if(data.status == '1') {
                    document.getElementById('success_message').textContent = data.message;
                    document.getElementById('success_message').classList.remove('d-none');
                    document.getElementById('error_message').classList.add('d-none');
                    document.getElementById('public_nearmiss_form').reset();
                    window.scrollTo(0,0);
                } else {
                    document.getElementById('error_message').textContent = data.message;
                    document.getElementById('error_message').classList.remove('d-none');
                    document.getElementById('success_message').classList.add('d-none');
                }
            })
            .catch(error => {
                submitBtn.removeAttribute('data-kt-indicator');
                submitBtn.disabled = false;
                document.getElementById('error_message').textContent = "An error occurred during submission.";
                document.getElementById('error_message').classList.remove('d-none');
            });
        });
        
        // Show/hide injury details based on category
        document.querySelector('select[name="near_miss_category_text"]').addEventListener('change', function(e) {
            let val = e.target.value;
            let injuryDiv = document.getElementById('Injury_Details');
            let reqFields = injuryDiv.querySelectorAll('#incident_person_name, #incident_person_designation, #incident_person_age, #incident_person_company, #incident_person_sift, #nature_of_damage');
            
            if(val == 'Minor Injury' || val == 'Major Injury') {
                injuryDiv.style.display = 'block';
                reqFields.forEach(f => f.setAttribute('required', 'required'));
            } else {
                injuryDiv.style.display = 'none';
                reqFields.forEach(f => f.removeAttribute('required'));
            }
        });
    </script>
</body>
</html>
