<?	
	$BF = '';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Edit Profile';      // Title to display at the top of the browser window.
	$active = "home";           // This needs to be set for the nav bar at the top to know which section to highlight..

	require($BF. '_lib.php');
	include($BF. 'includes/meta.php');

	// Get info to populate fields. Also ... If the old information is the same as the current, why update it?  Get the old information to test this against.
	$info = fetch_database_query("SELECT * FROM Users WHERE ID=". $_SESSION['idUser'],"getting user info");

	// Checks to see if the form was submitted, any required field may be used
	if(isset($_POST['chrFirst'])) { 

		// Set the basic values to be used.
		//   $table = the table that you will be connecting to to check / make the changes
		//   $mysqlStr = this is the "mysql string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		//   $sudit = this is the "audit string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		$table = 'Users';
		$mysqlStr = '';
		$audit = '';

		// "List" is a way for php to split up an array that is coming back.  
		// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
		//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
		//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
		//    ...  This also will ONLY add changes to the audit table if the values are different.
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrFirst',$info['chrFirst'],$audit,$table,$_SESSION['idUser']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrLast',$info['chrLast'],$audit,$table,$_SESSION['idUser']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrEmail',$info['chrEmail'],$audit,$table,$_SESSION['idUser']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'idShow',$info['idShow'],$audit,$table,$_SESSION['idUser']);
	
		$_SESSION['idShow'] = $_POST['idShow'];
			
		if( $_POST['chrPassword'] != '')
		{
			if( $_POST['chrPassword'] == $_POST['chrPassword2'])
			{
				list($mysqlStr,$audit) = set_strs_password($mysqlStr,'chrPassword',$info['chrPassword'],$audit,$table,$_POST['id']);
			}
		}
		
		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_SESSION['idUser']);
			$_SESSION['infoMessages'][] = 'Profile Updated Successfully'; 
		} else {
			ErrorPage('An Error has occurred while trying to update your profile. Please contact an Administrator.');
		}

		// When the page is done updating, move them back to whatever the list page is for the section you are in.
		header("Location: index.php");
 		die();
	}
	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = 'onload="document.getElementById(\'chrFirst\').focus()"';
	
	// Getting Drop Down Menu for Default Show
	if ($_SESSION['idRight'] == 1) {
		$shows = database_query("SELECT ID,chrName FROM Shows WHERE !bDeleted ORDER BY chrName", "getting shows");
	} else {
		$shows = database_query("SELECT DISTINCT Shows.ID,Shows.chrName 
		FROM Shows
		JOIN SponsorsbyShow ON SponsorsbyShow.idShow=Shows.ID AND !SponsorsbyShow.bDeleted 
		LEFT JOIN SponsorsByUser ON SponsorsbyShow.idUser=SponsorsByUser.idSponsor
		WHERE !Shows.bDeleted AND Shows.idStatus IN (2,3) AND (SponsorsbyShow.idUser = ".$_SESSION['idUser']." OR SponsorsByUser.idUser = ".$_SESSION['idUser'].")
		ORDER BY chrName
		", "getting show status");
	}

?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>

<script language="javascript">
	function error_check() {
		if(total != 0) { reset_errors(); }
		

		var total=0;

		total += ErrorCheck('chrFirst', "You must enter a First Name.");
		total += ErrorCheck('chrLast', "You must enter a Last Name.");
		total += ErrorCheck('chrEmail', "You must enter an Email Address.","email");
		
		if( document.getElementById('chrPassword').value != '' || document.getElementById('chrPassword2').value != '')
		{			
			total += matchPasswords('chrPassword', 'chrPassword2', 'Passwords must match')
		}
		
		if(total == 0) { document.getElementById('idForm').submit(); } else { window.scrollTo(0,0); }
	}
	
</script>

<?
	include($BF. 'includes/top.php');
?>

<form name='idForm' id='idForm' method="post">

	<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
		<tr>
			<td class="left"></td>
			<td class="title">Edit Profile</td>
			<td class="title_right"></td>
			<td class="right"></td>
		</tr>
	</table>
	<div class='instructions'>To modify your profile, change any of the information and click on the "Update Information" button.</div>

	<div class='innerbody'>
	
		<div id='errors'></div>
		<div id='showinfo'></div>
		
		<table class='twoCol'>
			<tr>
				<td class='left'>

					<div class='FormName'>First Name <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrFirst' id='chrFirst' size='35' value='<?=$info['chrFirst']?>' /></div>
					
					<div class='FormName'>Last Name <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrLast' id='chrLast' size='35' value='<?=$info['chrLast']?>' /></div>
					
					<div class='FormName'>Email <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrEmail' id='chrEmail' size='40' value='<?=$info['chrEmail']?>' /></div>
					
			  </td>
			  <td class='gutter'></td>
				<td class='right'>
					<div class='FormName'>Default Show Selected</div>
					<div class='FormField'><select name='idShow' id='idShow'>
												<option value=''>Select Show</option>
										<?	while($row = mysqli_fetch_assoc($shows)) { ?>
												<option <?=(@$_SESSION['idShow'] == $row["ID"] ? ' selected="selected" ' : '')?> value='<?=$row["ID"]?>'><?=$row['chrName']?></option>
										<?	} ?>
											</select>
					</div>
					<div class='FormName'>Password (Only if changing password)</div>
					<div class='FormField'><input type='password' name='chrPassword' size='20' id='chrPassword' /></div>
					
					<div class='FormName'>Verify Password (Only if changing password)</div>
					<div class='FormField'><input type='password' name='chrPassword2' size='20' id='chrPassword2' /></div>
											
				</td>
			</tr>
		</table>

		<input class='FormButtons' type='button' value='Update Information' onclick="error_check()" />
	</div>

</form>
<?
	include($BF. 'includes/bottom.php');
?>
