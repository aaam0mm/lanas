<?php
require_once 'init.php';
$access_key = $_GET["key"] ?? "";

if( strlen($access_key) == 32 ) {
	
	$get_file = $dsql->dsql()->table('files')->where('file_key', $access_key)->limit(1)->getRow();
	
	if($get_file) {
		$file = $get_file;
		$file_name_ext = explode(".",$file["file_name"]);
		$file_name = $file_name_ext[0];
		$file_ext = $file_name_ext[1];
		$file_dir = $file["file_dir"];
		$file_mimetype = $file["mime_type"];
		
		$file = UPLOAD_DIR.$file_dir."/".$file["file_name"];
        $image = new Imagick();
        $image->readImage($file);
        
		header('Content-Description: File Transfer');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header("Content-Type: ".$file_mimetype."");
		echo $image;
	}
}else{
	exit(http_response_code(404));
}