/* assets/js/hse_nc_filters.js
   Handles dependent Account Type -> Region[] -> Cluster[] -> Location[] selects for HSE NC Tracker.
   Now supports multi-select with Select2 for Region, Cluster, and Location.
   Expects a container #hse_filter_container with data-* attributes.
*/

(function () {
    "use strict";

    // Polling for jQuery (since it might be loaded at the footer)
    function waitForJQuery(cb) {
        if (window.jQuery) {
            cb(null, window.jQuery);
            return;
        }
        var attempts = 0;
        var maxAttempts = 100; // 10 seconds max
        var interval = setInterval(function () {
            attempts++;
            if (window.jQuery) {
                clearInterval(interval);
                cb(null, window.jQuery);
            } else if (attempts >= maxAttempts) {
                clearInterval(interval);
                cb(new Error("jQuery not found after waiting"));
            }
        }, 100);
    }

    function bootstrap($) {
        var container = document.getElementById("hse_filter_container");
        if (!container) return;

        var urlClusters = container.getAttribute("data-get-clusters-url");
        var urlLocations = container.getAttribute("data-get-locations-url");
        var preAccountTypeStr = container.getAttribute("data-pre-account-type") || "[]";
        var preRegionsStr = container.getAttribute("data-pre-regions") || "[]";
        var preClustersStr = container.getAttribute("data-pre-clusters") || "[]";
        var preLocationsStr = container.getAttribute("data-pre-locations") || "[]";

        var preAccountType = [];
        var preRegions = [];
        var preClusters = [];
        var preLocations = [];

        try {
            preAccountType = JSON.parse(preAccountTypeStr);
            if (!Array.isArray(preAccountType)) preAccountType = [];
        } catch (e) { preAccountType = []; }

        try {
            preRegions = JSON.parse(preRegionsStr);
            if (!Array.isArray(preRegions)) preRegions = [];
        } catch (e) { preRegions = []; }

        try {
            preClusters = JSON.parse(preClustersStr);
            if (!Array.isArray(preClusters)) preClusters = [];
        } catch (e) { preClusters = []; }

        try {
            preLocations = JSON.parse(preLocationsStr);
            if (!Array.isArray(preLocations)) preLocations = [];
        } catch (e) { preLocations = []; }

        var $accountType = $("#flt_account_type");
        var $region = $("#flt_region");
        var $cluster = $("#flt_cluster");
        var $location = $("#flt_location");

        if ($accountType.length === 0 || $region.length === 0 || $cluster.length === 0 || $location.length === 0) return;

        // Shared function to update "ALL" display for any multi-select
        var updateAllDisplay = function($el) {
            setTimeout(function() {
                var $container = $el.next('.select2-container');
                var $rendered = $container.find('.select2-selection__rendered');
                var selectedVals = $el.val() || [];
                var totalCount = $el.find('option:not([value="__ALL__"])').length;
                
                if (selectedVals.length === totalCount && totalCount > 0) {
                    // All selected - show "ALL" with clear button
                    var html = '<li class="select2-selection__choice" title="Click × to clear all">' +
                               '<span class="select2-selection__choice__remove" role="presentation">×</span>' +
                               '<span class="select2-selection__choice__display">ALL (' + totalCount + ' items)</span>' +
                               '</li>';
                    $rendered.html(html);
                    
                    // Handle clear button click
                    $rendered.find('.select2-selection__choice__remove').off('click').on('click', function(e) {
                        e.stopPropagation();
                        $el.val(null).trigger('change');
                    });
                }
            }, 100); // Increased timeout to ensure Select2 has finished rendering
        };

        // Initialize Select2
        try {
            if ($.fn && $.fn.select2) {
                // Helper to init if not exists
                var initSelect2 = function ($el, options) {
                    if (!$el.data("select2") && !$el.hasClass("select2-hidden-accessible")) {
                        $el.select2(options);
                    }
                };

                // Single-select
                $(".select2-single").each(function () {
                    initSelect2($(this), {
                        width: "100%",
                        placeholder: "Select an option"
                    });
                });

                // Multi-select for Region, Cluster, Location AND Account Type
                $(".select2-multi").each(function () {
                    var $el = $(this);
                    
                    initSelect2($el, {
                        width: "100%",
                        placeholder: "Select multiple options",
                        allowClear: true,
                        closeOnSelect: false
                    });
                    
                    // Update display after selection changes
                    $el.on('change', function() {
                        updateAllDisplay($el);
                    });
                    
                    // Initial update
                    updateAllDisplay($el);
                });
            }
        } catch (e) {
            console.error("Select2 initialization error:", e);
        }

        function buildOptions(rows, key) {
            var html = '<option value="__ALL__">Select All</option>';
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
            $.ajax({
                url: url,
                method: "POST",
                data: data,
                dataType: "json",
                success: function (resp) { cb(null, resp); },
                error: function (xhr, status, err) { cb(err || status || "error"); }
            });
        }

        function loadClusters(regions, preselectClusters, done) {
            $cluster.prop("disabled", true).html('').trigger("change.select2");
            $location.prop("disabled", true).html('').trigger("change.select2");

            if (!regions || !Array.isArray(regions) || regions.length === 0) {
                $cluster.prop("disabled", false).html('').trigger("change.select2");
                if (done) done(null);
                return;
            }

            postJson(urlClusters, { regions: regions }, function (err, rows) {
                if (err) {
                    $cluster.html('').prop("disabled", false).trigger("change.select2");
                    if (done) done(err);
                    return;
                }
                var opts = buildOptions(rows, "cluster_name");
                $cluster.html(opts).prop("disabled", false);

                if (preselectClusters && Array.isArray(preselectClusters) && preselectClusters.length > 0) {
                    $cluster.val(preselectClusters);
                }

                $cluster.trigger("change.select2");
                
                // Update display after loading
                updateAllDisplay($cluster);
                
                if (done) done(null);
            });
        }

        function loadLocations(regions, clusters, preselectLocations, done) {
            $location.prop("disabled", true).html('').trigger("change.select2");

            if (!clusters || !Array.isArray(clusters) || clusters.length === 0) {
                $location.html('').prop("disabled", false).trigger("change.select2");
                if (done) done(null);
                return;
            }

            postJson(urlLocations, { regions: regions || [], clusters: clusters }, function (err, rows) {
                if (err) {
                    $location.html('').prop("disabled", false).trigger("change.select2");
                    if (done) done(err);
                    return;
                }
                var opts = buildOptions(rows, "location_name");
                $location.html(opts).prop("disabled", false);

                if (preselectLocations && Array.isArray(preselectLocations) && preselectLocations.length > 0) {
                    $location.val(preselectLocations);
                }

                $location.trigger("change.select2");
                
                // Update display after loading
                updateAllDisplay($location);
                
                if (done) done(null);
            });
        }

        // Set initial disabled states
        function updateDisabledStates() {
            var AT = $accountType.val();
            var hasAccountType = (AT && AT.length > 0);
            var selectedRegions = $region.val() || [];
            var selectedClusters = $cluster.val() || [];

            if (!hasAccountType) {
                $region.prop("disabled", true);
            } else {
                $region.prop("disabled", false);
            }

            if (!selectedRegions || selectedRegions.length === 0) {
                $cluster.prop("disabled", true).html('');
            }

            if (!selectedClusters || selectedClusters.length === 0) {
                $location.prop("disabled", true).html('');
            }

            if ($region.data('select2')) $region.trigger("change.select2");
            if ($cluster.data('select2')) $cluster.trigger("change.select2");
            if ($location.data('select2')) $location.trigger("change.select2");
        }

        // Attach handlers with select2:select event to intercept before value is set
        $accountType.on("select2:select", function (e) {
            if (e.params.data.id === '__ALL__') {
                e.preventDefault();
                var allOptions = [];
                $(this).find('option').each(function() {
                    var val = $(this).val();
                    if (val && val !== '__ALL__') {
                        allOptions.push(val);
                    }
                });
                $(this).val(allOptions).trigger('change');
                $region.prop("disabled", false);
            }
        });

        $accountType.off("change.hse").on("change.hse", function () {
            var accountType = $(this).val() || [];
            
            if (accountType && accountType.length > 0) {
                $region.prop("disabled", false);
            } else {
                $region.prop("disabled", true).val(null).trigger("change");
                $cluster.prop("disabled", true).val(null).html('').trigger("change");
                $location.prop("disabled", true).val(null).html('').trigger("change");
            }
        });

        $region.on("select2:select", function (e) {
            if (e.params.data.id === '__ALL__') {
                e.preventDefault();
                var allOptions = [];
                $(this).find('option').each(function() {
                    var val = $(this).val();
                    if (val && val !== '__ALL__') {
                        allOptions.push(val);
                    }
                });
                $(this).val(allOptions).trigger('change');
            }
        });

        $region.off("change.hse").on("change.hse", function () {
            var selectedRegions = $(this).val() || [];
            
            if (selectedRegions.length > 0) {
                loadClusters(selectedRegions, null);
            } else {
                $cluster.prop("disabled", true).val(null).html('').trigger("change");
                $location.prop("disabled", true).val(null).html('').trigger("change");
            }
        });

        $cluster.on("select2:select", function (e) {
            if (e.params.data.id === '__ALL__') {
                e.preventDefault();
                var allOptions = [];
                $(this).find('option').each(function() {
                    var val = $(this).val();
                    if (val && val !== '__ALL__') {
                        allOptions.push(val);
                    }
                });
                $(this).val(allOptions).trigger('change');
            }
        });

        $cluster.off("change.hse").on("change.hse", function () {
            var selectedClusters = $(this).val() || [];
            var selectedRegions = $region.val() || [];
            
            if (selectedClusters.length > 0) {
                loadLocations(selectedRegions, selectedClusters, null);
            } else {
                $location.prop("disabled", true).val(null).html('').trigger("change");
            }
        });
        
        $location.on("select2:select", function (e) {
            if (e.params.data.id === '__ALL__') {
                e.preventDefault();
                var allOptions = [];
                $(this).find('option').each(function() {
                    var val = $(this).val();
                    if (val && val !== '__ALL__') {
                        allOptions.push(val);
                    }
                });
                $(this).val(allOptions).trigger('change');
            }
        });

        $location.off("change.hse").on("change.hse", function () {
            // No special handling needed
        });

        // Initial state
        updateDisabledStates();

        // Initial preloads if needed
        if (preAccountType && preAccountType.length > 0 && preRegions && preRegions.length > 0) {
            loadClusters(preRegions, preClusters, function () {
                if (preClusters && preClusters.length > 0) {
                    setTimeout(function () {
                        loadLocations(preRegions, preClusters, preLocations);
                    }, 150);
                }
            });
        }
        
        // Update display for pre-selected values after page load
        setTimeout(function() {
            updateAllDisplay($accountType);
            updateAllDisplay($region);
            updateAllDisplay($cluster);
            updateAllDisplay($location);
        }, 800); // Increased timeout to ensure all AJAX loads complete
    } // bootstrap end

    // Global init function
    window.initHseNcFilters = function () {
        waitForJQuery(function (err, $) {
            if (err) {
                console.error("hse_nc_filters: jQuery not found");
                return;
            }
            bootstrap($);
        });
    };

    // Initial load
    waitForJQuery(function (err, $) {
        if (!err) {
            $(document).ready(function () {
                window.initHseNcFilters();
            });
        }
    });

    // MutationObserver to auto-reinit when DOM changes (SPA navigation)
    if (window.MutationObserver) {
        var observer = new MutationObserver(function (mutations) {
            if (document.getElementById("hse_filter_container")) {
                if (window.jQuery) {
                    var $region = window.jQuery("#flt_region");
                    if ($region && $region.length && !$region.data("select2") && !$region.hasClass("select2-hidden-accessible")) {
                        window.initHseNcFilters();
                    }
                }
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

})();
