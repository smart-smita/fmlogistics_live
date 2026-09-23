<?php
$session = session();
$isClusterManager = ($session->get('user_designation') && strtolower($session->get('user_designation')) === 'cluster manager');
helper('designation_acl');
$isHigherAuthorityUser = isHigherAuthority();
$isAccountManagerUser = isAccountManager();
$userRegion  = $session->get('user_region')  ?? '';
$userCluster = $session->get('user_name')    ?? '';
if ($isClusterManager) {
    $userRegion = getClusterManagerAssignedRegion();
    $userCluster = getClusterManagerAssignedCluster();
}
$filtersLocked = (($isClusterManager || $isAccountManagerUser) && !$isHigherAuthorityUser);
// If admin flag is 1, unlock filters
if (($_SESSION['admin_flag'] ?? 0) == 1) {
    $filtersLocked = false;
}
?>

<div id="oe_filter_container"
     data-get-clusters-url="<?= base_url('Customer/Audit_dashboard/get_clusters_by_region') ?>"
     data-get-locations-url="<?= base_url('Customer/Audit_dashboard/get_locations_by_cluster') ?>"
     data-pre-region='<?= json_encode($selRegion ?? []) ?>'
     data-pre-cluster='<?= json_encode($selCluster ?? []) ?>'
     data-pre-location='<?= json_encode($selLocation ?? []) ?>'
     data-user-region='<?= json_encode($userRegion) ?>'
     data-user-cluster='<?= json_encode($userCluster) ?>'
     data-is-cluster="<?= $isClusterManager ? '1' : '0' ?>"
     data-is-account-manager="<?= $isAccountManagerUser ? '1' : '0' ?>"
     data-is-higher-authority="<?= $isHigherAuthorityUser ? '1' : '0' ?>">

<form method="get" class="card mb-5 p-3" style="overflow: visible;">
    <div class="row g-3 align-items-start">

        <div class="col-md-2">
            <label class="form-label fw-bold">Region</label>
            <div class="position-relative">
                <select id="flt_region" name="region[]" multiple="multiple"
                        class="form-select form-select-sm fw-semibold js-example-basic-single"
                        <?= $filtersLocked ? 'disabled' : '' ?>>
                    <?php if($filtersLocked): ?>
                        <?php foreach($selRegion as $r): ?>
                            <option value="<?= esc($r) ?>" selected><?= esc($r) ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach($regions as $r): ?>
                            <option value="<?= esc($r['region_name']) ?>" 
                                <?= in_array(strtolower($r['region_name']), array_map('strtolower', $selRegion)) ? 'selected' : '' ?>>
                                <?= esc($r['region_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-bold">Cluster</label>
            <div class="position-relative">
                <select id="flt_cluster" name="cluster[]" multiple="multiple"
                        class="form-select form-select-sm fw-semibold js-example-basic-single"
                        <?= $filtersLocked ? 'disabled' : '' ?>>
                    <?php if($filtersLocked): ?>
                        <?php foreach($selCluster as $c): ?>
                            <option value="<?= esc($c) ?>" selected><?= esc($c) ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach($clusters as $c): ?>
                            <option value="<?= esc($c['cluster_name']) ?>" 
                                <?= in_array($c['cluster_name'], $selCluster) ? 'selected' : '' ?>>
                                <?= esc($c['cluster_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-bold">Location</label>
            <div class="position-relative">
                <select id="flt_location" name="location[]" multiple="multiple"
                        class="form-select form-select-sm fw-semibold js-example-basic-single"
                        <?= $filtersLocked ? 'disabled' : '' ?>>
                    <?php if($filtersLocked && $isAccountManagerUser): ?>
                        <?php foreach($selLocation as $l): ?>
                            <option value="<?= esc($l) ?>" selected><?= esc($l) ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach($locations as $l): ?>
                            <option value="<?= esc($l['location_name']) ?>" 
                                <?= in_array($l['location_name'], $selLocation) ? 'selected' : '' ?>>
                                <?= esc($l['location_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="col-md-2">
            <label class="form-label fw-bold">Month</label>
            <input type="month" name="month" class="form-control form-control-sm" value="<?= esc($selMonth ?? '') ?>">
        </div>

        <div class="col-md-auto d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-sm btn-primary" style="margin-top:28px;">Show</button>
            <a href="<?= base_url('Masters/Audit_final_structure') ?>" class="btn btn-sm btn-secondary" style="margin-top:28px;">Reset</a>
        </div>
    </div>
</form>
</div>

<script>
(function () {
  function onReady(cb) {
    if (document.readyState === "complete" || document.readyState === "interactive") {
      return cb();
    }
    document.addEventListener("DOMContentLoaded", cb);
  }

  onReady(function () {
    var $ = window.jQuery;
    var container = $("#oe_filter_container");
    if (!container.length) return;

    var urlClusters = container.data("get-clusters-url");
    var urlLocations = container.data("get-locations-url");

    var preRegion   = container.data("pre-region");   
    var preCluster  = container.data("pre-cluster");  
    var preLocation = container.data("pre-location"); 

    var userRegion  = container.data("user-region")   || "";
    var userCluster = container.data("user-cluster")  || "";
    var isClusterManager = container.data("is-cluster") === 1 || container.data("is-cluster") === "1";
    var isAccountManager = container.data("is-account-manager") === 1 || container.data("is-account-manager") === "1";
    var isHigherAuthority = container.data("is-higher-authority") === 1 || container.data("is-higher-authority") === "1";
    
    if (isHigherAuthority) {
        isClusterManager = false;
        isAccountManager = false;
    }

    var $region   = $("#flt_region");
    var $cluster  = $("#flt_cluster");
    var $location = $("#flt_location");

    function safeOptions(rows, key, placeholder) {
      if (rows && rows.clusters) {
          rows = rows.clusters;
      } else if (rows && rows.locations) {
          rows = rows.locations;
      }
      
      var html = '<option value="selectAll">Select All</option>';
      if (!Array.isArray(rows)) return html;
      
      rows.forEach(function (r) {
        var v = (r[key] || "").trim();
        if (v) html += `<option value="${v}">${v}</option>`;
      });
      return html;
    }

    function loadClusters(region, preselect, done) {
      if (!isHigherAuthority && (isClusterManager || isAccountManager)) {
        $cluster.prop("disabled", true); 
        if (!isAccountManager) {
             $location.html('').prop("disabled", true); 
        } else {
             $location.prop("disabled", true);
        }
      } else {
        $location.html(''); 
      }

      if (!region || region.length === 0) {
        if (!isClusterManager && !isAccountManager) {
             $cluster.html('').prop("disabled", false);
        }
        if (done) done();
        return;
      }

      var regionJson = Array.isArray(region) ? JSON.stringify(region) : region;

      $.ajax({
        url: urlClusters,
        type: 'POST',
        dataType: 'json',
        data: { region_name: regionJson }, 
        success: function(rows) {
            $cluster.html(safeOptions(rows, "cluster_name", "Select Cluster"));

            if (isClusterManager) {
                $cluster.val(userCluster ? [userCluster] : []);
            } else if (isAccountManager) {
                 if (preselect) $cluster.val(preselect);
            } else {
                if (preselect) $cluster.val(preselect);
                $cluster.prop("disabled", false);
            }

            if ($.fn.select2) $cluster.trigger("change.select2");
            if (done) done();
        },
        error: function(xhr, status, error) {
            if (!isClusterManager && !isAccountManager) {
                 $cluster.prop("disabled", false); 
            }
            if (done) done();
        }
      });
    }

    function loadLocations(region, cluster, preselect, done) {
      if (!isHigherAuthority && isAccountManager) {
           $location.prop("disabled", true);
      } 

      if (!region || region.length === 0 || !cluster || cluster.length === 0) {
         if (!isAccountManager) {
             $location.html('').prop("disabled", false);
         }
        if (done) done();
        return;
      }
      
      var regionJson  = Array.isArray(region)  ? JSON.stringify(region)  : region;
      var clusterJson = Array.isArray(cluster) ? JSON.stringify(cluster) : cluster;

      $.ajax({
        url: urlLocations,
        type: 'POST',
        dataType: 'json',
        data: { region_name: regionJson, cluster_name: clusterJson },
        success: function(rows) {
            $location.html(safeOptions(rows, "location_name", "Select Location"));
            
            if (!isAccountManager) {
                $location.prop("disabled", false);
            }

            if (preselect) {
                $location.val(preselect);
            }

            if ($.fn.select2) $location.trigger("change.select2");
            if (done) done();
        },
        error: function() {
            if (!isAccountManager) {
                 $location.prop("disabled", false);
            }
            if (done) done();
        }
      });
    }

    $region.on("select2:close", function () {
      loadClusters($(this).val(), null);
    });

    $cluster.on("select2:close", function () {
      loadLocations($region.val(), $(this).val(), null);
    });

    if (isClusterManager || isAccountManager) {
       var rVal = $region.val(); 
       var cVal = $cluster.val();
       
       if (!isAccountManager) {
           loadLocations(rVal, cVal, preLocation || null);
       } else {
           $location.prop("disabled", true);
       }
    } else if (preRegion && preRegion.length > 0) {
       loadClusters(preRegion, preCluster, function () {
         setTimeout(function () {
           loadLocations(preRegion, preCluster, preLocation);
         }, 120);
       });
    }

    function formatOption(state) {
        if (!state.id) {
            return state.text;
        }
        return state.text;
    }
  });
})();

document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined' && $.fn.select2) {
        // Initialization handled by custom-multiselect.js globally
    }
});
</script>


