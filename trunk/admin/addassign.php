<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Assign User';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "assign";		 // This is needed to highlight the user section
	
	require($BF. '_lib.php');

	if(isset($_POST['idSponsor'])) { // When doing isset, use a required field.  Faster than the php count funtion.
	
		// Insert into SponsorTypes
		$q = "INSERT INTO SponsorsByUser (idUser,idSponsor) VALUES ";
		
		$cnt = 0;
		foreach($_POST['users'] as $v) {
	 		$q .= ($cnt++ > 0 ? ',' : '')."('" . $v . "','" . $_POST['idSponsor'] . "')";
		}
		if(database_query($q,"Insert into Sponsors by Show")) {
			$_SESSION['infoMessages'][] = 'User Assignment saved successfully.';
		} else {
			ErrorPage('An Error has occurred while trying to save this User Assignment. Please contact Support.');
		}
				 
		header("Location: ". $_POST['moveTo'] ."?id=". $_POST['id']);
		die();
	}

	
	include($BF. 'includes/meta.php');
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = 'onload="document.getElementById(\'idSponsor\').focus()"';
	
	//Load drop down menus for the page
	$sponsors = database_query("SELECT ID,chrFirst,chrLast
		FROM Users
		WHERE !bDeleted AND idRight!=3
		AND Users.ID NOT IN (SELECT idSponsor FROM SponsorsByUser WHERE !bDeleted)
		ORDER BY chrLast,chrFirst", "getting Sponsors");
	
	$users = database_query("SELECT ID,chrFirst,chrLast FROM Users WHERE !bDeleted AND idRight!=2 ORDER BY chrLast,chrFirst", "getting Users");
			
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('idSponsor', "You must select a Sponsor.");

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
			<td class="title">Assign User to Sponsor</td>
			<td class="title_right"></td>
			<td class="right"></td>
		</tr>
	</table>
	<div class='instructions'>To add a User, fill in all the information and click on the "Add User" button.</div>

	<div class='innerbody'>
		<?=messages()?>
		<div id='errors'></div>
		<div id='showinfo'></div>
		
		
		<table class='twoCol'>
			<tr>
				<td class='left' style="width:75px;">
					
					<div class='FormName'>Sponsors <span class='Required'>(Required)</span></div>
					<div class='FormField'>
						<select id='idSponsor' name='idSponsor'>
							<option value=''>-Select User-</option>
<?	while($row = mysqli_fetch_assoc($sponsors)) { ?>
							<option value='<?=$row["ID"]?>'><?=$row['chrLast']?>, <?=$row['chrFirst']?></option>
<?	} ?>
						</select>
					</div>
				
				</td>
				<td class='gutter' style="width:10px;">
				</td>
				<td class='right'>

					<div class='FormName'>Users <span class='Required'>(Required)</span></div>

					<table width="100%" cellpadding="0" cellspacing="0" border="0">
						<tr>
<?
						$cols=0;
						$count=0;
						while($row = mysqli_fetch_assoc($users)) {
							if ($cols==4) { 
							$cols=0;
?>
						</tr>
						<tr>
<?
							}
?>
							<td><input type='checkbox' name='users[]' value='<?=$row["ID"]?>'> <?=$row['chrLast']?>, <?=$row['chrFirst']?></td>
<?
						$cols++;
						$count++;
						}
?>

						</tr>
					</table>
				</td>
			</tr>
		</table>

		<input class='FormButtons' type='button' value='Assign User And Return' onclick="document.getElementById('moveTo').value='addassign.php'; error_check();" /> &nbsp;&nbsp; 
		<input class='FormButtons' type='button' value='Assign User And Continue' onclick="document.getElementById('moveTo').value='assign.php'; error_check();" /> &nbsp;&nbsp; 
		<input type='hidden' name='moveTo' id='moveTo' />

	</div>

</form>
<?
	include($BF. 'includes/bottom.php');
?>
