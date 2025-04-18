$(function() {
	$("#ad_type_select").on("change",function() {
		if($(this).val() == "sell") {
			$(".sell-ad-type-info").show(500);
		}else{
			$(".sell-ad-type-info").hide(500);
		}
		if($(this).val() == "auction") {
			$(".auction-ad-type-info").show(500);
		}else{
			$(".auction-ad-type-info").hide(500);
		}
	});
	
	$( ".add-form-btn" ).click(function(e) {
		tinyMCE.triggerSave(); 
		$submit_btn = $(this);
		$submit_btn.attr({"disabled":true,"data-loading":true});
		$submit_btn.ajax_req(function(response) {
			$submit_btn.attr({"disabled":false,"data-loading":false});
			if(response.success == true) {
				if(typeof(response.ad_url) != "undefined") {
					window.location.href = response.ad_url;
				}
			}else{
				if(typeof(response.input_error) == "object") {
					$(response.input_error).each(function(i,v) {
						$(v.html_selector).addClass("is-invalid");
						$(v.html_selector+"_error_txt").html(v.error);
					});
				}
				if(response.error) {
					alertify.alert("خطأ",response.error);
				}
				if(response.login_modal === true) {
					$("#signModal").modal();
				}
			}			
		});
		e.preventDefault();
	});
	
	$( "#ad_image" ).on("change",function() {
		$(this).upload_req(function(response) {
			$(".ad-images-preview").append( '<div class="image-ad-preview col-sm-2" data-id="'+response.file_id+'"><a href="#" class="btn remove-image-preview btn-transparent btn-sm"><i class="fas fa-times text-danger"></i></a><img src="'+response.file_url+'" class="img-fluid rounded"><input type="hidden" name="ad_gallery[]" value="'+response.file_id+'"/></div>' );
			
			if(isNaN(parseInt($("#ad_main_image").val()))) {
				$(".image-ad-preview").children("img").addClass("ad-main-image-s");
				$("#ad_main_image").val(response.file_id);
			}
	},"upload_image","ad_image");
	}); 
	
	$( "#ad_country_select" ).on("change",function() {
		$country_code = $(this).val();
		$.get("htmlLoader.php?path=cities-select-option&country_code="+$country_code,function(data) {
			$("#ad_city_select").html(data);
		});
	});

	$( "#ad_department_select" ).on("change",function() {
		$department_id = $(this).val();
		$.get("htmlLoader.php?path=ad_properties&depart_id="+$department_id,function(data) {
			$("#ad_properties").html(data);
		});
	});
	
	$( document ).on("click",".remove-image-preview",function(e) {
		$this = $(this);
		$this.parent( ".image-ad-preview" ).remove();
		e.preventDefault();
	});
	
	$(".ad-in").click(function() {
		$(".choose-ad-in").slideUp(500);
		$("#add-form form").slideDown(500);
		if($(this).data("type") == "store") {
			$("form").append( '<input type="hidden" name="ad_in" value="store"/>' );
			$(".ad-store-inp").show();
		}
	});
	
	$(document).on("click",".signup-link-modal",function(e) {
		$(".form-signin-modal").hide();
		$(".form-signup-modal").show();
		e.preventDefault();
	});	
	
	$(document).on("click",".signin-link-modal",function(e) {
		$(".form-signin-modal").show();
		$(".form-signup-modal").hide();
		e.preventDefault();
	});	
	
	$(document).on("click",".signin-modal",function(e) {
		$(this).ajax_req(function(response) {
			
			if(response.success === true) {
				$("#add_ad").click();
				$("#signModal").modal('hide');
			}else{
				$(response.input_error).each(function(i,v) {
					$(v.html_selector+"_signin").addClass("is-invalid");
					$(v.html_selector+"_signin_error_txt").html(v.error);
				});				
			}
			
		});
		e.preventDefault();
	});
	
	$(document).on("click",".signup-modal",function(e) {
		$(this).ajax_req(function(response) {
			
			if(response.success === true) {
				$("#add_ad").click();
				$("#signModal").modal('hide');
			}else{
				if(typeof(response.input_error) == "object") {
					$(response.input_error).each(function(i,v) {
						$(v.html_selector+"_signup").addClass("is-invalid");
						$(v.html_selector+"_signup_error_txt").html(v.error);
					});		
				}				
			}
			
		});
		e.preventDefault();
	});	
	
	$( document ).on("click",".image-ad-preview",function() {
		$(".image-ad-preview img").removeClass("ad-main-image-s");
		$(this).children("img").addClass("ad-main-image-s");
		$("#ad_main_image").val($(this).data("id"));
	});	
});
