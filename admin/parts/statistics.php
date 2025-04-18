<?php
$get_dash_os_analytics = get_analytics("post_views","os");
$get_dash_browser_analytics = get_analytics("post_views","browser");
$get_dash_visits_analytics = get_analytics("post_views","site_visitors");
$get_dash_onlineusers = get_analytics("post_views","online_users");
$get_dash_Countusers = get_analytics("post_views","count_users");
//$get_posts_analytics = get_analytics("post_views","posts_analytics");
//echo "<pre>"; print_r($get_posts_analytics); echo "</pre>";
$os_in = json_encode(array_keys($get_dash_os_analytics));
$os_in_visits = json_encode(array_values($get_dash_os_analytics));
$browser_in = json_encode(array_keys($get_dash_browser_analytics));
$browser_in_visits = json_encode(array_values($get_dash_browser_analytics));

?>
	<div class="dash-part-form">
    <div id="statistics-page-carts">
        <div class="statistics-page-carts line-elm-flex">
            <div class="stat-cart stat-cart-1 r25-width">
                <div class="stat-cart-icon pull-left"><img src="<?php echo siteurl(); ?>/admin/img/user.png"/></div>
                <div class="stat-cart-info pull-right">
                    <ul>
                        <li><span>الزوار</span> : <?php esc_html($get_dash_visits_analytics["visits"] ?? 0); ?></li>
                        <li><span>الأعضاء (متواجدين)</span> : <?php esc_html($get_dash_onlineusers["online_users"] ?? 0); ?></li>
                    </ul>
                    <div class="stat-info-box-txt-l">
                        <span>أعضاء وزوار الموقع</span>
                    </div>
                </div>
            </div>
            <div class="stat-cart stat-cart-2 r25-width">
                <div class="stat-cart-icon pull-left"><img src="<?php echo siteurl(); ?>/admin/img/contract.png"/></div>
                <div class="stat-cart-info pull-right">
                    <ul>
                        <li>
							<span>
							<i class="fa fa-check-circle" style="color:rgb(115, 189, 64)" aria-hidden="true"></i>
							 : <?php esc_html($get_posts_analytics["trusted_posts"] ?? 0); ?>
							</span>
						</li>
                        <li>
							<span>
								<i class="fa fa-check-circle" aria-hidden="true"></i>
								 : <?php esc_html($get_posts_analytics["untrusted_posts"] ?? 0); ?>
							</span>
						</li>
                    </ul>
                    <div class="stat-info-box-txt-l">
                        <span>كل المواضيع  : <?php echo @$get_posts_analytics['untrusted_posts'] + @$get_posts_analytics['trusted_posts']; ?></span>
                    </div>
                </div>
            </div>
            <div class="stat-cart stat-cart-3 r25-width">
                <div class="stat-cart-icon pull-left"><img src="<?php echo siteurl(); ?>/admin/img/lock.png"/></div>
                <div class="stat-cart-info pull-right" style="width:60%;">
                    <h2><?php esc_html($get_posts_analytics["pending_posts"] ?? 0); ?></h2>
                    <div class="stat-info-box-txt-l">
                        <span style="white-space: nowrap;">مواضيع في إنتظار الموافقة</span>
                    </div>
                </div>
            </div>
            <div class="stat-cart stat-cart-4 r25-width">
                <div class="stat-cart-icon pull-left"><img src="<?php echo siteurl(); ?>/admin/img/users.png"/></div>
                <div class="stat-cart-info pull-right">
                    <ul>
                        <li><span>فعال</span> : <?php esc_html($get_dash_Countusers["active"] ?? 0); ?></li>
                        <li><span>محظور</span> : <?php esc_html($get_dash_Countusers["blocked"] ?? 0); ?></li>
                    </ul>
                    <div class="stat-info-box-txt-l">
                        <span>كل الأعضاء : <?php esc_html($get_dash_Countusers["all_users"] ?? 0); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="clear"></div>
    <!-- START SITE Statistics -->
    <div class="full-width">
        <div class="charts-stat-box">
        <div class="stat-box-title" style="overflow:hidden;">
			<h3 class="pull-right">إحصائيات</h3>
			<div class="pull-left r25-width">
				<select class="statistics_dur">
					<option value="today">اليوم</option>
					<option value="month">الشهر</option>
					<option value="year">السنة</option>
				</select>
			</div>
		</div>
        <div class="stat-box-content site-statistics">
        
        </div>
        </div>        
    </div>
    <!-- END SITE Statistics -->
    <div class="clear"></div>
    <!-- START NEW POSTS Statistics -->
    
    <div class="full-width posts-new-stat">
        <div class="charts-stat-box">
		<div class="stat-box-title" style="overflow:hidden;">
			<div class="pull-right"><h3>جديد المواضيع</h3></div>
			<div class="pull-left r25-width">
				<select name="filter_status" class="filter_post_status">
					<option value="">عرض الجميع</option>
					<option value="trusted">مواضيع موثوقة</option>
					<option value="untrusted">مواضيع غير موثوقة</option>
				</select> 
			</div>
		</div>
        <div class="stat-box-content posts-new-data"></div>
        </div>
    </div>
    
    <!-- END NEW POSTS Statistics -->
    
    <div class="clear"></div>
    
    <!-- START Visits by country Statistics -->
    
    <div class="full-width">
        <div class="charts-stat-box">
		<div class="stat-box-title" style="overflow:hidden;">
			<div class="pull-right"><h3>زوار حسب الدولة</h3></div>
			<div class="pull-left r25-width">
				<select name="filter_status" class="country_v_dur">
					<option value="today">اليوم</option>
					<option value="month">الشهر</option>
					<option value="year">السنة</option>
				</select> 
			</div>
		</div>		
        <div class="stat-box-content visitors-byCountry vst_cntry">
        
        </div>
        </div>
    </div>
    
    <!-- END Visits by country Statistics -->
    
    <div class="clear"></div>
    
    <!-- START POSTS Statistics -->
    
    <div class="full-width posts-new-stat">
        <div class="charts-stat-box">
        <div class="stat-box-title" style="overflow:hidden;">
			<h3 class="pull-right">إحصائيات المواضيع</h3>
			<div class="pull-left r25-width">
				<select name="filter_users" class="filter_posts_statistics">
					<option value="" selected="">عرض الكل</option>
					<option value="top_shared">أكثر مشاركة</option>
					<option value="top_liked">أكثر اعجابا</option>
					<option value="top_voted">أكثر تقيما</option>
				</select> 
			</div>		
		</div>
        <div class="stat-box-content posts-data">
        
        </div>
    </div>
    
    <!-- END POSTS Statistics -->
    
    <div class="clear"></div>
    
    <!-- START Browsers & OS Statistics -->
    
    <div class="full-width">
       <div class="line-elm-flex">
        <div class="half-width">
            <div class="charts-stat-box">
                <div class="stat-box-title"><h3>إستخدام نظام التشغيل</h3></div>
                <div class="stat-box-content os-chart-data">
                    <canvas id="os-chart-data" width="400" height="400"></canvas>
                </div>
            </div>
        </div>
        <div class="half-width">
            <div class="charts-stat-box">
                <div class="stat-box-title"><h3>إستخدام المتصفح</h3></div>
                <div class="stat-box-content browsers-chart-data">
                <canvas id="browsers-chart-data" width="400" height="400"></canvas>
                </div>
        </div>
        </div>
    </div>
    </div>
    
    <!-- END Browsers & OS Statistics -->
	<div class="clear"></div>
	<!-- Start users Statistics -->
    <div class="full-width">
        <div class="charts-stat-box">
        <div class="stat-box-title">
			<div style="overflow:hidden">
			<div class="pull-right"><h3>إحصائيات الأعضاء</h3></div>
			<div class="pull-left r25-width">
				<select name="filter_users" class="filter_users_statistics">
					<option value="top_points">كتاب لديهم أكثر نقاط</option>
					<option value="active_today">كتاب الموجودين اليوم</option>
					<option value="active">كتاب نشيطين</option>
					<option value="top_posts">كتاب لديهم أكثر المواضيع</option>
				</select> 
			</div>
			</div>
		</div>
        <div class="stat-box-content users_statistics">
        
        </div>
        </div>
    </div>	
	<!-- End users Statistics -->
    </div>
<script>

</script>
<script>
$(document).ready(function() {	
	var data_req = [
			{params : {path : "dash-statistics", section : "visitors"}, elm : ".visitors-byCountry"},
			{params : {path : "dash-statistics", section : "newPosts"}, elm : ".posts-new-data"},
			{params : {path : "dash-statistics", section : "postsData"}, elm : ".posts-data"},
			{params : {path : "dash-statistics", section : "statistics"}, elm : ".site-statistics"},
			{params : {path : "dash-statistics", section : "users_statistics"}, elm : '.users_statistics'}
		];
	$.each(data_req,function(key,value) {
		$.get(gbj.siteurl+"/ajax/ajax-html.php",value.params,function(data) {
			$(value.elm).html(data);
		});
	});
	$(".filter_post_status").on("change",function() {
		var filter_data = data_req[1].params.filter_by = $(this).val();
		$.get(gbj.siteurl+"/ajax/ajax-html.php",data_req[1].params,function(data) {
			$(data_req[1].elm).html(data);
		});
	});
	
	$(".filter_users_statistics").on("change",function() {
		var filter_data = data_req[4].params.filter_by = $(this).val();
		$.get(gbj.siteurl+"/ajax/ajax-html.php",data_req[4].params,function(data) {
			$(data_req[4].elm).html(data);
		});
	});
	
	$(".filter_posts_statistics").on("change",function() {
		var filter_data = data_req[2].params.filter_by = $(this).val();
		$.get(gbj.siteurl+"/ajax/ajax-html.php",data_req[2].params,function(data) {
			$(data_req[2].elm).html(data);
		});
	});	
	
	$(".statistics_dur").on("change",function() {
		var filter_data = data_req[3].params.duration = $(this).val();
		$.get(gbj.siteurl+"/ajax/ajax-html.php",data_req[3].params,function(data) {
			$(data_req[3].elm).html(data);
		});
	});	
	
	$(".country_v_dur").on("change",function() {
		var filter_data = data_req[0].params.duration = $(this).val();
		$.get(gbj.siteurl+"/ajax/ajax-html.php",data_req[0].params,function(data) {
			$(data_req[0].elm).html(data);
		});
	});	
    var browsers_chart_data = $("#browsers-chart-data");
    var os_chart_data = $("#os-chart-data");
    
    var osChart = new Chart(os_chart_data, {
    type: 'doughnut',
    data: {
        labels: <?php echo $os_in; ?>,
        datasets: [{
            label: '# of Votes',
            data: <?php echo $os_in_visits; ?>,
            backgroundColor: [
                '#27457c',
                '#437f97',
                '#849324',
                '#ffb30f',
                '#fd151b',
                '#000000'
            ],
            borderWidth: 1
        }]
    },
    });
    
    var browsersChart = new Chart(browsers_chart_data, {
    type: 'doughnut',
    data: {
        labels: <?php echo $browser_in; ?>,
        datasets: [{
            label: '# of Votes',
            data: <?php echo $browser_in_visits; ?>,
            backgroundColor: [
                '#e4572e',
                '#76b041',
                '#17bebb',
                '#ef626c',
                '#2bd9fe',
                '#000000'
            ],
            borderWidth: 1
        }]
    },
    });
});    
</script>