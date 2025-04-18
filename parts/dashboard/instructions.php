<?php
/**
 * instructions.php
 * User dashboard page
 */
 $instructions = @json_decode(get_option("instructions"));
?>
<div class="user-dashboard-instructions position-relative">
	<div class="my-5"></div>
    <div class="">
        <?php echo $instructions->{current_lang()}; ?>
    </div>
</div>