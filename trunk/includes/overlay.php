	<div id='overlaypage' class='overlaypage'>
		<div id='gray' class='gray'></div>
		<div id='message' class='message'>
			<div class='warning' id='warning'>
				<div class='red'>WARNING!!</div>
				<div class='body'>
					<div>You are about to remove: <br />
		
						Name: <span id='delName' style='color: blue;'></span><br />
						<input type='hidden' value='' id='idDel' name='idDel' />
						<input type='hidden' value='' id='chrTablePrefix' name='chrTablePrefix' />
					</div>
					<div style='margin-top: 20px; '><strong>Are you sure you want to do this? It cannot be undone!</strong><br />
						<input type='button' value='Yes' onclick="javascript:delItem('<?=$BF?>ajax_delete.php?postType=delete&tbl=<?=$TableName?>&idUser=<?=$_SESSION['idUser']?>&id=');" /> &nbsp;&nbsp; <input type='button' value='No' onclick="javascript:revert();" />
					</div>
				</div>
			</div>
		</div>
	</div>