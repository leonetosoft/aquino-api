<?php

	//Prove integração dos elementos do cre
	//require_once "jwt/JWT.php";

	foreach (glob(_APP. "/core/jwt/*.php") as $file)
	{
		require_once  $file;
	}

	require_once "ApiModule.php";

	//require_once _APP . "/ApiModule.php";

?>