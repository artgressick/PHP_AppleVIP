<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Add Sponsor';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "sponsors";		 // This is needed to highlight the user section
	
	require($BF. '_lib.php');

	
	if(isset($_POST['id'])) { // When doing isset, use a required field.  Faster than the php count funtion.
	
		$userList = array_unique($_POST['users']);

		// Insert into SponsorTypes
		$q = "INSERT INTO SponsorsbyShow (idShow,idUser,intInvites) VALUES 
		 	('" . $_POST['id'] . "','" . $_POST['idUser'] . "','" . $_POST['intInvites'] . "')";
		if(database_query($q,"Insert into Sponsors by Show")) {
			$_SESSION['infoMessages'][] = 'Sponsor added successfully.';
		} else {
			ErrorPage('An Error has occurred while trying to add this Sponsor. Please contact Support.');
		}
							 
		header("Location: ". $_POST['moveTo'] ."?id=". $_POST['id']);
		die();
	}
	include($BF. 'includes/meta.php');	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = 'onload="document.getElementById(\'intInvites\').focus()"';
	
	//Load drop down menus for the page
	$users = database_query("SELECT *
		FROM Users
		WHERE idRight!=3 AND !bDeleted AND ID NOT IN (SELECT idUser FROM SponsorsByShow WHERE idShow='". $_REQUEST['id'] ."' AND !bDeleted)
		ORDER BY chrLast,chrFirst", "getting users");
	
	$show = fetch_database_query("SELECT chrName FROM Shows WHERE ID=". $_REQUEST['id'], "getting show");
			
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('idUser', "You must select a Sponsor.");
		total += ErrorCheck('intInvites', "You must enter the maximum number of Invites.");
		

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
			<td class="title">Add Sponsor</td>
			<td class="title_right"></td>
			<td class="right"></td>
		</tr>
	</table>
	<div class='instructions'>To add a sponsor, fill in all the information and click on the "Add Sponsor" button.</div>

	<div class='innerbody'>
		<?=messages()?>
		<div id='errors'></div>
		<div id='showinfo'></div>
		
		<table class='twoCol'>
			<tr>
				<td class='left'>

					<div class='FormName'>Show</div>
					<div class='FormField'>
						<?=$show['chrName']?>
					</div>
					
					<div class='FormName'>Sponsor <span class='Required'>(Required)</span></div>
					<div class='FormField'>
						<select id='idUser' name='idUser'>
							<option value=''>-Select Sponsor-</option>
<?	while($row = mysqli_fetch_assoc($users)) { ?>
							<option value='<?=$row["ID"]?>'><?=$row['chrLast']?>, <?=$row['chrFirst']?></option>
<?	} ?>
						</select>
					</div>
				
				</td>
				<td class='gutter'>
				</td>
				<td class='right'>
					
					<div class='FormName'>Invites <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='intInvites' id='intInvites' /></div>

				</td>
			</tr>
		</table>

		<input class='FormButtons' type='button' value='Add Sponsor And Return' onclick="document.getElementById('moveTo').value='addsponsor.php'; error_check();" /> &nbsp;&nbsp; 
		<input class='FormButtons' type='button' value='Add Sponsor And Continue' onclick="document.getElementById('moveTo').value='sponsors.php'; error_check();" /> &nbsp;&nbsp; 
		<input type='hidden' name='moveTo' id='moveTo' />
		<input type='hidden' name='id' value='<?=$_REQUEST['id']?>' />

	</div>

</form>
<?
	include($BF. 'includes/bottom.php');
?>
