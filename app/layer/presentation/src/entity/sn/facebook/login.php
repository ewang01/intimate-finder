<?php
/*
Logs in to FaceBook and save the access_token to the DB, so the scripts can run later.
Additionally starts the script: start_fetching_facebook_user_data.php

Uses: SNFacebookService->login(...)
*/

$access_token = isset($_SESSION["access_token"]) ? $_SESSION["access_token"] : NULL;

$user_id = $SNFacebookService->getUserId();

if($user_id) {
	echo $user_id."\n";
	$_SESSION["network_user_id"] = $user_id;
	$_SESSION["network_id"] = 2;
     $_SESSION["access_token"] = $SNFacebookService->getAccessToken();
     
	//1. Check if the user has a tribe
	$data = array(
		'network_user_id'	=>	$user_id,
		'network_id' => 2,
     );
     
	$user_exist =  $UserService->getUserInfoFromLiveSystem($data);
	if(!empty($user_exist)){
		
	}else{
		getUserInfo();		
	}
	header("Location: ".HOST_PREFIX."/home/hero");
	die();
		
}
else{
 	$loginUrl = $SNFacebookService->login();
}

function getUserInfo() {
	global $SNFacebookService, $UserService;
	
	$screenName = $SNFacebookService->getUserPseudo(array("network_user_id" => $_SESSION["network_user_id"]));
	$data = array(
		'access_token'  => $_SESSION['access_token'],
		'network_user_id'	=>	$_SESSION["network_user_id"],
		'network_id' => $_SESSION["network_id"],
		'name'	=>	$SNFacebookService->getUserName(array("network_user_id" => $_SESSION["network_user_id"])),
		'screen_name' => $screenName ? $screenName : '' ,
		'invitation_pending' => 1
     );

	$UserService->insertUserNetwork($data);
}

?>
