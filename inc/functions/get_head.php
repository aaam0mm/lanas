<?php
if(!function_exists("get_head")) {
    function get_head() {
		$v = "";
		?>
		
		<base href="/"/>
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"> 
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Noto+Naskh+Arabic:wght@400..700&display=swap" rel="stylesheet">
		<link rel="icon" href="<?php esc_html(get_thumb( get_settings("site_favicon"),null )); ?>">
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-138258366-1"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
        
          gtag('config', 'UA-138258366-1');
        </script>
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
        <script>
          (adsbygoogle = window.adsbygoogle || []).push({
            google_ad_client: "ca-pub-3476000985436829",
            enable_page_level_ads: true
          });
        </script>
		<?php if(is_ltr()): ?>
		<!-- BOOTSTRAP CSS -->
		<link href="<?php echo siteurl(); ?>/assets/lib/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
		<!-- DIRECTION CSS -->
		<link href="<?php echo siteurl(); ?>/assets/css/ltr.css" rel="stylesheet" type="text/css"/>
		<?php else: ?>
		<!-- BOOTSTRAP RTL CSS -->
		<link href="<?php echo siteurl(); ?>/assets/lib/bootstrap/css/rtl/bootstrap.min.css" rel="stylesheet" type="text/css"/>
		<!-- DIRECTION CSS -->
		<link href="<?php echo siteurl(); ?>/assets/css/rtl.css" rel="stylesheet" type="text/css"/>		
		<?php endif; ?>
		<!-- DEFAULT CSS -->
		<link href="<?php echo siteurl(); ?>/assets/css/default.css" rel="stylesheet" type="text/css"/>	
		<!-- SITE CSS -->
		<link href="<?php echo siteurl(); ?>/assets/css/style.css" rel="stylesheet" type="text/css"/>
		<link href="<?php echo siteurl(); ?>/assets/css/emojionearea.min.css" rel="stylesheet" type="text/css"/>
		<!-- FONT AWESOME CSS -->
		<link href="<?php echo siteurl(); ?>/assets/lib/fontawesome/css/all.css" rel="stylesheet" type="text/css"/>
		<link href="<?php echo siteurl(); ?>/assets/lib/alertify/css/themes/bootstrap.rtl.min.css" rel="stylesheet" type="text/css"/>	
		<!-- ANIMATE CSS -->
		<link href="<?php echo siteurl(); ?>/assets/css/animate.css" rel="stylesheet" type="text/css"/>
		<!-- ANIMATE CSS -->
		<?php global_js(); ?>
        <!-- HTML 5 shiv -->
        <!--[if lt IE 9]>
        <script src="<?php echo siteurl(); ?>/assets/js/html5shiv.min.js?v="></script>
        <![endif]-->
        <style>
        /*
            @font-face {
              font-family: 'Arvo-fallback';
              font-display: fallback;
              src: url(https://fonts.gstatic.com/s/arvo/v9/rC7kKhY-eUDY-ucISTIf5PesZW2xOQ-xsNqO47m55DA.woff2) format('woff2');
            }   */ 
        </style>
		<?php
	}
}