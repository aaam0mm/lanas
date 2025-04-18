<?php
require_once 'init.php';

if(is_login_in()) {
	exit;
}

$platform = $_GET["platform"] ?? null;

$config_facebook = [
	'callback' => siteurl().'/signin.php?platform='.$platform,
	'scope' => 'email',
    'keys' => [ 
		'id' => '555928628558574', 
		'secret' => '50453d177b67149b177a31dfd25d19e5' 
	]
];

$config_twitter = [
	'callback' => siteurl().'/signin.php?platform='.$platform,
	'scope' => 'user_gender,email',
    'keys' => [ 
		'id' => 'bSSf3QG5VAy3RC4pIHv3O8kaQ', 
		'secret' => '8iGflRI65a7Y4LeREyAtBJRFczmJ0E15rjY4RTP6qULraMtobP' 
	]
];

$social_login_error = '';

if(!empty($platform) && in_array($platform, SOCIAL_LOGIN)) {
	try {

		if($platform == "Facebook") {
			$adapter = new Hybridauth\Provider\Facebook($config_facebook);
		}elseif($platform == "Twitter") {
			$adapter = new Hybridauth\Provider\Twitter($config_twitter);
		}

		//Attempt to authenticate the user with Facebook
		$adapter->authenticate( $platform );
	
		//Returns a boolean of whether the user is connected
		$isConnected = $adapter->isConnected();
	
		//Retrieve the user's profile
		$userProfile = $adapter->getUserProfile();
		$userProfile = [
			"platform" => $platform,
			"identifier" => $userProfile->identifier,
			"displayName" => $userProfile->displayName,
			"gender" => $userProfile->gender,
			"email" => $userProfile->email
		];
		$adapter->disconnect();
	    
	    $is_already_sign = is_user_email_exist($userProfile["email"]);
	    
	    $sign_user = user_social_media_signin($userProfile);
		if($sign_user) {
		    set_user_cookie($sign_user["user_login_identify"]);
		    if($is_already_sign) {
		        header("location:".siteurl());
		    }else{
		        header("location:".siteurl()."/dashboard/instructions");
		    }
		}else{
			$social_login_error = "<p>"._t('حدث خطأ المرجو إعادة المحاولة مرة أخرى')."</p>";
		}
	
	}
	catch(\Exception $e){
		$social_login_error = "<p>"._t('حدث خطأ المرجو إعادة المحاولة مرة أخرى')."</p>" . "<em>".$e->getMessage()."</em>";
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php get_head(); ?>
		<title></title>
	</head>
	<body class="body-sign-page">

		<?php get_header(); ?>

		<div class="my-5"></div>
		
		<div class="container">

			<div class="row d-flex justify-content-center">

				<div class="col-lg-6 col-10 sign-form bg-white px-0">
				<?php
				if(!empty($platform) && !empty($social_login_error)) {
					?>
					<div class="alert alert-danger">
						<?php echo $social_login_error; ?>
					</div>
					<?php
				}
				?>
					<form method="POST" id="signin-form">
						<div class="form-title text-center text-white py-3">
							<h4><i class="fas fa-user"></i>&nbsp;<?php echo _t("تسجيل الدخول"); ?></h4>
						
						</div>
						<div class="my-5"></div>

						<div class="form-body px-md-5 px-3">

							<div class="form-group">
								<label for="user_name_email"><?php echo _t("الإسم أو البريد الإلكتروني"); ?></label>
								<input type="text" id="user_name_email" name="user_name_email" class="form-control rounded-0 form-control-lg"/>
								<div id="user_name_email_error_txt" class="invalid-feedback"></div>
							</div>

							<div class="form-group">
								<label for="user_pwd"><?php echo _t("كلمة المرور"); ?></label>
								<input type="password" id="user_pwd" name="user_pwd" class="form-control rounded-0 form-control-lg"/>
								<div id="user_pwd_error_txt" class="invalid-feedback"></div>
							</div>

							<div class="form-group">
								<a href="<?php echo siteurl(); ?>/forget_password.php" class="text-danger"><?php echo _t("إسترجاع كلمة المرور ؟"); ?></a>
							</div>

							<div class="form-group sign-btns">
								<button class="btn btn-lg btn-sign-green rounded-0"><?php echo _t("دخول");?></button>
							</div>
							
							<div class="form-group border-top pt-3">
								<div class="signin-methods text-left mx-auto">
									<a href="<?php echo siteurl(); ?>/signup.php" class="text-white small btn btn-signin btn-block btn-email-login d-flex align-items-center rounded-0"><i class="fab fa-facebook-f mr-3"></i><?php echo _t("تسجيل الدخول بالبريد الإلكتروني"); ?></a>
									<a href="<?php echo siteurl(); ?>/signin.php?platform=Facebook" class="text-white small btn btn-signin btn-block btn-facebook-login d-flex align-items-center rounded-0"><i class="fab fa-facebook-f mr-3"></i><?php echo _t("تسجيل الدخول عبر فيسبوك"); ?></a>
									<a href="<?php echo siteurl(); ?>/signin.php?platform=Twitter" class="text-white btn btn-signin btn-block btn-twitter-login d-flex align-items-center rounded-0"><i class="fab fa-twitter mr-3"></i><?php echo _t("تسجيل الدخول عبر تويتر"); ?></a>
								</div>
							</div>

						</div>

						<input type="hidden" name="method" value="user_ajax"/>
						<input type="hidden" name="request" value="signin"/>

					</form>

				</div>

			</div>

		</div>
		<div class="my-5"></div>
		<?php user_end_scripts(); ?>
		<?php get_footer(); ?>
	</body>
	
</html>