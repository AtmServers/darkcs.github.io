<?php
session_set_cookie_params(0, '/', '', false, true);
session_start();

if(!isset($protection)) {
	$protection = 1;
}

include_once '../inc/db.php';
include_once '../inc/config.php';
include_once '../inc/functions.php';

if(empty($_SESSION['token'])){
	$token = get_token();
	$_SESSION['token'] = $token;
} else {
	$token = $_SESSION['token'];
}
if (isset($_COOKIE['id']) and isset($_COOKIE['login']) and isset($_COOKIE['password'])){
	$_SESSION['password'] = clean($_COOKIE['password'],NULL);
	$_SESSION['login'] = clean($_COOKIE['login'],NULL);
	$_SESSION['id'] = clean($_COOKIE['id'],"int");
}

if (empty($_POST['phpaction'])) {
	log_error("Прямой вызов update_installer.php"); 
	exit('Ошибка: [Прямой вызов инклуда]');
}
if(!is_admin()){
	exit('Ошибка: [Доступно только администраторам]');
}

if (isset($_POST['install_update'])) {
	ignore_user_abort(1);
	set_time_limit(0);

	$STH = $pdo->query("SELECT `version`, `update_link` FROM `config__secondary` LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);  
	$row = $STH->fetch();

	$params = unserialize($row->update_link);
	$version = $params['version'];
	$link = $params['link'];
	$path = '../modules/updates/'.$version.'/';
	mkdir($path, 0777);
	
	$arr = explode("/",$link);
	$zip_file = $arr[count($arr)-1];
	
	$update_file = $path.$zip_file;
	file_put_contents($update_file, file_get_contents($link));

	$archive = new PclZip($update_file);
	$result = $archive->extract(PCLZIP_OPT_PATH, $path);

	include_once $path.'installer/first_installer.php';
	$pdo->exec(trim(file_get_contents($path.'installer/base.sql')));
	copy_files($path.'files/', '../');
	include_once $path.'installer/second_installer.php';

	$STH = $pdo->prepare("UPDATE `config__secondary` SET `version`=:version, `update_link`=:update_link LIMIT 1");
	if ($STH->execute(array( ':version' => $version, ':update_link' => '' )) == '1') {
		unlink($update_file);
		removeDirectory($path);

		exit(json_encode(array('status' => '1')));
	}
}
?>