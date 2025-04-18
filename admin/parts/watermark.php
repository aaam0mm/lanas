
    <div class="dash-part-form">
        <?php
        $watemark = @unserialize(get_option('watermark'));
        $watermark_image = $watemark["image"] ?? "";
        $watermark_pos_x = $watemark["pos_x"] ?? "";
        $watermark_pos_y = $watemark["pos_y"] ?? "";
        $watermark_display = $watemark["display"] ?? "";
        $watermark_opacity = $watemark["opacity"] ?? "";
        $watermark_visible = $watermark["visible"] ?? "on";
        $watermark_w = $watermark["w"] ?? 20;
        $watermark_h = $watermark["h"] ?? 20;
	     ?>
        <div class="full-width line-elm-flex">
            <div class="half-width watermar-settings">
            <form method="post" id="form_data">
    			<div class="full-width input-label-noline">
    				<div class="up-upload-input">
    					<input type="hidden" id="watermark_image" name="watermark[image]" value="<?php esc_html($watermark_image); ?>"/>
    					<button class="upload-btn" data-input="#watermark_image"><i class="fas fa-upload"></i>&nbsp;<span>تصفح ...</span></button>
    				</div>
    			</div>
				<label for="copy">ظهور العلامة</label>
				<div class="full-width line-elm-flex">
    				<div class="col-s-setting">
    					<input name="watermark[visible]" value="on" id="watermark_visible" class="ios-toggle" type="checkbox" <?php checked_val($watermark_visible,"on"); ?> />
    					<label for="watermark_visible" class="checkbox-label"></label>
    				</div>													
				</div>
                <input type="hidden" name="watermark[pos_x]" class="watermark_pos_x" value="<?php esc_html($watermark_pos_x); ?>"/> 
                <input type="hidden" name="watermark[pos_y]" class="watermark_pos_y" value="<?php esc_html($watermark_pos_y); ?>"/>
                <input type="hidden" name="watermark[w]" class="watermark_w" value="<?php esc_html($watermark_w); ?>"/>
                <input type="hidden" name="watermark[h]" class="watermark_h" value="<?php esc_html($watermark_w); ?>"/>
				<input type="hidden" name="watermark[opacity]" value="<?php esc_html($watermark_opacity); ?>" class="opacity_inp_h"/>
                <input type="hidden" name="method" value="watermark"/>
                <button id="submit_form" class="saveData">أضف</button>
            </form>
        </div>
        <div class="half-width">
          
           <div id="watermark-img-default" class="watermark-img-default">
                <img src="img/watermark-placeholder.jpg" alt="image_watermark"/>
                <div class="watermark-image-e" id="draggable-watermark-e" style="top:<?php esc_html($watermark_pos_y); ?>%; left:<?php esc_html($watermark_pos_x); ?>%; width:<?php esc_html($watermark_w); ?>%; height:<?php esc_html($watermark_h); ?>%;">
					<img src="<?php echo get_thumb($watermark_image,null); ?>" class="image_watermark" id="watermark_image_prv" style="opacity:<?php esc_html($watermark_opacity); ?>;"/>
                </div>
           </div>
           
            <div class="full-width watermark-opacity">
                <input type="range" value="<?php esc_html($watermark_opacity); ?>" min="0" step="0.1" max="1" class="opacity_setting"/>
            </div>
        </div>
        </div>
        <script>
		$( "#draggable-watermark-e" ).draggable({
			containment: "#watermark-img-default", cursor: "move", scroll: false,
			drag: function(event) {
				//var o = $(this).offset();
				var l_i = $(this).position().left;
				var t_i = $(this).position().top;
				var l = ( 100 * parseFloat(l_i / parseFloat($(this).parent().width())) );
				var t = ( 100 * parseFloat(t_i / parseFloat($(this).parent().height())) );
				

				l = Math.round(l);
				t = Math.round(t);
				//var p = $(this).position();
				$('.watermark_pos_x').val(l);
				$('.watermark_pos_y').val(t);
			}
		});

        $(document).ready(function() {
			$(".watermark-image-e").resizable({
				option : "grid",
				resize: function( event) {
					var w_i = $(this).width();
					var h_i = $(this).height();
					var w = ( 100 * parseFloat( w_i / $(this).parent().width()) );
					var h = ( 100 *  parseFloat( h_i / $(this).parent().height()) );  
					w = Math.round(w)
					h = Math.round(h)
					$('.watermark_w').val(w);
					$('.watermark_h').val(h);
				}
			});
            $(".opacity_setting").on("change",function() {
                var opacity_val = $(this).val();
                $("#watermark_image_prv").css({ opacity : ""+opacity_val+"" }); 
				$(".opacity_inp_h").val(opacity_val);
            });
            $(".width_setting").on("change",function() {
                var width_val = $(this).val();
            //    $(".watermark-image-e").css({ width : ""+width_val+"%" }); 
            });

        });
        </script>
    </div>
