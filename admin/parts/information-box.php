<?php
$instructions = get_option("instructions");
$points_bag = get_option("points-bag");
?>
	<div class="dash-part-form">
        <div class="r7-width">
        <form action="" method="POST" id="form_data">
            <label>تعليمات</label>
            <?php multi_input_languages("instructions","textarea",json_decode($instructions)); ?>
            <div class="clear"></div>
            <label>حقيبة النقاط</label>
            <?php multi_input_languages("points-bag","textarea",json_decode($points_bag)); ?>
			<button id="submit_form" class="saveData">أضف</button>	
			<input type="hidden" name="method" value="information_box"/>
        </form>
        </div>
    </div>