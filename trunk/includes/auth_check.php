<?
	global $auth_not_required, $BF;

		if (isset($_REQUEST['auth_form_name'])) {  // check to see if this is a submission of the login form
			$error_messages = array();
			
			$auth_form_name = strtolower($_REQUEST['auth_form_name']);

			$query = "SELECT * FROM Users WHERE chrEmail='" . $auth_form_name . "' AND
				chrPassword=MD5('" . $_REQUEST['auth_form_password'] . "')";

			$result = database_query($query, "auth_check: verifying Email and Password against db.");
			
			if ($result) {
				if (mysqli_num_rows($result)) {
					
					$row = mysqli_fetch_assoc($result);

					$_SESSION['chrEmail'] = $row["chrEmail"];
					$_SESSION['idUser'] = $row["ID"];
					$_SESSION['chrFirst'] = $row["chrFirst"];
					$_SESSION['chrLast'] = $row["chrLast"];
					$_SESSION['idRight'] = $row["idRight"];
					if ($row["idShow"] != "" || $row["idShow"] != "0") { $_SESSION['idShow'] = $row["idShow"]; }
					$_SESSION['bMasq'] = false;

					if($_SESSION['idRight'] == 4) {
						header('Location: '. $BF .'checkin.php');
						die();
					} else {
						header('Location: '. $_SERVER['REQUEST_URI'] .'?id='. $_REQUEST['idShow']);
						die();
					}
	
				} else {
					$error_messages[] = "Authentication failed<!--(1)-->.";
				}
			} else {
				//echo(mysql_error());
				$error_messages[] = "Authentication failed<!--(2)-->.";
			}
		}

		if (isset($_SESSION['idUser'])) {  // if this variable is set, they are now authenticated
			header('Location: ' . $_SERVER['REQUEST_URI']);			
			die();
		}
	
	if (!isset($auth_not_required)) $auth_not_required = false;

	if (!$auth && $auth_not_required != true) {  // if not authenticated, present the form

		include($BF. "includes/login.php");
		die();
	}
?>
