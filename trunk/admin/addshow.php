<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Add Show';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "shows";		 // This is needed to highlight the show section
	
	require($BF. '_lib.php');

	if(isset($_POST['chrName'])) { // When doing isset, use a required field.  Faster than the php count funtion.
		$q = "INSERT INTO Shows SET 
			 chrName='". encode($_POST['chrName']) ."',
			 idStatus='". $_POST['idShowStatus'] ."',
			 dBegin=now()
		";
		if(database_query($q,"Insert into shows")) {
			
			// This is the code for inserting the Audit Page
			// Type 1 means ADD NEW RECORD, change the TABLE NAME also
			global $mysqli_connection;  // This is needed for mysqli to be able to get the "last insert id"
			$newID = mysqli_insert_id($mysqli_connection);
					
			$q = "INSERT INTO Audit SET 
				idType=1, 
				idRecord='". $newID ."',
				txtNewValue='". encode($_POST['chrName']) ."',
				dtDateTime=now(),
				chrTableName='Shows',
				idUser='". $_SESSION['idUser'] ."'
			";
			database_query($q,"Insert audit");
			//End the code for History Insert
			$_SESSION['infoMessages'][] = $_POST['chrName'].' added successfully.';
		} else {
			ErrorPage('An Error has occurred while trying to add this Show. Please contact Support.');
		}
			
			
		header("Location: ". $_POST['moveTo']);
		die();
	}


	include($BF. 'includes/meta.php');
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = 'onload="document.getElementById(\'chrName\').focus()"';
	
	//Load drop down menus for the page
	$showStatus = database_query("SELECT ID,chrStatus FROM ShowStatus", "getting show status");	
	
?>

<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>

<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrName', "You must enter a Show Name.");
		total += ErrorCheck('idShowStatus', "You must enter a Show Status.");

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
			<td class="title">Add Show</td>
			<td class="title_right"></td>
			<td class="right"></td>
		</tr>
	</table>
	<div class='instructions'>To add a show, fill in all the information and click on the "Add Show" button.</div>

	<div class='innerbody'>
		<?=messages()?>
		<div id='errors'></div>
		<div id='showinfo'></div>
		
		<table class='twoCol'>
			<tr>
				<td class='left'>

					<div class='FormName'>Show Name <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrName' id='chrName' /></div>
								
				</td>
				<td class='gutter'>
				</td>
				<td>
					<div class='FormName'>Show Status <span class='Required'>(Required)</span></div>
					<div class='FormField'>
						<select id='idShowStatus' name='idShowStatus'>
							<option value=''>-Select Show Status-</option>
<?	while($row = mysqli_fetch_assoc($showStatus)) { ?>
							<option value='<?=$row["ID"]?>'><?=$row['chrStatus']?></option>
<?	} ?>
						</select>
					</div>					
				</td>
			</tr>
		</table>

		<input class='FormButtons' type='button' value='Add Show And Return' onclick="document.getElementById('moveTo').value='addshow.php'; error_check();" /> &nbsp;&nbsp; 
		<input class='FormButtons' type='button' value='Add And Move On' onclick="document.getElementById('moveTo').value='index.php'; error_check();" />
		<input type='hidden' name='moveTo' id='moveTo' />

	</div>

</form>
<?
	include($BF. 'includes/bottom.php');
?>
