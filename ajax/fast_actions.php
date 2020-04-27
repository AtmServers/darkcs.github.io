<?php
include_once '../inc/start_lite.php';

$AA = new AuthApi;
$auth_api = $AA->auth_api_info($pdo);

/* Vk
=========================================*/
if(isset($_POST['get_vk_auth_link'])) {
	if($auth_api->vk_api == 1) {
		$params = array(
			'client_id' => $auth_api->vk_id,
			'redirect_uri' => $full_site_host,
			'display' => 'popup',
			'response_type' => 'code',
			'state' => 'login',
			'v' => '5.73'
		);
		$url = 'https://oauth.vk.com/authorize?' . urldecode(http_build_query($params));
		$url = str_replace("&amp;", "&", $url);

		exit(json_encode(array('url' => $url)));
	} else {
		exit(json_encode(array('url' => '#')));
	}
}
if(isset($_POST['attach_user_vk'])) {
	if($auth_api->vk_api == 1) {
		$params = array(
			'client_id' => $auth_api->vk_id,
			'redirect_uri' => $full_site_host.'settings',
			'display' => 'popup',
			'response_type' => 'code',
			'state' => 'attach',
			'v' => '5.73'
		);
		$url = 'https://oauth.vk.com/authorize?' . urldecode(http_build_query($params));
		$url = str_replace("&amp;", "&", $url);

		exit(json_encode(array('url' => $url)));
	} else {
		exit(json_encode(array('url' => '#')));
	}
}
/* Steam
=========================================*/
if(isset($_POST['get_steam_auth_link'])) {
	if($auth_api->steam_api == 1) {
		$openid = new LightOpenID($host);
		$openid->returnUrl = $full_site_host."index?steam_auth=1";

		if(!$openid->mode) {
			$openid->identity = 'https://steamcommunity.com/openid';
			$url = $openid->authUrl();
			exit(json_encode(array('url' => $url)));
		}
	}
	exit(json_encode(array('url' => '#')));
}
if(isset($_POST['attach_user_steam'])) {
	if($auth_api->steam_api == 1) {
		$openid = new LightOpenID($host);
		$openid->returnUrl = $full_site_host."settings?steam_auth=1";

		if(!$openid->mode) {
			$openid->identity = 'https://steamcommunity.com/openid';
			$url = $openid->authUrl();
			exit(json_encode(array('url' => $url)));
		}
	}
	exit(json_encode(array('url' => '#')));
}
/* Facebook
=========================================*/
if(isset($_POST['get_fb_auth_link'])) {
	if($auth_api->fb_api == 1) {
		
		$params = array(
		    'client_id'     => $auth_api->fb_id,
		    'redirect_uri'  => $full_site_host."index?fb_auth=1",
		    'response_type' => 'code',
		);
		$url = 'https://www.facebook.com/dialog/oauth?' . urldecode(http_build_query($params));
		$url = str_replace("&amp;", "&", $url);

		exit(json_encode(array('url' => $url)));
	} else {
		exit(json_encode(array('url' => '#')));
	}
}
if(isset($_POST['attach_user_fb'])) {
	if($auth_api->fb_api == 1) {

		$params = array(
		    'client_id'     => $auth_api->fb_id,
		    'redirect_uri'  => $full_site_host."settings?fb_attach=1",
		    'response_type' => 'code',
		);
		$url = 'https://www.facebook.com/dialog/oauth?' . urldecode(http_build_query($params));
		$url = str_replace("&amp;", "&", $url);

		exit(json_encode(array('url' => $url)));
	} else {
		exit(json_encode(array('url' => '#')));
	}
}
/* Reg
=========================================*/
if(isset($_POST['reg_by_api'])) {
	$email = checkJs($_POST['email'],null);
	$type = checkJs($_POST['type'],null);
	if(empty($email)) {
		exit(json_encode(array('data' => '<p class="text-danger">Введите e-mail!</p>')));
	}

	$U = new Users($pdo);

	if(!$U->check_email($email)) {
		exit(json_encode(array('data' => '<p class="text-danger">Неверно введен е-mail!</p>')));
	}
	if(!$U->check_email_busyness($email)) {
		exit(json_encode(array('data' => '<p class="text-danger">Введеный Вами E-mail уже зарегистрирован!</p>')));
	}

	if($type == 'vk') {
		if($auth_api->vk_api == 1) {
			$params = array(
				'client_id' => $auth_api->vk_id,
				'redirect_uri' => $full_site_host,
				'display' => 'popup',
				'response_type' => 'code',
				'state' => $email,
				'v' => '5.73'
			);
			$url = 'https://oauth.vk.com/authorize?' . urldecode(http_build_query($params));
			$url = str_replace("&amp;", "&", $url);
			exit(json_encode(array('data' => '<script>$("#api_reg_btn").fadeOut(0); document.location.href = "'.$url.'";</script><p class="text-success">Если Вас не перенаправило на сайт Вконтакте автоматически, то нажмите на ссылку: <a href="'.$url.'">перейти</a></p>')));
		} else {
			exit(json_encode(array('data' => '<p class="text-danger">Регистрация через Вконтакте недоступна!</p>')));
		}
	} elseif($type == 'steam') {
		if($auth_api->steam_api == 1) {

			$openid = new LightOpenID($host);
			$openid->returnUrl = $full_site_host."index?steam_reg=1&email=".$email;

			if(!$openid->mode) {
				$openid->identity = 'https://steamcommunity.com/openid';
				$url = $openid->authUrl();
			} else {
				exit(json_encode(array('data' => '<p class="text-danger">Ошибка</p>')));
			}

			exit(json_encode(array('data' => '<script>$("#api_reg_btn").fadeOut(0); document.location.href = "'.$url.'";</script><p class="text-success">Если Вас не перенаправило на сайт Steam автоматически, то нажмите на ссылку: <a href="'.$url.'">перейти</a></p>')));
		} else {
			exit(json_encode(array('data' => '<p class="text-danger">Регистрация через Steam недоступна!</p>')));
		}
	} elseif($type == 'fb') {
		if($auth_api->fb_api == 1) {

			$params = array(
				'client_id'     => $auth_api->fb_id,
				'redirect_uri'  => $full_site_host."index?fb_reg=1",
				'response_type' => 'code',
				'state'         => $email
			);
			$url = 'https://www.facebook.com/dialog/oauth?' . urldecode(http_build_query($params));
			$url = str_replace("&amp;", "&", $url);

			exit(json_encode(array('data' => '<script>$("#api_reg_btn").fadeOut(0); document.location.href = "'.$url.'";</script><p class="text-success">Если Вас не перенаправило на сайт Facebook автоматически, то нажмите на ссылку: <a href="'.$url.'">перейти</a></p>')));
		} else {
			exit(json_encode(array('data' => '<p class="text-danger">Регистрация через Facebook недоступна!</p>')));
		}
	} else {
		exit(json_encode(array('data' => '<p class="text-danger">Ошибка</p>')));
	}
}
/* Profile info
=========================================*/
if (isset($_POST['get_vk_profile_info'])) {
	$vk_api = checkJs($_POST['vk_api'], null);

	if (empty($vk_api) || $auth_api->vk_api == 2){
		exit(json_encode(array('avatar' => 'none', 'first_name' => 'none', 'last_name' => 'none')));
	}

	if(empty($auth_api->vk_service_key)) {
		$content['response'][0]['photo_50'] = null;
	} else {
		$content = file_get_contents_curl("https://api.vk.com/method/users.get?user_id=".$vk_api."&v=5.73&lang=ru&fields=photo_50&access_token=".$auth_api->vk_service_key."&callback=?"); 
		$content = json_decode($content, true);
	}

	if(!empty($content['response'][0]['photo_50'])) {
		$avatar = $content['response'][0]['photo_50'];
		$first_name = $content['response'][0]['first_name'];
		$last_name = $content['response'][0]['last_name'];
	} else {
		$avatar = 'none';
		$first_name = $vk_api;
		$last_name = '';
	}

	exit(json_encode(array('avatar' => $avatar, 'first_name' => $first_name, 'last_name' => $last_name)));
}
if (isset($_POST['get_user_steam_info'])) {
	$steam_api = checkJs($_POST['steam_api'], null);

	if (empty($steam_api) || $auth_api->steam_api == 2){
		exit(json_encode(array('avatar' => '../files/avatars/no_avatar.jpg', 'login' => 'Неизвестно')));
	}

	$content = file_get_contents_curl("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".$auth_api->steam_key."&steamids=".$steam_api); 
	$content = json_decode($content, true);

	exit(json_encode(array('avatar' => $content['response']['players'][0]['avatarfull'], 'login' => $content['response']['players'][0]['personaname'])));
}
if (isset($_POST['get_fb_profile_info'])) {
	$fb_api = checkJs($_POST['fb_api'], null);

	if (empty($fb_api) || $auth_api->fb_api == 2){
		exit(json_encode(array('login' => 'none')));
	}

	$content = file_get_contents_curl('https://graph.facebook.com/'.$fb_api.'?fields=id,name&access_token='.$auth_api->fb_id.'|'.$auth_api->fb_key.'');
	$content = json_decode($content, true);

	if(isset($content['name'])) {
		exit(json_encode(array('login' => $content['name'])));
	} else {
		exit(json_encode(array('login' => 'none')));
	}
}
?>