<?php

# (objectcreate)
function objectcreate($params) {

	// (deploy)
	deploy(
		// params
		$params,
		// assets
		array(
			sprintf("%sextension/%s.%s.php", $params->path, $params->applicationid, strtolower(ConVar(2))) => loadobject("extension.php")
		),
		// values
		array(
			"classname" => sprintf("mg%s%s", ucfirst($params->applicationname), ConVar(2))
		)
	);
};