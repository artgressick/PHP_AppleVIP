<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Assign User';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "assign";		 // This is needed to highlight the user section
	
	require($BF. '_lib.php');
	(!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id']) ? ErrorPage() : "" );
	
	if(count($_POST)) { // When doing isset, use a required field.  Faster than the php count funtion.
	
		database_query("DELETE FROM SponsorsByUser WHERE idSponsor=". $_POST['idSponsor'],"delete from sponsors by user");
	
		// Insert into SponsorTypes
		$q = "INSERT INTO SponsorsByUser (idUser,idSponsor) VALUES ";
		$cnt = 0;
		$q2 = '';
		if(isset($_POST['users']) && count($_POST['users'])) {
			foreach($_POST['users'] as $v) {
		 		$q2 .= ($cnt++ > 0 ? ',' : '')."('" . $v . "','" . $_POST['idSponsor'] . "')";
			}
		}
		if ($q2 != '') { database_query($q.$q2,"Insert into Sponsors by Show");
			$_SESSION['infoMessages'][] = 'Sponsor Assignment updated successfully.';
		}		 
		header("Location: assign.php");
		die();
	}
	include($BF. 'includes/meta.php');	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
//	$bodyParams = 'onload="document.getElementById(\'intInvites\').focus()"';
	
	//Load drop down menus for the page
	$sponsor = fetch_database_query("SELECT ID,chrFirst,chrLast FROM Users WHERE !bDeleted AND ID=". $_REQUEST['id'], "getting sponsor");
	$users = database_query("SELECT idUser FROM SponsorsByUser JOIN Users ON idUser=Users.ID WHERE !Users.bDeleted AND idSponsor='". $_REQUEST['id'] ."'", "getting user array");
	$userArray = array();
	while($row = mysqli_fetch_assoc($users)) {
		$userArray[] = $row['idUser'];
	}

	$users = database_query("SELECT ID,chrFirst,chrLast FROM Users WHERE !bDeleted AND idRight!=2 ORDER BY chrLast,chrFirst", "getting users");
		
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

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
	<div class='instructions'>To update the list of associated users, choose your users and click the update information button.</div>

	<div class='innerbody'>
		<?=messages()?>
		<div id='errors'></div>
		<div id='showinfo'></div>
		
		<table class='twoCol'>
			<tr>
				<td class='left' style="width:75px;">
					
					<div class='FormName'>Sponsor</div>
					<div class='FormField'><?=$sponsor['chrFirst']?> <?=$sponsor['chrLast']?></div>
				
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
							<td<?=(in_array($row['ID'],$userArray) ? ' style="background:#CCC;"' : '')?>><input<?=(in_array($row['ID'],$userArray) ? ' checked' : '')?> type='checkbox' name='users[]' value='<?=$row["ID"]?>'> <?=$row['chrLast']?>, <?=$row['chrFirst']?></td>
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

		<input class='FormButtons' type='button' value='Update Information' onclick="document.getElementById('moveTo').value='assign.php'; error_check();" /> &nbsp;&nbsp; 
		<input type='hidden' name='moveTo' id='moveTo' />
		<input type='hidden' name='idSponsor' value='<?=$_REQUEST["id"]?>' />

	</div>

</form>
<?
	include($BF. 'includes/bottom.php');
?>
