<?php
	$BF = "";
	include($BF.'_lib.php');
	$title = 'Check-In Page';
	$active = '';
	if (!isset($_REQUEST['idShow']) && !isset($_SESSION['idShow'])) { $_REQUEST['idShow'] = ""; }
	 else if (isset($_SESSION['idShow']) && !isset($_REQUEST['idShow'])) { $_REQUEST['idShow'] = $_SESSION['idShow']; }
	
	$Shows = database_query("SELECT ID,chrName FROM Shows WHERE !bDeleted AND idStatus = 3 ORDER BY Shows.dBegin DESC, Shows.chrName", "getting shows");

	if(mysqli_num_rows($Shows) > 0 && isset($_REQUEST['idShow']) && $_REQUEST['idShow'] > 0 && is_numeric($_REQUEST['idShow'])) {
		$showPage = 1;
	} else {
		$showPage = 0;
	}
	
if($showPage) { 
	$intRecords = 0;
	$chrDisplay = "TRUE";

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrContactLast"; }	

	if(!isset($_REQUEST['chrChr'])) { $_REQUEST['chrChr'] = ''; }
	if(!isset($_REQUEST['chrSearch'])) { $_REQUEST['chrSearch'] = ''; }
	if(!isset($_REQUEST['idStatus'])) { $_REQUEST['idStatus'] = '2'; }
	if(!isset($_REQUEST['search'])) { $_REQUEST['search'] = ''; }
	
	if ($_REQUEST['chrChr'] != '' || $_REQUEST['search'] != '') {
		
		$query = "SELECT Invites.ID, Contacts.chrFirst AS chrContactFirst, Contacts.chrLast AS chrContactLast, Contacts.chrCompany, Invites.idStatus, chrStatus, Invites.bType, Contacts.idCategory, chrCategory,Users.chrFirst AS chrUserFirst, Users.chrLast AS chrUserLast,
			(SELECT DATE_FORMAT(dtStamp, '%h:%i %p') from Checkin where Checkin.idInvite = Invites.ID LIMIT 1) as dtTime
			FROM Invites
			JOIN Users ON Invites.idUser=Users.ID
			JOIN Contacts ON Invites.idContact=Contacts.ID
			LEFT JOIN Categories ON Contacts.idCategory=Categories.ID
			JOIN iStatus ON Invites.idStatus=iStatus.ID
			JOIN SponsorsbyShow ON SponsorsbyShow.idShow=Invites.idShow AND SponsorsbyShow.idUser=Users.ID
			WHERE !Invites.bDeleted AND !SponsorsbyShow.bDeleted AND !Contacts.bDeleted AND !Users.bDeleted AND SponsorsbyShow.idReviewStatus=3 AND Invites.idShow='". $_REQUEST['idShow'] ."' AND Invites.idStatus != 4";

		
		if($_REQUEST['search'] == 'Search All') { $_REQUEST['chrChr'] = ''; }
		
		if(strlen($_REQUEST['chrChr']) == 1 && preg_match('/^[A-Z]$/',strtoupper($_REQUEST['chrChr']))) {
			$query .= " AND lower(Contacts.chrLast) LIKE '".strtolower($_REQUEST['chrChr'])."%' ";
		}
		
		if($_REQUEST['chrSearch'] != '') {
			$chrSearch = encode($_REQUEST['chrSearch']);
			$possiblebadge = ltrim(substr($chrSearch, 5, -3), '0');
		
			$query .= " AND ((lower(Contacts.chrFirst) LIKE '%" . strtolower($chrSearch) . "%' 
						OR lower(Contacts.chrLast) LIKE '%" . strtolower($chrSearch) . "%' 
						OR lower(concat(Contacts.chrFirst,' ',Contacts.chrLast)) LIKE '%" . strtolower($chrSearch) . "%'
						OR lower(chrCompany) LIKE '%" . $chrSearch . "%')
						OR (Contacts.ID = '" . $possiblebadge ."')) ";
		}
	
		if($_REQUEST['idStatus'] == 1) {
			$query .= " AND Invites.ID in (SELECT idInvite FROM Checkin WHERE idShow='".$_REQUEST['idShow']."') ";
		} else if ( $_REQUEST['idStatus'] == 2 ) {
			$query .= " AND Invites.ID not in (SELECT idInvite FROM Checkin WHERE idShow='".$_REQUEST['idShow']."') ";
		}

		//This is where we need to do the sorting
		$query .= " ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
	
		$result = database_query($query,"Running Query");
		} else {
		//Dont display anything
		$chrDisplay = "FALSE";
	}
}
	include($BF.'includes/meta.php');
	include($BF.'includes/topcheckin.php');
	//Load drop down menus for the page
?>
<!-- This is the main body of the page.-->
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">
		<strong>Checkin Desk for Show:</strong> 
					<select name='idShow id='idShow' onchange='location.href="checkin.php?idShow="+this.value'>
				<option value=''>-Select Show-</option>
				<?	while($row = mysqli_fetch_assoc($Shows)) { ?>
							<option <?=(isset($_REQUEST['idShow'])) ? ($_REQUEST['idShow'] == $row["ID"] ? ' selected ' : '') : '' ?> value='<?=$row["ID"]?>'><?=$row['chrName']?></option>
				<?	} ?>
			</select>
		</td>
		<td class="title_right">

		</td>
		<td class="right"></td>
	</tr>
</table>
<div class='instructions'>Select a show from the list above, use fields below to Check-in the guest.</div>
<?
if($showPage) {
?>
<div class='innerbody'>
<?=messages()?>
<table width="800" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td>
      <table width="100%"  border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td id=cont>
          <form name="form1" method="get" action="">
          <input type='hidden' name='idShow' value='<?=$_REQUEST['idShow']?>' />
          <input type='hidden' name='chrChr' value='<?=$_REQUEST['chrChr']?>' />
            <table width="100%"  border="0" cellspacing="0" cellpadding="5">
              <tr>
              	<td align='center'>
<? $url = 'idShow='.$_REQUEST['idShow'].'&idStatus='.$_REQUEST['idStatus']; ?>
					<table cellpadding="0" border="0" cellspacing="0" class='Tabs' style='padding-top:5px;padding-left:5px;' align='center'>
						<tr>
							<td class='<?=("All"==$_REQUEST['chrChr']?"Current":"")?>'><a href='checkin.php?chrChr=All&<?=$url?>' style="padding: 0 5px;">ALL</a></td>
<? 
			$char = 65;
			$end = 90;
			while ($char <= $end ) {
				$chrChr = chr($char++);
?>
							<td class='<?=($chrChr==strtoupper($_REQUEST['chrChr'])?"Current":"")?>'><a href='checkin.php?chrChr=<?=$chrChr?>&<?=$url?>' style="padding: 0 5px;"><?=$chrChr?></a></td>
<?
			}
?>
							</tr>
						</table>
		           	</td>
              </tr>
              <tr>
                <td colspan="2">
					<table width="100%"  border="0" cellspacing="0" cellpadding="3">
					  <tr>
						<td align="right" class="adminbar"><strong>Checked In:</strong>:</td>
						<td class="adminbar">
							  <select name="idStatus" size="1" id="idStatus">
							  <option value="0" <?=($_REQUEST['idStatus'] == 0 ? "selected" : "" )?>>Either</option>
							  <option value="1" <?=($_REQUEST['idStatus'] == 1 ? "selected" : "" )?>>Yes</option>
							  <option value="2" <?=($_REQUEST['idStatus'] == 2 || $_REQUEST['idStatus'] == "" ? "selected" : "" )?>>No</option>
						  </select>
						</td>
						<td align="right" class="adminbar"><strong>Search</strong>:</td>
						<td class="adminbar"><input name="chrSearch" type="text" class="style1" id="chrSearch" size="25" maxlength="30" value="<?=$_REQUEST['chrSearch']?>"></td>
						<td class="adminbar">
<?
	if(strlen($_REQUEST['chrChr']) == 1 && preg_match('/^[A-Z]$/',strtoupper($_REQUEST['chrChr']))) {
?>
							<input type='submit' name='search' value='Search Within &quot;<?=strtoupper($_REQUEST['chrChr'])?>&quot;' />
<?
	}
?>
							<input type='submit' name='search' value='Search All' />
						</td>
					  </tr>
					</table>
				</td>
              </tr>
<? $url = 'idShow='.$_REQUEST['idShow'].'&idStatus='.$_REQUEST['idStatus'].'&chrSearch='.urlencode($_REQUEST['chrSearch']).'&chrChr='.strtoupper($_REQUEST['chrChr']); ?>
			  <? if ( $chrDisplay == "TRUE" ) { ?>
			  <tr>
			  	<td>
						<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
							<tr>
								<th class='headImg' style='width:12px;white-space:nowrap;'></th>		
								<? sortList('Name', 'chrContactLast','',$url); ?>
								<? sortList('Sponsor', 'chrUserLast','',$url); ?>
								<? sortList('Category', 'idCategory','',$url); ?>								
								<? sortList('Company', 'chrCompany','',$url); ?>
								<? sortList('Status', 'idStatus','',$url); ?>
								<? sortList('Checked In', 'dtTime','',$url); ?>
							</tr>
					<? $count=0;	
					$intRecords = mysqli_num_rows($result);
					while ($row = mysqli_fetch_assoc($result)) { 
?>
								<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
								onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
									<td><img src="<?=$BF?>images/<?=($row['bType'] == 1 ? "circle_red" : "circle_gold" )?>.png" width="11" height="11" /></td>		
									<? if ($row['dtTime'] == "") { ?>
									<td style='cursor: pointer;' onclick='location.href="viewattendee.php?id=<?=$row['ID']?>&idShow=<?=$_REQUEST['idShow']?>"'><?=($row['idCategory']==2?'<img src="'.$BF.'images/appleicon.png" width="15" height="15" /> ':'')?><?=$row['chrContactLast']?>, <?=$row['chrContactFirst']?></td>
									<? } else { ?>
									<td><?=($row['idCategory']==2?'<img src="'.$BF.'images/appleicon.png" width="15" height="15" /> ':'')?><?=$row['chrContactLast']?>, <?=$row['chrContactFirst']?></td>
									<? } ?>			
									<td><?=$row['chrUserLast']?>, <?=$row['chrUserFirst']?></td>					
									<td><?=$row['chrCategory']?></td>										
									<td><?=$row['chrCompany']?></td>	
									<td><?=$row['chrStatus']?></td>		
									<td><?=$row['dtTime']?></td>
								</tr>
					<?	} 
					if($count == 0) { ?>
								<tr>
									<td align="center" colspan='8' class='ListLineOdd'>No Users to display</td>
								</tr>
					<?	} ?>
						</table>
					</td>
				</tr>
				<? } ?>

            </table>
          </form></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</div>
<?
} else { 
?>
<div class='innerbody'>
<div style="text-align:center;">No Show Selected</div>
</div>
<?
}
?>
<!-- This is the bottom of the body -->
<?php
	include($BF.'includes/bottom.php');
?>
</body>
</html>
