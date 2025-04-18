$(function() {
	$( ".sign-form-btn" ).click(function(e) {
		$submit_btn = $(this);
		$submit_btn.attr({"disabled":true,"data-loading":true});
		$submit_btn.ajax_req(function(response) {
			if(response.success === true) {
				if(response.msg) {
					alertify.alert("",response.msg,function() {
						location.reload();
					});
				}else{
					location.reload();
				}
			}else{
				if(typeof(response.input_error) == "object") {
					$(response.input_error).each(function(i,v) {
						$(v.html_selector).addClass("is-invalid");
						$(v.html_selector+"_error_txt").html(v.error);
					});
				}
				if(response.msg) {
					alertify.alert("",response.msg);
				}
			}
			$submit_btn.attr({"disabled":false,"data-loading":false});
		});
		e.preventDefault();
	});
});