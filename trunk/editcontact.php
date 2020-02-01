<?	
	$BF = '';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Edit Contact Page';      // Title to display at the top of the browser window.
	$active = "addressbook";           // This needs to be set for the nav bar at the top to know which section to highlight..
	require($BF. '_lib.php');

	parse_str(base64_decode($_REQUEST['d']),$inf);
	
	if ( $inf['key'] != $_SESSION['idUser'] ) { die(); }

	// Get info to populate fields. Also ... If the old information is the same as the current, why update it?  Get the old information to test this against.
	$info = fetch_database_query("SELECT * FROM Contacts WHERE ID=". $_REQUEST['id'],"getting user info");

		// Grab Notes
		// Type 1 = Invites, Type 2 = Address Book (This is usually the table)
		// idRecord = ID of Table to link with this entry.
		
		
	if(isset($_POST['chrFirst'])) { 

		// Set the basic values to be used.
		//   $table = the table that you will be connecting to to check / make the changes
		//   $mysqlStr = this is the "mysql string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		//   $sudit = this is the "audit string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		$table = 'Contacts';
		$mysqlStr = '';
		$audit = '';

		// "List" is a way for php to split up an array that is coming back.  
		// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
		//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
		//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
		//    ...  This also will ONLY add changes to the audit table if the values are different.
		list($mysqlStr,$audit) = set_strs($mysqlStr,'bType',$info['bType'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'bAltEmail',$info['bAltEmail'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'bAltMail',$info['bAltMail'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'idCategory',$info['idCategory'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrFirst',$info['chrFirst'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrLast',$info['chrLast'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAlias',$info['chrAlias'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCompany',$info['chrCompany'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrTitle',$info['chrTitle'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAddress1',$info['chrAddress1'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAddress2',$info['chrAddress2'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCity',$info['chrCity'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrState',$info['chrState'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrPostalCode',$info['chrPostalCode'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCountry',$info['chrCountry'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrPhone',$info['chrPhone'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrFax',$info['chrFax'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrMobile',$info['chrMobile'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrEmail',$info['chrEmail'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrURL',$info['chrURL'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAltFirst',$info['chrAltFirst'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAltLast',$info['chrAltLast'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAltTitle',$info['chrAltTitle'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAltAddress1',$info['chrAltAddress1'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAltAddress2',$info['chrAltAddress2'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAltCity',$info['chrAltCity'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAltState',$info['chrAltState'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAltPostalCode',$info['chrAltPostalCode'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAltCountry',$info['chrAltCountry'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAltPhone',$info['chrAltPhone'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAltFax',$info['chrAltFax'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAltMobile',$info['chrAltMobile'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAltEmail',$info['chrAltEmail'],$audit,$table,$_POST['id']);
		
		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_POST['id']);
			$_SESSION['infoMessages'][] = $_POST['chrFirst'].' '.$_POST['chrLast'].' has been Updated successfully';
		} else {
			ErrorPage('An Error has occurred while trying to add this contact. Please contact an administrator.');
		}

		// When the page is done updating, move them back to whatever the list page is for the section you are in.
		header("Location: addressbook.php?d=".$_REQUEST['d']);
		die();
	}
	include($BF. 'includes/meta.php');
	
	
		$q = "SELECT ID, chrFirst,chrLast
		FROM Users
		WHERE !bDeleted AND ID=". $inf['to'];
	$userinfo = fetch_database_query($q,"Getting all contacts");
	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = 'onload="document.getElementById(\'chrFirst\').focus()"';

?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>

<script language="javascript">
	function error_check() {
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

<form name='idForm' id='idForm' method="post">

	<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
		<tr>
			<td class="left"></td>
			<td class="title">Edit Contact</td>
			<td class="title_right" style="color:#FFFFFF; font-weight:bolder;">Managing Address Book for <?=$userinfo['chrFirst']?> <?=$userinfo['chrLast']?></td>
			<td class="right"></td>
		</tr>
	</table>
	<div class='instructions'>To edit a contact, fill in all the information and click on the "Update Information" button.</div>

	<div class='innerbody'>
		<?=messages()?>
		<div id='errors'></div>
		<div id='showinfo'></div>
		
		<table class='twoCol'>
			<tr>
				<td class='left'>

					<div style="margin-left:2px; font-size:13px; font-weight:bold; margin-bottom:20px; margin-top:10px;">Main Contact Information</div>
					
					<div class='FormName'>First Name <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrFirst' id='chrFirst' size='40' value='<?=$info['chrFirst']?>' /></div>

					<div class='FormName'>Last Name <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='chrLast' id='chrLast' size='40' value='<?=$info['chrLast']?>' /></div>
					
					<div class='FormName'>Type </div>
					<div class='FormField'>
						<input name="bType" type="radio" value="0" <?=($info['bType'] == 0 ? ' checked ' : '')?> /> 
						<img src="images/circle_gold.png" style="margin-top:4px;" />&nbsp;&nbsp;
						<input name="bType" type="radio" value="1" <?=($info['bType'] == 1 ? ' checked ' : '')?> /> 
						<img src="images/circle_red.png" style="margin-top:4px;" />
					</div>
					
					<div class='FormName'>Alias </div>
					<div class='FormField'><input type='text' name='chrAlias' id='chrAlias' size='40' value='<?=$info['chrAlias']?>' /></div>
					
					<div class='FormName'>Category <span class='Required'>(Required)</span></div>
					<div class='FormField'><select name="idCategory" size="1" id="idCategory">
					    <option value="">Please choose</option>
<?
	$categories = database_query("SELECT ID,chrCategory FROM Categories WHERE !bDeleted ORDER BY chrCategory", "getting contact categories");
	while($row = mysqli_fetch_assoc($categories)) { ?>
				    	<option<?=($info['idCategory'] == $row["ID"] ? ' selected ' : '')?> value="<?=$row["ID"]?>"><?=$row['chrCategory']?></option>
<?
	}
?>
					</select></div>

					<div class='FormName'>Company</div>
					<div class='FormField'><input type='text' name='chrCompany' id='chrCompany' size='40' value='<?=$info['chrCompany']?>' /></div>
					
					<div class='FormName'>Title</div>
					<div class='FormField'><input type='text' name='chrTitle' id='chrTitle' size='40' value='<?=$info['chrTitle']?>' /></div>
					
					<div class='FormName'>Address</div>
					<div class='FormField'><input type='text' name='chrAddress1' id='chrAddress1' size='40' value='<?=$info['chrAddress1']?>' /><br />
						<input type='text' name='chrAddress2' id='chrAddress2' size='40' value='<?=$info['chrAddress2']?>' /></div>

					<div class='FormName'>City</div>
					<div class='FormField'><input type='text' name='chrCity' id='chrCity' size='40' value='<?=$info['chrCity']?>' /></div>

					<div class='FormName'>State</div>
					<div class='FormField'><input type='text' name='chrState' id='chrState' size='40' value='<?=$info['chrState']?>' /></div>

					<div class='FormName'>Postal/Zip Code</div>
					<div class='FormField'><input type='text' name='chrPostalCode' id='chrPostalCode' size='20' value='<?=$info['chrPostalCode']?>' /></div>

					<div class='FormName'>Country</div>
					<div class='FormField'><input type='text' name='chrCountry' id='chrCountry' size='40' value='<?=$info['chrCountry']?>' /></div>
					
					<div class='FormName'>Phone</div>
					<div class='FormField'><input type='text' name='chrPhone' id='chrPhone' size='25' value='<?=$info['chrPhone']?>' /></div>
					
					<div class='FormName'>Fax</div>
					<div class='FormField'><input type='text' name='chrFax' id='chrFax' size='25' value='<?=$info['chrFax']?>' /></div>
			
					<div class='FormName'>Mobile</div>
					<div class='FormField'><input type='text' name='chrMobile' id='chrMobile' size='25' value='<?=$info['chrMobile']?>' /></div>
					
					<div class='FormName'>Email</div>
					<div class='FormField'><input type='text' name='chrEmail' id='chrEmail' size='40' value='<?=$info['chrEmail']?>' /></div>
					
					<div class='FormName'>URL</div>
					<div class='FormField'><input type='text' name='chrURL' id='chrURL' size='40' value='<?=$info['chrURL']?>' /></div>
			
				</td>
				<td class='right'>
					<div style="margin-left:2px; font-size:13px; font-weight:bold; margin-bottom:20px; margin-top:10px;">Alternate Contact Information</div>
					
					<div class='FormName'>Alternate First Name</div>
					<div class='FormField'><input type='text' name='chrAltFirst' id='chrAltFirst' size='40' value='<?=$info['chrAltFirst']?>' /></div>

					<div class='FormName'>Alternate Last Name</div>
					<div class='FormField'><input type='text' name='chrAltLast' id='chrAltLast' size='40' value='<?=$info['chrAltLast']?>' /></div>
					
					<div class='FormName'>Title</div>
					<div class='FormField'><input type='text' name='chrAltTitle' id='chrAltTitle' size='40' value='<?=$info['chrAltTitle']?>' /></div>
					
					<div class='FormName'>Address</div>
					<div class='FormField'><input type='text' name='chrAltAddress1' id='chrAltAddress1' size='40' value='<?=$info['chrAltAddress1']?>' /><br />
						<input type='text' name='chrAltAddress2' id='chrAltAddress2' size='40' value='<?=$info['chrAltAddress2']?>' /></div>

					<div class='FormName'>City</div>
					<div class='FormField'><input type='text' name='chrAltCity' id='chrAltCity' size='40' value='<?=$info['chrAltCity']?>' /></div>

					<div class='FormName'>State</div>
					<div class='FormField'><input type='text' name='chrAltState' id='chrAltState' size='40' value='<?=$info['chrAltState']?>' /></div>

					<div class='FormName'>Postal/Zip Code</div>
					<div class='FormField'><input type='text' name='chrAltPostalCode' id='chrAltPostalCode' size='20' value='<?=$info['chrAltPostalCode']?>' /></div>

					<div class='FormName'>Country</div>
					<div class='FormField'><input type='text' name='chrAltCountry' id='chrCountry' size='40' value='<?=$info['chrCountry']?>' /></div>
					
					<div class='FormName'>Phone</div>
					<div class='FormField'><input type='text' name='chrAltPhone' id='chrAltPhone' size='25' value='<?=$info['chrAltPhone']?>' /></div>
					
					<div class='FormName'>Fax</div>
					<div class='FormField'><input type='text' name='chrAltFax' id='chrAltFax' size='25' value='<?=$info['chrAltFax']?>' /></div>
			
					<div class='FormName'>Mobile Phone</div>
					<div class='FormField'><input type='text' name='chrAltMobile' id='chrAltMobile' size='25' value='<?=$info['chrAltMobile']?>' /></div>
					
					<div class='FormName'>Email</div>
					<div class='FormField'><input type='text' name='chrAltEmail' id='chrAltEmail' size='40' value='<?=$info['chrAltEmail']?>' /></div>

					<div class='FormName'>Send email to alternate? </div>
					<div class='FormField'>
						<input name="bAltEmail" type="radio" value="0" <?=($info['bAltEmail'] == 0 ? ' checked ' : '')?> /> 
						Yes&nbsp;&nbsp;
						<input name="bAltEmail" type="radio" value="1" <?=($info['bAltEmail'] == 1 ? ' checked ' : '')?> /> No
					</div>
					
					<div class='FormName'>Send postal mail to alternate? </div>
					<div class='FormField'>
						<input name="bAltMail" type="radio" value="0" <?=($info['bAltMail'] == 0 ? ' checked ' : '')?> /> 
						Yes&nbsp;&nbsp;
						<input name="bAltMail" type="radio" value="1" <?=($info['bAltMail'] == 1 ? ' checked ' : '')?> /> No
					</div>
				</td>
				<td class='right'>
						<table class='title' style='width: 200px; border: 0; padding: 0; margin: 0; margin-top:10px;' cellpadding="0" cellspacing="0">
							<tr>
								<td style='font-size: 14px; font-weight: bold; margin-top: 10px;'>Notes:</td>
								<td style="text-align:right;"><a style='cursor: pointer;' onclick='javascript:window.open("popup_notes.php?id=<?=$_REQUEST['id']?>&amp;type=2","new","width=600,height=400,resizable=1,scrollbars=1");'>[ADD NOTE]</a></td>
								</tr>
								<tr>
									<td colspan="2">
								<div style='border:1px solid #999; height:200px; padding:5px; overflow:auto; background:white;'>
<?
				$notestmp = database_query("SELECT N.dtStamp, CONCAT(N.txtNote,'<br /> by ',U.chrFirst,' ',U.chrLast) as txtNote 
											FROM Notes AS N
											JOIN Users AS U ON N.idUser=U.ID
											WHERE N.idType=2 AND N.idRecord='".$info['ID']."' ORDER BY N.dtStamp DESC","Getting Contact Notes");
				$notes = array();
				while($row = mysqli_fetch_assoc($notestmp)) {
					$notes[$row['dtStamp']] = $row['txtNote'];
				}
				$notestmp = database_query("SELECT N.dtStamp, CONCAT(S.chrName,'<br/>',N.txtNote) AS txtNote 
											FROM Invites AS I
											JOIN Notes AS N ON N.idType=3 AND idRecord=I.ID
											JOIN Shows AS S ON I.idShow=S.ID 
											WHERE I.idContact='".$info['ID']."' 
											ORDER BY dtStamp DESC","Getting Invite Notes");
				while($row = mysqli_fetch_assoc($notestmp)) {
					$notes[$row['dtStamp']] = $row['txtNote'];
				}
				krsort($notes);
				
				$cnt = 0;
				foreach($notes AS $k => $note) {
					$cnt++;
?>
									<p><?=date('F j, Y g:i a',strtotime($k))?><br/><?=nl2br($note)?></p>
<?					
				}
				if($cnt == 0) {
?>
									<p>No notes to display.</p>
<?					
				}
?>				
								</div>
								</td>
							</tr>
						</table>	

<?
				// Lets get the notes for this show.
				$history = database_query("SELECT S.chrName AS chrShow, I.dtStamp, Status.chrStatus
											FROM Shows AS S
											JOIN Invites AS I ON I.idShow=S.ID
											JOIN iStatus AS Status ON I.idStatus=Status.ID
											WHERE !I.bDeleted AND I.idContact='".$info['ID']."'
											ORDER BY dtStamp DESC","Getting Invite History");
?>
						<table class='title' style='width: 200px; border: 0; padding: 0; margin: 0; padding-top:10px;' cellpadding="0" cellspacing="0">
							<tr>
								<td style='font-size: 14px; font-weight: bold; margin-top: 10px;'>Event History:</td>
							</tr>
							<tr>
								<td>
							<div style='border:1px solid #999; height:200px; padding:5px; overflow:auto; background:white;'>
<?
				$cnt = 0;
				while($row = mysqli_fetch_assoc($history)) {
					$cnt++;
?>
								<p><?=date('F j, Y g:i a',strtotime($row['dtStamp']))?><br/>-<?=$row['chrShow'].'<br />&nbsp;&nbsp;Status: '.$row['chrStatus']?></p>
<?					
				}
				if($cnt == 0) {
?>
								<p>No History to display.</p>
<?					
				}
?>				
							</div>
							</td>
						</tr>
					</table>	
				</td>
			</tr>
		</table>

		<input class='FormButtons' type='button' value='Update Information' onclick="error_check()" />
		<input type='hidden' name='id' value='<?=$_REQUEST['id']?>' >
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
