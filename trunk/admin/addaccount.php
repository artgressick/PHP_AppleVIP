<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Add Account';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "accounts";		 // This is needed to highlight the user section
	
	require($BF. '_lib.php');
	
	if(isset($_POST['chrFirst'])) { // When doing isset, use a required field.  Faster than the php count funtion.
		$q = "INSERT INTO Users SET 
			chrFirst='". encode($_POST['chrFirst']) ."',
			chrLast='". encode($_POST['chrLast']) ."',
			chrEmail='". strip_quotes($_POST['chrEmail']) ."',
			chrPassword='". md5($_POST['chrPassword']) . "',
			idRight='". $_POST['idUserRights'] ."'
		";
		if(database_query($q,"Insert into users")) {
		
			// This is the code for inserting the Audit Page
			// Type 1 means ADD NEW RECORD, change the TABLE NAME also
			global $mysqli_connection;  // This is needed for mysqli to be able to get the "last insert id"
			$newID = mysqli_insert_id($mysqli_connection);
					
			$q = "INSERT INTO Audit SET 
				idType=1, 
				idRecord='". $newID ."',
				txtNewValue='". encode($_POST['chrFirst']) ." ". encode($_POST['chrLast']) ."',
				dtDateTime=now(),
				chrTableName='Users',
				idUser='". $_SESSION['idUser'] ."'
			";
			database_query($q,"Insert audit");
			//End the code for History Insert
			$_SESSION['infoMessages'][] = $_POST['chrFirst'].' '.$_POST['chrLast'].' has been added successfully.';
		} else {
			ErrorPage('An Error has occurred while trying to add this account. Please contact Support.');
		}
		
		header("Location: ". $_POST['moveTo']);
		die();
	}

	include($BF. 'includes/meta.php');
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = 'onload="document.getElementById(\'chrFirst\').focus()"';

?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>

<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrFirst', "You must enter a First Name.");
		total += ErrorCheck('chrLast', "You must enter a Last Name.");
		total += ErrorCheck('chrEmail', "You must enter an Email Address.","email");
		total += matchPasswordsAdd('chrPassword', 'chrPassword2');
		total += ErrorCheck('idUserRights', "You must select a User Right");

		if(total == 0) { document.getElementById('idForm').submit(); } else { window.scrollTo(0,0); }
	}
</script>
<?
	include($BF. 'includes/top.php');
?>

<form name='idForm' id='idForm' action='' method="post">

	<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
		<tr>
			<td class="left"></td>
			<td class="title">Add Account</td>
			<td class="title_right"></td>
			<td class="right"></td>
		</tr>
	</table>
	<div class='instructions'>To add an acount please enter the information below. After you enter the information you will need to link the Sponsor and User in their respective areas.</div>

	<div class='innerbody'>
		<?=messages()?>
		<div id='errors'></div>
		<div id='showinfo'></div>
		
		<table class='twoCol'>
			<tr>
				<td class='left'>

					<div class='FormName'>First Name <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrFirst' id='chrFirst' size='35' /></div>
					
					<div class='FormName'>Last Name <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrLast' id='chrLast' size='35' /></div>
					
					<div class='FormName'>Email <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrEmail' id='chrEmail' size='40' /></div>
								
				</td>
				<td class='gutter'></td>
				<td class='right'>
					<div class='FormName'>User Right <span class='Required'>(Required)</span></div>
					<div class='FormField'>
						<select id='idUserRights' name='idUserRights'>
							<option value=''>-Select User Right-</option>
<?
	$userRights = database_query("SELECT ID,chrRights FROM UserRights", "getting user rights");
	while($row = mysqli_fetch_assoc($userRights)) {
?>
							<option value='<?=$row["ID"]?>'><?=$row['chrRights']?></option>
<?
	}
?>
						</select>
					</div>
					
					<div class='FormName'>Password <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='password' name='chrPassword' id='chrPassword' size='20' /></div>
					
					<div class='FormName'>Verify Password<span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='password' name='chrPassword2' id='chrPassword2' size='20' /></div>
					
				</td>
			</tr>
		</table>

		<input class='FormButtons' type='button' value='Add Account And Return' onclick="document.getElementById('moveTo').value='addaccount.php'; error_check();" /> &nbsp;&nbsp; 
		<input class='FormButtons' type='button' value='Add And Move On' onclick="document.getElementById('moveTo').value='accounts.php'; error_check();" />
		<input type='hidden' name='moveTo' id='moveTo' />

	</div>

</form>
<?
	include($BF. 'includes/bottom.php');
?>
