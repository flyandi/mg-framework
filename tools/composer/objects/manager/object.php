<?php

# (objectcreate)
function objectcreate($params) {
	// (init)
	$mpath = sprintf("%smanager/manager.%s/", $params->path, strtolower(ConVar(2)));

	// (deploy)
	deploy(
		// params
		$params,
		// assets
		array(
			sprintf("%smanager.js", $mpath) => loadobject("manager.js"),
			sprintf("%smanager.php", $mpath) => loadobject("manager.php"),
			sprintf("%smanager.xml", $mpath) => loadobject("manager.xml"),
			sprintf("%smanager.css", $mpath) => loadobject("manager.css"),
			sprintf("%ssnippet/default.snippet", $mpath) => ""
		),
		// values
		array(
			"managerid" => strtolower(ConVar(2)),
			"managerclass" => ConVar(2),
		)
	);
};