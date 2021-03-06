<?php
if (!session_id()) {
	session_start();
}
require getConfigFilePath("config");

if (isset($_REQUEST["interface_version"])) {
	$INTERFACE_VERSION = strtoupper($_REQUEST["interface_version"]);
}
else if (isset($_SESSION["interface_version"])) {
	$INTERFACE_VERSION = $_SESSION["interface_version"];
}
else {
	$INTERFACE_VERSION = "DESKTOP";
}


$_SESSION["interface_version"] = $INTERFACE_VERSION;

$evc_settings = Controller::getPresentationEntityViewSettings($controller_setings);
$evc_settings['CONTROLLER'] = $controller_setings;
unset($controller_setings);
unset($url);

if (!$evc_settings['ENTITY']['OBJ_FILE_PATH'] && !$evc_settings['VIEW']['OBJ_FILE_PATH']) {
	require getPresentationViewFilePath("default.error");
}
else {
	if ($evc_settings['ENTITY']['OBJ_FILE_PATH']) {
		require $evc_settings['ENTITY']['OBJ_FILE_PATH'];
	}
	
	if ($evc_settings['VIEW']['OBJ_FILE_PATH']) {
		require $evc_settings['VIEW']['OBJ_FILE_PATH'];
	}
}
?>
