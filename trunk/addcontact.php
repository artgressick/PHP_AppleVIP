<?	
	$BF = '';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Add Contact Page';      // Title to display at the top of the browser window.
	$active = "addressbook";           // This needs to be set for the nav bar at the top to know which section to highlight..
	require($BF. '_lib.php');
	
	(!isset($_REQUEST['d']) ? ErrorPage() : "" );
	
	parse_str(base64_decode($_REQUEST['d']),$info);
	(($info['key'] != $_SESSION['idUser']) || !isset($info['to']) || !is_numeric($info['to']) ? ErrorPage() : "" );
	
	if(isset($_POST['chrFirst'])) { // When doing isset, use a required field.  Faster than the php count funtion.
		$q = "INSERT INTO Contacts SET 
			bType='". $_POST['bType'] ."',
			bAltEmail='". $_POST['bAltEmail'] ."',
			bAltMail='". $_POST['bAltMail'] ."',
			idUser='". $_POST['idTo'] ."',
			idCategory='". $_POST['idCategory'] ."',
			chrFirst='". encode($_POST['chrFirst']) ."',
			chrLast='". encode($_POST['chrLast']) ."',
			chrAlias='". encode($_POST['chrAlias']) ."',
			chrCompany='". encode($_POST['chrCompany']) ."',
			chrTitle='". encode($_POST['chrTitle']) ."',
			chrAddress1='". encode($_POST['chrAddress1']) ."',
			chrAddress2='". encode($_POST['chrAddress2']) ."',
			chrCity='". encode($_POST['chrCity']) ."',
			chrState='". encode($_POST['chrState']) ."',
			chrPostalCode='". strip_quotes($_POST['chrPostalCode']) ."',
			chrCountry='". encode($_POST['chrCountry']) ."',
			chrPhone='". strip_quotes($_POST['chrPhone']) ."',
			chrFax='". strip_quotes($_POST['chrFax']) ."',
			chrMobile='". strip_quotes($_POST['chrMobile']) ."',
			chrEmail='". strip_quotes($_POST['chrEmail']) ."',
			chrURL='". $_POST['chrURL'] ."',			
			chrAltFirst='". encode($_POST['chrAltFirst']) ."',
			chrAltLast='". encode($_POST['chrAltLast']) ."',
			chrAltTitle='". encode($_POST['chrAltTitle']) ."',
			chrAltAddress1='". encode($_POST['chrAltAddress1']) ."',
			chrAltAddress2='". encode($_POST['chrAltAddress2']) ."',
			chrAltCity='". encode($_POST['chrAltCity']) ."',
			chrAltState='". encode($_POST['chrAltState']) ."',
			chrAltPostalCode='". encode($_POST['chrAltPostalCode']) ."',
			chrAltCountry='". encode($_POST['chrAltCountry']) ."',
			chrAltPhone='". strip_quotes($_POST['chrAltPhone']) ."',
			chrAltFax='". strip_quotes($_POST['chrAltFax']) ."',
			chrAltMobile='". strip_quotes($_POST['chrAltMobile']) ."',
			chrAltEmail='". strip_quotes($_POST['chrAltEmail']) ."'
		";
		if(database_query($q,"Insert into contacts")) {
			
			
			
		
			// This is the code for inserting the Audit Page
			// Type 1 means ADD NEW RECORD, change the TABLE NAME also
			global $mysqli_connection;  // This is needed for mysqli to be able to get the "last insert id"
			$newID = mysqli_insert_id($mysqli_connection);
	
	
	
		if($_POST['txtNote'] != "") { // When doing isset, use a required field.  Faster than the php count funtion.
			$q = "INSERT INTO Notes SET 
				idType='2',
				idUser='". $_SESSION['idUser'] ."',
				idRecord='". $newID ."',
				txtNote='". encode($_POST['txtNote']) ."',
				dtStamp=now()
			";
			database_query($q,"Insert into Notes");
			}
					
			$q = "INSERT INTO Audit SET 
				idType=1, 
				idRecord='". $newID ."',
				txtNewValue='". encode($_POST['chrFirst']) ." ". encode($_POST['chrLast']) ."',
				dtDateTime=now(),
				chrTableName='Contacts',
				idUser='". $_SESSION['idUser'] ."'
			";
			database_query($q,"Insert audit");
			//End the code for History Insert
			$_SESSION['infoMessages'][] = $_POST['chrFirst'].' '.$_POST['chrLast'].' has been added successfully.';
		} else {
			ErrorPage('An Error has occurred while trying to add this contact. Please contact an administrator.');
		}
			header("Location: ". $_POST['moveTo'] ."?d=".$_REQUEST['d']);
			die();

	}

	$q = "SELECT ID, chrFirst,chrLast
		FROM Users
		WHERE !bDeleted AND ID=". $info['to'];
	$userinfo = fetch_database_query($q,"Getting all contacts");

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
		total += ErrorCheck('idCategory', "You must choose a Category.");
//		total += ErrorCheck('chrEmail', "You must enter an Email Address.","email");

		if(total == 0) { document.getElementById('idForm').submit(); } else { window.scrollTo(0,0); }
	}
</script>
<?
	include($BF. 'includes/top.php');
?>

<form name='idForm' id='idForm' action='' method="post">
	<input type="hidden" name="idTo" id="idTo" value="<?=$info['to']?>" />
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
		<tr>
			<td class="left"></td>
			<td class="title">Add Contact</td>
			<td class="title_right" style="color:#FFFFFF; font-weight:bolder;">Managing Address Book for <?=$userinfo['chrFirst']?> <?=$userinfo['chrLast']?></td>
			<td class="right"></td>
		</tr>
	</table>
	<div class='instructions'>To add a contact, fill in all the information and click on the "Add Contact" button.</div>

	<div class='innerbody'>
		<?=messages()?>
		<div id='errors'></div>
		<div id='showinfo'></div>
		
		<table class='twoCol'>
			<tr>
				<td class='left'>
					<div style="margin-left:2px; font-size:13px; font-weight:bold; margin-bottom:20px; margin-top:10px;">Main Contact Information</div>
					
					<div class='FormName'>First Name <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrFirst' id='chrFirst' size='40' /></div>

					<div class='FormName'>Last Name <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrLast' id='chrLast' size='40' /></div>
					
					<div class='FormName'>Type </div>
					<div class='FormField'><input name="bType" type="radio" value="0" checked /><img src="images/circle_gold.png" style="margin-top:4px;" />&nbsp;&nbsp;
					<input name="bType" type="radio" value="1" /> <img src="images/circle_red.png" style="margin-top:4px;" /></div>
					
					<div class='FormName'>Alias </div>
					<div class='FormField'><input type='text' name='chrAlias' id='chrAlias' size='40' /></div>
					
					<div class='FormName'>Category <span class='Required'>(Required)</span></div>
					<div class='FormField'><select name="idCategory" id="idCategory" size="1">
					    <option value="">Please choose</option>
<?
	$categories = database_query("SELECT ID,chrCategory FROM Categories WHERE !bDeleted ORDER BY chrCategory", "getting contact categories");
	while($row = mysqli_fetch_assoc($categories)) { ?>
				    	<option value="<?=$row["ID"]?>"><?=$row['chrCategory']?></option>
<?
	}
?>
					</select></div>

					<div class='FormName'>Company</div>
					<div class='FormField'><input type='text' name='chrCompany' id='chrCompany' size='40' /></div>
					
					<div class='FormName'>Title</div>
					<div class='FormField'><input type='text' name='chrTitle' id='chrTitle' size='40' /></div>
					
					<div class='FormName'>Address</div>
					<div class='FormField'><input type='text' name='chrAddress1' id='chrAddress1' size='40' /><br />
						<input type='text' name='chrAddress2' id='chrAddress2' size='40' /></div>

					<div class='FormName'>City</div>
					<div class='FormField'><input type='text' name='chrCity' id='chrCity' size='40' /></div>

					<div class='FormName'>State</div>
					<div class='FormField'><input type='text' name='chrState' id='chrState' size='40' /></div>

					<div class='FormName'>Postal/Zip Code</div>
					<div class='FormField'><input type='text' name='chrPostalCode' id='chrPostalCode' size='20' /></div>

					<div class='FormName'>Country</div>
					<div class='FormField'><input type='text' name='chrCountry' id='chrCountry' size='40' /></div>
					
					<div class='FormName'>Phone</div>
					<div class='FormField'><input type='text' name='chrPhone' id='chrPhone' size='25' /></div>
					
					<div class='FormName'>Fax</div>
					<div class='FormField'><input type='text' name='chrFax' id='chrFax' size='25' /></div>
			
					<div class='FormName'>Mobile</div>
					<div class='FormField'><input type='text' name='chrMobile' id='chrMobile' size='25' /></div>
					
					<div class='FormName'>Email</div>
					<div class='FormField'><input type='text' name='chrEmail' id='chrEmail' size='40' /></div>
					
					<div class='FormName'>URL</div>
					<div class='FormField'><input type='text' name='chrURL' id='chrURL' size='40' /></div>
			
				</td>
				<td class='gutter'>
				</td>
				<td class='right'>
					<div style="margin-left:2px; font-size:13px; font-weight:bold; margin-bottom:20px; margin-top:10px;">Alternate Contact Information</div>
					
					<div class='FormName'>Alternate First Name</div>
					<div class='FormField'><input type='text' name='chrAltFirst' id='chrAltFirst' size='40' /></div>

					<div class='FormName'>Alternate Last Name</div>
					<div class='FormField'><input type='text' name='chrAltLast' id='chrAltLast' size='40' /></div>
					
					<div class='FormName'>Title</div>
					<div class='FormField'><input type='text' name='chrAltTitle' id='chrAltTitle' size='40' /></div>
					
					<div class='FormName'>Address</div>
					<div class='FormField'><input type='text' name='chrAltAddress1' id='chrAltAddress1' size='40' /><br />
						<input type='text' name='chrAltAddress2' id='chrAltAddress2' size='40' /></div>

					<div class='FormName'>City</div>
					<div class='FormField'><input type='text' name='chrAltCity' id='chrAltCity' size='40' /></div>

					<div class='FormName'>State</div>
					<div class='FormField'><input type='text' name='chrAltState' id='chrAltState' size='40' /></div>

					<div class='FormName'>Postal/Zip Code</div>
					<div class='FormField'><input type='text' name='chrAltPostalCode' id='chrAltPostalCode' size='20' /></div>

					<div class='FormName'>Country</div>
					<div class='FormField'><input type='text' name='chrAltCountry' id='chrCountry' size='40' /></div>
					
					<div class='FormName'>Phone</div>
					<div class='FormField'><input type='text' name='chrAltPhone' id='chrAltPhone' size='25' /></div>
					
					<div class='FormName'>Fax</div>
					<div class='FormField'><input type='text' name='chrAltFax' id='chrAltFax' size='25' /></div>
			
					<div class='FormName'>Mobile Phone</div>
					<div class='FormField'><input type='text' name='chrAltMobile' id='chrAltMobile' size='25' /></div>
					
					<div class='FormName'>Email</div>
					<div class='FormField'><input type='text' name='chrAltEmail' id='chrAltEmail' size='40' /></div>

					<div class='FormName'>Send email to alternate? </div>
					<div class='FormField'><input name="bAltEmail" type="radio" value="0" checked /> No&nbsp;&nbsp;
					<input name="bAltEmail" type="radio" value="1" /> Yes</div>
					
					<div class='FormName'>Send postal mail to alternate? </div>
					<div class='FormField'><input name="bAltMail" type="radio" value="0" checked /> No&nbsp;&nbsp;
					<input name="bAltMail" type="radio" value="1" /> Yes</div>
					
					<div class='FormName'>Add Notes</div>
					<div class='FormField'><textarea name="txtNote" id="txtNote" cols="40" rows="6" wrap="virtual"></textarea></div>
						
				</td>
			</tr>
		</table>

		<input class='FormButtons' type='button' value='Add Contact And Return' onclick="document.getElementById('moveTo').value='addcontact.php'; error_check();" /> &nbsp;&nbsp; 
		<input class='FormButtons' type='button' value='Add And Move On' onclick="document.getElementById('moveTo').value='addressbook.php'; error_check();" />
		<input type='hidden' name='moveTo' id='moveTo' />

	</div>
	<table cellpadding='0' cellspacing='0' style='padding-top:10px;'>
		<tr>
			<td style='width:10px; vertical-align:middle; text-align:center;'><img src="<?=$BF?>images/circle_gold.png" /></td>
			<td style='vertical-align:left; text-align:center; padding:0 20px 0 5px;'>Guest or VIP</td>
			<td style='width:10px; vertical-align:middle; text-align:center;'><img src="<?=$BF?>images/circle_red.png" /></td>
			<td style='vertical-align:left; text-align:center; padding:0 20px 0 5px;'>Special Guest</td>
		</tr>
	</table>							

</form>
<?
	include($BF. 'includes/bottom.php');
?>
