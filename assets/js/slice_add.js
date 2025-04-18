var data_slice_count;
var arrange_slice;
		function add_slice(obj) {
		var data_slice = $(obj).attr("data-slice");
		var slice_title = $(obj).attr("slice-title");
		var data_append = $(obj).attr("data-append");
		var data_more = $(obj).attr("data-more");
        if(typeof(data_append) == "undefined") {
			data_append = "append-slice-area";
		}
		data_slice_count = $(".slice-"+data_slice+"-area").length + 1;
        arrange_slice = $(".slice-area-elms").length + 1;
		var slice_area_elms = $("<div class='slice-area-elms slice-"+data_slice+"-area slice-"+data_slice+"-id-"+data_slice_count+"'><div class='slice-title'><h3>"+slice_title+"</h3><div class='plgn_cncl' data-remove='slice-"+data_slice+"-id-"+data_slice_count+"'></div></div></div>");
        var new_content_btn_add_text = "إضافة شريحة أخرى مثل هذه";
		

		//Start New slice input url
		
		if(data_slice == "file_input") {
			var slide_html = "<input type='file' name='book_urls[]'/>";
		}
		// End New slice input url
		


		//Start New slice element Research page
		
		if(data_slice == "research_page") {
			var slide_html = "<div class='sctn_frm_data'><input type='text' placeholder='أدخل العنوان هنا' name='research_title[]'/>"
			+"<div class='textarea_sctn'><textarea id='content-"+data_slice+"_"+data_slice_count+"' placeholder='أكتب تفاصيل المقال هنا' name='research_content[]'></textarea></div>";
		}
		// End New slice Research page
		
		
		//Start New slice element Image
		
		if(data_slice == "image") {
			var slide_html = "<div class='sctn_frm_data'>"
			+"<input type='text' name='slice_image_title[]' placeholder='عنوان الشريحة'/>"
			+"</div>"
			+"<div class='sctn_frm_data'>"
			+"<textarea name='slice_image_desc[]' id='content-"+data_slice+"_"+data_slice_count+"' placeholder='وصف الشريحة'></textarea>" 
			+"</div>"
			+"<div class='add_img_sctn no-padding'>"
			+"<div class='sctn_frm_data img-frm_drop'>"
			+"<div class='img-frm_drop_stl open-gImage slice_"+data_slice+"_n_"+data_slice_count+"' data-appear='slice_"+data_slice+"_n_"+data_slice_count+"' data-val='slice_"+data_slice+"_thumb_"+data_slice_count+"'>"
			+"<div> 410 * 700 </div>"
			+"<i class='fa fa-camera'></i>"
			+"<p>أنقر لإضافة صورة</p>"
			+"</div>"
			+"<input type='hidden' name='slice_image_img[]' value='' id='slice_"+data_slice+"_thumb_"+data_slice_count+"'/>"
			+"</div></div>";
		}
		// End New slice element Image
		
		
		// New slice element Video
		if(data_slice == "video") {
			var slide_html = "<div class='sctn_frm_data'><input type='text' name='slice_video_title[]' placeholder='عنوان الشريحة'/></div>"
			+ "<div class='sctn_frm_data'><textarea name='slice_video_desc[]' id='content-"+data_slice+"_"+data_slice_count+"' placeholder='وصف الشريحة'></textarea></div>"
			+ "<div class='sctn_frm_data'><textarea name='slice_video_link[]' placeholder='ألصق رابط الفيديو'></textarea></div>"
			+ '<div class="plat-area-embed">'
            + '<i class="fa fa-facebook" title="Facebook"></i>'
            + '<i class="fa fa-youtube" title="YouTube"></i>'
            + '<i class="fa fa-vine" title="Vine"></i>'
            + '<i class="fa fa-vimeo" title="Vimeo"></i>'
            + '<i class="fa fa-dailymotion" title="Dailymotion"></i>'
            + '<i class="fa fa-instagram" title="Instagram"></i>'
            + '<i class="fa fa-twitter" title="Twitter"></i>'
            + '<i class="fa fa-pinterest-p" title="Pinterest"></i>'
            + '<i class="fa fa-map-marker" title="Google Maps"></i>'
            + '<i class="fa fa-type-gif" title="Gif"></i>'
            + '<i class="fa fa-image" title="Image"></i>'
            + '<i class="fa fa-soundcloud" title="Soundcloud"></i>'
            + '<i class="fa fa-mixcloud" title="Mixcloud"></i>'
            + '<i class="fa fa-reddit" title="Reddit"></i>'
            + '<i class="fa fa-coubcom" title="Coub"></i>'
            + '<i class="fa fa-imgur" title="Imgur"></i>'
            + '<i class="fa fa-vidme" title="Vidme"></i>'
            + '<i class="fa fa-twitch" title="Twitch"></i>'
            + '<i class="fa fa-vk" title="VK"></i>'
            + '<i class="fa fa-odnoklassniki" title="Odnoklassniki"></i>'
            + '<i class="fa fa-google-plus" title="Google+"></i>'
            + '<i class="fa fa-giphy" title="Giphy"></i>'
            + '</div>';
		}
		// End New slice element Video
		
		
		// New slice element Song
		
		if(data_slice == "song") {
			var slide_html = "<div class='sctn_frm_data'><input type='text' name='slice_song_title[]' placeholder='عنوان الشريحة'/></div>"
			+ "<div class='sctn_frm_data'><input type='text' name='slice_song_link[]' placeholder='ألصق رابط الصوت'/></div>"
			+ '<div class="plat-area-embed">'
			+ '<i class="fa fa-soundcloud" title="Soundcloud"></i>'
			+ '</div>';
		}
		// End New slice element Song
		
		
		// New slice element Text
		
		if(data_slice == "text") {
			var slide_html = "<div class='sctn_frm_data'><input type='text' name='slice_text_title[]' placeholder='عنوان الشريحة'/></div>";
		}

		// End New slice element Text
		
		
		// New slice element Share
		if(data_slice == "share") {
			var slide_html = "<div class='sctn_frm_data'><input type='text' name='slice_share_title[]' placeholder='عنوان الشريحة'/></div>"
			+ "<div class='sctn_frm_data'><textarea name='slice_share_desc[]' id='content-"+data_slice+"_"+data_slice_count+"' placeholder='وصف الشريحة'></textarea></div>"
			+ "<div class='sctn_frm_data'><textarea name='slice_share_link[]' placeholder='ألصق رابط محتوى'></textarea></div>"
			+ '<div class="plat-area-embed">'
            + '<i class="fa fa-facebook" title="Facebook"></i>'
            + '<i class="fa fa-youtube" title="YouTube"></i>'
            + '<i class="fa fa-vine" title="Vine"></i>'
            + '<i class="fa fa-vimeo" title="Vimeo"></i>'
            + '<i class="fa fa-dailymotion" title="Dailymotion"></i>'
            + '<i class="fa fa-instagram" title="Instagram"></i>'
            + '<i class="fa fa-twitter" title="Twitter"></i>'
            + '<i class="fa fa-pinterest-p" title="Pinterest"></i>'
            + '<i class="fa fa-map-marker" title="Google Maps"></i>'
            + '<i class="fa fa-type-gif" title="Gif"></i>'
            + '<i class="fa fa-image" title="Image"></i>'
            + '<i class="fa fa-soundcloud" title="Soundcloud"></i>'
            + '<i class="fa fa-mixcloud" title="Mixcloud"></i>'
            + '<i class="fa fa-reddit" title="Reddit"></i>'
            + '<i class="fa fa-coubcom" title="Coub"></i>'
            + '<i class="fa fa-imgur" title="Imgur"></i>'
            + '<i class="fa fa-vidme" title="Vidme"></i>'
            + '<i class="fa fa-twitch" title="Twitch"></i>'
            + '<i class="fa fa-vk" title="VK"></i>'
            + '<i class="fa fa-odnoklassniki" title="Odnoklassniki"></i>'
            + '<i class="fa fa-google-plus" title="Google+"></i>'
            + '<i class="fa fa-giphy" title="Giphy"></i>'
            + '</div>';
		}
		// End New slice element Share
		
		
		// New slice element Maps
        if(data_slice == "map") {
			var slide_html = "<div class='sctn_frm_data'><input type='text' name='slice_map_title[]' placeholder='عنوان الشريحة'/></div>"
			+ "<div class='sctn_frm_data'><textarea name='slice_map_desc[]' id='content-"+data_slice+"_"+data_slice_count+"' placeholder='وصف الشريحة'></textarea></div>"
			+ "<div class='sctn_frm_data'><textarea name='slice_map_link[]' placeholder='ألصق رابط الخريطة هنا'></textarea></div>"	
            + "<div class='slice-notice'><span>جوجل , بنج</span></div>"			
		}
		// End New slice element Maps
		
		// New slice element Vote
		if(data_slice == "vote") {
            var slide_html = "<div class='sctn_frm_data'><input type='text' name='vote["+data_slice_count+"][title]' placeholder='عنوان الشريحة'/></div>"
			+ "<div class='sctn_frm_data'><textarea name='vote["+data_slice_count+"][description]' id='content-"+data_slice+"_"+data_slice_count+"' placeholder='وصف الشريحة'></textarea></div>"
			+ "<div class='add_img_sctn no-padding'>"
			+ "<div class='sctn_frm_data img-frm_drop'>"
			+ "<div class='img-frm_drop_stl open-gImage slice_image_"+data_slice+"_n_"+data_slice_count+"' data-appear='slice_image_"+data_slice+"_n_"+data_slice_count+"' data-val='slide_img_thumb_"+data_slice+"_n_"+data_slice_count+"'>"
			+ "<div> 410 * 700 </div>"
			+ "<i class='fa fa-camera'></i><p>أنقر لإضافة صورة</p>"
			+ "</div></div>"
			+ "<input type='hidden' name='vote["+data_slice_count+"][image]' value='' id='slide_img_thumb_"+data_slice+"_n_"+data_slice_count+"'/>"
			+ "</div>"
			+ "<div class='sctn_frm_data slice-vote-area-s-"+data_slice_count+"'>"
		+ "<div class='vote-choice-1 vote-choice-a'><input type='text' name='vote["+data_slice_count+"][vote_choice][]' placeholder='خيار 1'/></div>"
			+ "<div class='vote-choice-2 vote-choice-a'><input type='text' name='vote["+data_slice_count+"][vote_choice][]' placeholder='خيار 2'/></div>"
			+ "</div>"
			+ "<div class='add-new-vote-choice'><button class='new_vote_choice' data-choice-for='"+data_slice_count+"' onclick='return false;'><em><i class='fa fa-plus'></i></em>&nbsp;إضافة إستطلاع</button></div>";
		}
            
        // New slice element quiz
        
        if(data_slice == "quiz") {
             var slide_html = "<div class='sctn_frm_data'><input type='text' name='quiz["+data_slice_count+"][title]' placeholder='عنوان الشريحة'/></div>"
			+ "<div class='sctn_frm_data'><textarea name='quiz["+data_slice_count+"][description]' id='content-"+data_slice+"_"+data_slice_count+"' placeholder='وصف الشريحة'></textarea></div>"
             +"<div id='quiz-questions-area' class='quiz-question-area-"+data_slice_count+"'>"
            +"<div class='quiz-question quiz-question-1'>"
			+ "<div class='add_img_sctn no-padding'><div class='sctn_frm_data img-frm_drop'><div class='img-frm_drop_stl open-gImage slice_"+data_slice+"_n_"+data_slice_count+"' data-appear='slice_"+data_slice+"_n_"+data_slice_count+"' data-val='slide_"+data_slice+"_thumb_"+data_slice_count+"'><div> 410 * 700 </div><i class='fa fa-camera'></i><p>أنقر لإضافة صورة</p></div></div></div><input type='hidden' name='quiz["+data_slice_count+"][question][1][image]' id='slice_"+data_slice+"_thumb_"+data_slice_count+"'/>"
			+ "<div class='sctn_frm_data slice-quiz-area-s-1-"+data_slice_count+"'>"
			+ "<div class='quiz-choice-1 quiz-choice-a'><div class='quiz-inp-elm'><input type='text' name='quiz["+data_slice_count+"][question][1][choices][choice_1][text]' placeholder='خيار 1'/></div><div class='quiz-inp-elm-check'><span>صحيح</span><input type='checkbox' class='check_for_correct' data-hidden='q"+data_slice_count+"_q1_c1'/><input type='hidden' value='false' name='quiz["+data_slice_count+"][question][1][choices][choice_1][is]' class='q"+data_slice_count+"_q1_c1_hidden'/></div></div>"
			+ "<div class='quiz-choice-2 quiz-choice-a'><div class='quiz-inp-elm'><input type='text' name='quiz["+data_slice_count+"][question][1][choices][choice_2][text]' placeholder='خيار 2'/></div><div class='quiz-inp-elm-check'><span>صحيح</span><input type='checkbox' class='check_for_correct' data-hidden='q"+data_slice_count+"_q1_c2'/><input type='hidden' name='quiz["+data_slice_count+"][question][1][choices][choice_2][is]' value='false' class='q"+data_slice_count+"_q1_c2_hidden'/></div></div>"
			+"</div>"
            +"<button class='new_quiz_choice' data-quiz-for='"+data_slice_count+"' data-choice-for='1' onclick='return false;'><em><i class='fa fa-plus'></i></em>&nbsp;إضافة جواب</button>"
            +"</div>"
            +"</div>"
			+ "<div class='add-new-quiz-choice'>"
            +"<button class='new_quiz_question' data-quiz-for='"+data_slice_count+"' onclick='return false;'><em><i class='fa fa-plus'></i></em>&nbsp;إضافة سؤال</button>"
    
             +"</div>";
		}           
        
            
        // End slice elemnt quiz
        slide_html += "<input type='hidden' name='arrange_"+data_slice+"[]' value='"+arrange_slice+"'/>";
		if(typeof(data_more) == "undefined") {
		slide_html += "<button onclick='return false;' class='slice-add-btn-isd' data-slice='"+data_slice+"' slice-title='"+slice_title+"'><i class='fa fa-plus'></i>&nbsp;"+new_content_btn_add_text+"</button>";
		}
		slice_area_elms.append(slide_html);		
		$("#"+data_append+"").prepend(slice_area_elms);
		$(".Slices_choice").slideUp();

		// Apply tinymce Editor to textarea elements
        
        tinymce.EditorManager.execCommand('mceAddEditor',true, "content-"+data_slice+"_"+data_slice_count+"");
}