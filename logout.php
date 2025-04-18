<?php
require_once 'init.php';
if(is_login_in()) {
	if(logout()) {
		header("location:".siteurl()."/signin.php");
	}else{
		http_response_code(404);
	}
}else{
	http_response_code(404);
}