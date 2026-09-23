/* public/js/oe_nc_filters.js
   Handles dependent Region -> Cluster -> Location selects.
   Expects a container #oe_filter_container with data-* attributes:
     data-get-clusters-url, data-get-locations-url,
     data-pre-region, data-pre-cluster, data-pre-location
   Optional: data-csrf-name and data-csrf-value for CI CSRF.
*/

(function () {
  "use strict";

  function loadScript(url, cb) {
    var s = document.createElement("script");
    s.src = url;
    s.onload = function () { cb(null); };
    s.onerror = function () { cb(new Error("Failed to load " + url)); };
    document.head.appendChild(s);
  }

  function ensurejQuery(cb) {
    if (window.jQuery) return cb(null, window.jQuery);
    loadScript("https://code.jquery.com/jquery-3.6.0.min.js", function (err) {
      if (err) return cb(err);
      return cb(null, window.jQuery);
    });
  }

  function bootstrap($) {
    var container = document.getElementById("oe_filter_container");
    if (!container) return;

    var urlClusters = container.getAttribute("data-get-clusters-url");
    var urlLocations = container.getAttribute("data-get-locations-url");
    var preRegion = container.getAttribute("data-pre-region") || "";
    var preCluster = container.getAttribute("data-pre-cluster") || "";
    var preLocation = container.getAttribute("data-pre-location") || "";
    var csrfName = container.getAttribute("data-csrf-name") || null;
    var csrfValue = container.getAttribute("data-csrf-value") || null;

    var $region = $("#flt_region");
    var $cluster = $("#flt_cluster");
    var $location = $("#flt_location");

    if ($region.length === 0 || $cluster.length === 0 || $location.length === 0) return;

    try {
      if ($.fn && $.fn.select2) {
        $(".select2").each(function () {
          var $s = $(this);
          if (!$s.data("select2")) $s.select2({ width: "100%" });
        });
      }
    } catch (e) { /* ignore */ }

    function buildOptions(rows, key, placeholder) {
      var html = "<option value=\"\">" + placeholder + "</option>";
      if (!Array.isArray(rows)) return html;
      rows.forEach(function (r) {
        var v = (r[key] || "").toString().trim();
        if (!v) return;
        var opt = $("<div>").text(v).html();
        html += '<option value="' + opt + '">' + opt + "</option>";
      });
      return html;
    }

    function postJson(url, data, cb) {
      if (csrfName && csrfValue) data[csrfName] = csrfValue;
      $.ajax({
        url: url,
        method: "POST",
        data: data,
        dataType: "json",
        success: function (resp) { cb(null, resp); },
        error: function (xhr, status, err) { cb(err || status || "error"); }
      });
    }

    function loadClusters(region, preselectCluster, done) {
      $cluster.prop("disabled", true).html('<option>Loading...</option>');
      $location.prop("disabled", true).html('<option value="">Select location</option>');
      if (!region) {
        $cluster.prop("disabled", false).html('<option value="">Select region first</option>');
        if ($.fn && $.fn.select2) $cluster.trigger("change.select2");
        if (done) done(null);
        return;
      }
      postJson(urlClusters, { region_name: region }, function (err, rows) {
        if (err) {
          $cluster.html('<option value="">Error loading clusters</option>').prop("disabled", false);
          if ($.fn && $.fn.select2) $cluster.trigger("change.select2");
          if (done) done(err);
          return;
        }
        var opts = buildOptions(rows, "cluster_name", "Select cluster");
        $cluster.html(opts).prop("disabled", false);
        if (preselectCluster) $cluster.val(preselectCluster);
        if ($.fn && $.fn.select2) $cluster.trigger("change.select2");
        if (done) done(null);
      });
    }

    function loadLocations(region, cluster, preselectLocation, done) {
      $location.prop("disabled", true).html('<option>Loading...</option>');
      if (!region || !cluster) {
        $location.html('<option value="">Select cluster first</option>').prop("disabled", false);
        if ($.fn && $.fn.select2) $location.trigger("change.select2");
        if (done) done(null);
        return;
      }
      postJson(urlLocations, { region_name: region, cluster_name: cluster }, function (err, rows) {
        if (err) {
          $location.html('<option value="">Error loading locations</option>').prop("disabled", false);
          if ($.fn && $.fn.select2) $location.trigger("change.select2");
          if (done) done(err);
          return;
        }
        var opts = buildOptions(rows, "location_name", "Select location");
        $location.html(opts).prop("disabled", false);
        if (preselectLocation) $location.val(preselectLocation);
        if ($.fn && $.fn.select2) $location.trigger("change.select2");
        if (done) done(null);
      });
    }

    // attach handlers
    $region.off("change.oe").on("change.oe", function () {
      loadClusters($(this).val(), null);
    });
    $cluster.off("change.oe").on("change.oe", function () {
      loadLocations($region.val(), $(this).val(), null);
    });

    // initial preloads if needed
    if (preRegion) {
      loadClusters(preRegion, preCluster, function () {
        if (preCluster) {
          setTimeout(function () {
            loadLocations(preRegion, preCluster, preLocation);
          }, 150);
        }
      });
    }
  } // bootstrap end

  // start
  ensurejQuery(function (err, $) {
    if (err) {
      console.error("oe_nc_filters: failed to load jQuery", err);
      return;
    }
    bootstrap($);
  });

})();
