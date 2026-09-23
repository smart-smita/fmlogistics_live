commonjs = {
	ajaxDataCallFuntion : function(url, loading_div_class, div_data,
			data_to_send) {
		var htmlData = " ";
		$
				.ajax({
					url : url,
					async : true,
					data : data_to_send,
					type : 'post',
					statusCode : {
						500 : function() {
							error_notifications_massage("<b>Server Error Contact To Unitglo Solutions.</b>");
						}
					},
					beforeSend : function() {
						htmlData = "" + $(loading_div_class).html();
						$(loading_div_class)
								.html(
										'<div class="loding"><div class="spinner-wrapper"><div class="rotator"><div class="inner-spin"></div><div class="inner-spin"></div></div></div></div>');
					},
					success : function(returnHtml) {
						var message = "";
						try {
							$(div_data).html(returnHtml);
						} catch (err) {
							error_notifications_massage("Error On Data : "
									+ err);
						}
					},
					complete : function(data) {
						$(loading_div_class).html("" + htmlData);
					}
				});

	}
}