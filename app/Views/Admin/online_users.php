<?= $this->extend('Layout/base_admin') ?>
<?= $this->section('main_body') ?>

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend" data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}" class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Online Users & Login Activity Dashboard</h1>
            </div>
        </div>
    </div>

    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">


            <style>
                .kpi-card {
                    cursor: pointer;
                    transition: transform 0.2s ease, box-shadow 0.2s ease;
                }
                .kpi-card:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
                }
            </style>

            <!-- Summary Cards -->
            <div class="row g-5 g-xl-8 mb-xl-10">
                <div class="col-xl-3 col-md-6">
                    <div class="card kpi-card kpi-blue shadow-sm" id="card_total_registered" onclick="window.location.href='<?= base_url('Masters/User') ?>';">
                        <div class="card-body">
                            <div class="kpi-icon-box bg-kpi-blue">
                                <i class="fas fa-users text-white"></i>
                            </div>
                            <div class="kpi-text">
                                <div class="kpi-label">Total Registered Users</div>
                                <div class="kpi-value" id="summary_total_registered">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card kpi-card kpi-green shadow-sm" id="card_currently_online">
                        <div class="card-body">
                            <div class="kpi-icon-box bg-kpi-green">
                                <i class="fas fa-user-check text-white"></i>
                            </div>
                            <div class="kpi-text">
                                <div class="kpi-label">Currently Online Users</div>
                                <div class="kpi-value" id="summary_online">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card kpi-card kpi-purple shadow-sm" id="card_todays_logins">
                        <div class="card-body">
                            <div class="kpi-icon-box bg-kpi-purple">
                                <i class="fas fa-sign-in-alt text-white"></i>
                            </div>
                            <div class="kpi-text">
                                <div class="kpi-label">Today's Logins</div>
                                <div class="kpi-value" id="summary_todays_logins">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card kpi-card kpi-blue shadow-sm" id="card_total_active_users">
                        <div class="card-body">
                            <div class="kpi-icon-box bg-kpi-blue" style="background: #009ef7;">
                                <i class="fas fa-chart-line text-white"></i>
                            </div>
                            <div class="kpi-text">
                                <div class="kpi-label">Total Users (Logged In At Least Once)</div>
                                <div class="kpi-value" id="summary_total_active_users">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Filter Panel -->
            <div class="card mb-5 mb-xl-10">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">Advanced Filters</span>
                    </h3>
                </div>
                <div class="card-body py-3">
                    <form id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Role</label>
                                <select class="form-select form-select-solid" data-control="select2" data-placeholder="Select a role" id="filterRole">
                                    <option></option>
                                    <?php if (!empty($roles)): ?>
                                        <?php foreach ($roles as $r): ?>
                                            <option value="<?= htmlspecialchars($r) ?>"><?= htmlspecialchars($r) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">User Name</label>
                                <select class="form-select form-select-solid" data-control="select2" data-placeholder="Select a user" id="filterUserName">
                                    <option></option>
                                    <!-- Options populated dynamically based on role -->
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Login Date</label>
                                <input type="date" class="form-control form-control-solid" id="filterLoginDate">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Last Activity Date</label>
                                <input type="date" class="form-control form-control-solid" id="filterLastActivityDate">
                            </div>
                        </div>
                        <div class="row g-3 mt-3">
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-custom-search me-2 px-4" id="btnSearch">
                                    <i class="fas fa-search me-1 text-white"></i> Search
                                </button>
                                <button type="button" class="btn btn-secondary me-2 px-4" id="btnReset">
                                    <i class="fas fa-redo me-1"></i> Reset
                                </button>
                                <button type="button" class="btn btn-custom-refresh px-4" id="btnRefresh">
                                    <i class="fas fa-sync-alt me-1  text-white"></i> Refresh
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- DataTables -->
            <div class="card mb-5 mb-xl-10">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">Users List</span>
                    </h3>
                    <div class="card-toolbar">
                        <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0">
                            <li class="nav-item">
                                <a class="nav-link active text-active-primary" data-bs-toggle="tab" href="#kt_tab_online">Online Users</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary" data-bs-toggle="tab" href="#kt_tab_history">Login History</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body py-3">
                    <div class="tab-content" id="myTabContent">
                        <!-- Online Users Tab -->
                        <div class="tab-pane fade show active" id="kt_tab_online" role="tabpanel">
                            <div class="table-responsive">
                                <table id="online_users_table" class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                                    <thead>
                                        <tr class="fw-bolder text-muted">
                                            <th>User Name</th>
                                            <th>Emp ID</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Region</th>
                                            <th>Login Time</th>
                                            <th>Last Activity</th>
                                            <th>IP Address</th>
                                            <th>Device/Browser</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Login History Tab -->
                        <div class="tab-pane fade" id="kt_tab_history" role="tabpanel">
                            <div class="table-responsive">
                                <table id="login_history_table" class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                                    <thead>
                                        <tr class="fw-bolder text-muted">
                                            <th>User Name</th>
                                            <th>Role</th>
                                            <th>Region</th>
                                            <th>Login Time</th>
                                            <th>Logout Time</th>
                                            <th>Duration (Mins)</th>
                                            <th>Logout Reason</th>
                                            <th>IP Address</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('javascript_section') ?>
<link href="<?=base_url();?>/assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>
<script src="<?=base_url();?>/assets/plugins/custom/datatables/datatables.bundle.js"></script>
<script>
    $(document).ready(function() {
        var onlineTable = $('#online_users_table').DataTable({
            "order": [[ 6, "desc" ]], // Sort by Last Activity descending
            dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                {
                    extend: 'excelHtml5',
                    className: 'btn btn-success mb-2',
                    text: '<i class="fas fa-file-excel"></i> Export Excel',
                    title: 'Online Users List'
                },
                {
                    extend: 'csvHtml5',
                    className: 'btn btn-info mb-2',
                    text: '<i class="fas fa-file-csv"></i> Export CSV',
                    title: 'Online Users List'
                }
            ]
        });

        var historyTable = $('#login_history_table').DataTable({
            "order": [[ 3, "desc" ]], // Sort by Login Time descending
            dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                {
                    extend: 'excelHtml5',
                    className: 'btn-export-excel mb-2',
                    text: '<i class="fas fa-file-excel"></i> Export Excel',
                    title: 'Login History'
                },
                {
                    extend: 'csvHtml5',
                    className: 'btn-export-csv mb-2',
                    text: '<i class="fas fa-file-csv"></i> Export CSV',
                    title: 'Login History'
                }
            ]
        });

        function fetchOnlineUsers() {
            var filterData = {
                role: $('#filterRole').val(),
                user_name: $('#filterUserName').val(),
                login_date: $('#filterLoginDate').val(),
                last_activity_date: $('#filterLastActivityDate').val()
            };

            $.ajax({
                url: "<?= base_url('Admin/OnlineUsers/get_online_users') ?>",
                type: "GET",
                data: filterData,
                dataType: "json",
                success: function(response) {
                    $('#summary_total_registered').text(response.summary.total_registered);
                    $('#summary_online').text(response.summary.online);
                    $('#summary_todays_logins').text(response.summary.todays_logins);
                    $('#summary_total_active_users').text(response.summary.total_active_users);

                    onlineTable.clear();
                    
                    if (response.data && response.data.length > 0) {
                        $.each(response.data, function(index, user) {
                            var browserBadge = `<span class="badge badge-secondary">${user.browser || 'Unknown'}</span>`;
                            var deviceBadge = `<span class="badge badge-info">${user.device_type || 'Unknown'}</span>`;
                            
                            onlineTable.row.add([
                                user.user_name || 'Admin',
                                user.user_emp_code || '-',
                                user.user_email || '-',
                                user.user_designation || '-',
                                user.user_region || '-',
                                user.login_time,
                                user.last_activity,
                                user.ip_address,
                                browserBadge + ' ' + deviceBadge
                            ]);
                        });
                    }
                    
                    onlineTable.draw();
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching online users: ", error);
                }
            });
        }

        // Dynamic User Name dropdown based on Role
        $('#filterRole').on('change', function() {
            var role = $(this).val();
            var $userNameSelect = $('#filterUserName');
            
            $userNameSelect.empty().append('<option></option>').trigger('change'); // Clear existing and update select2
            
            if (role) {
                $.ajax({
                    url: "<?= base_url('Admin/OnlineUsers/get_users_by_role') ?>",
                    type: "GET",
                    data: { role: role },
                    dataType: "json",
                    success: function(response) {
                        if (response.users && response.users.length > 0) {
                            $.each(response.users, function(index, user) {
                                $userNameSelect.append($('<option>', {
                                    value: user.user_name,
                                    text: user.user_name
                                }));
                            });
                            // Re-initialize select2 with new options
                            $userNameSelect.trigger('change');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching users by role: ", error);
                    }
                });
            }
        });

        // Filter button events
        $('#btnSearch').on('click', function() {
            fetchOnlineUsers();
            fetchLoginHistory();
        });

        $('#btnReset').on('click', function() {
            $('#filterForm')[0].reset();
            $('#filterRole').val(null).trigger('change');
            fetchOnlineUsers();
            fetchLoginHistory();
        });

        $('#btnRefresh').on('click', function() {
            fetchOnlineUsers();
            fetchLoginHistory();
        });

        function fetchLoginHistory() {
            var filterData = {
                role: $('#filterRole').val(),
                user_name: $('#filterUserName').val(),
                login_date: $('#filterLoginDate').val(),
                last_activity_date: $('#filterLastActivityDate').val()
            };
            
            $.ajax({
                url: "<?= base_url('Admin/OnlineUsers/get_login_history') ?>",
                type: "GET",
                data: filterData,
                dataType: "json",
                success: function(response) {
                    historyTable.clear();
                    $.each(response.data, function(index, log) {
                        historyTable.row.add([
                            log.user_name || 'Admin',
                            log.user_designation || '-',
                            log.user_region || '-',
                            log.login_time,
                            log.logout_time ? log.logout_time : 'Active',
                            log.session_duration_minutes !== null ? log.session_duration_minutes : '-',
                            log.logout_reason ? log.logout_reason : (log.logout_time ? 'Unknown' : 'Active'),
                            log.ip_address,
                            log.status === 'Success' ? '<span class="badge badge-light-success">Success</span>' : '<span class="badge badge-light-danger">Failed</span>'
                        ]);
                    });
                    historyTable.draw();
                }
            });
        }

        // Card click event handlers
        $('#card_currently_online').on('click', function() {
            var tabEl = document.querySelector('a[href="#kt_tab_online"]');
            if (tabEl) {
                var tab = new bootstrap.Tab(tabEl);
                tab.show();
            }
            $('html, body').animate({
                scrollTop: $("#online_users_table").offset().top - 100
            }, 300);
        });

        $('#card_todays_logins').on('click', function() {
            var tabEl = document.querySelector('a[href="#kt_tab_history"]');
            if (tabEl) {
                var tab = new bootstrap.Tab(tabEl);
                tab.show();
            }
            var today = new Date().toISOString().split('T')[0];
            $('#filterLoginDate').val(today);
            fetchLoginHistory();
            $('html, body').animate({
                scrollTop: $("#login_history_table").offset().top - 100
            }, 300);
        });

        $('#card_total_active_users').on('click', function() {
            var tabEl = document.querySelector('a[href="#kt_tab_history"]');
            if (tabEl) {
                var tab = new bootstrap.Tab(tabEl);
                tab.show();
            }
            $('#filterLoginDate').val('');
            $('#filterLastActivityDate').val('');
            fetchLoginHistory();
            $('html, body').animate({
                scrollTop: $("#login_history_table").offset().top - 100
            }, 300);
        });

        // Initial load
        fetchOnlineUsers();
        fetchLoginHistory();

        // Refresh online users every 30 seconds
        setInterval(fetchOnlineUsers, 30000);
    });
</script>
<?= $this->endSection() ?>
