<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page'],$computerID);

$json = getComputerData($computerID, array("products","startup"));

$query = "SELECT  online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];
$date = strtotime($json['general_lastUpdate']);
if($date < strtotime('-1 days')) {
	$online="0";
}
$programs = $json['products']['Response'];
$startup = $json['startup']['Response'];
$error = $json['products_error'];
?>

<div style="padding:20px;margin-bottom:-1px;" class="card">
	<div class="row" style="padding:15px;">
		<div class="col-md-9">
			<h5 style="color:#0c5460">
				Installed Programs (<?php echo count($programs);?>)
			</h5>
			<span style="font-size:12px;color:#666;">
				Last Update: <?php echo ago($json['products_lastUpdate']);?>
			</span>
		</div>
		<div style="text-align:right;" class="col-md-3">
			<div class="btn-group">
				<button style="background:#0c5460;color:#d1ecf1" onclick="loadSection('Asset_Programs');" type="button" class="btn btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
				<button style="background:#0c5460;color:#d1ecf1" type="button" class="btn dropdown-toggle-split btn-sm" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<i class="fas fa-sort-down"></i>
				</button>
				<div class="dropdown-menu">
					<a onclick="force='true'; loadSection('Asset_Programs','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>
				</div>
			</div>
			<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="$('#olderData_content').html(older_data_modal);olderData('<?php echo $computerID; ?>','Programs','null');">
				<i class="fas fa-scroll"></i>
			</button>
		</div>
	</div>
</div>
<?php if($online=="0"){ ?>
	<div  style="border-radius: 0px 0px 4px 4px;" class="alert alert-danger" role="alert">
		<i class="fas fa-ban"></i>&nbsp;&nbsp;&nbsp;This Agent is offline		
	</div>
<?php 
}else{
	echo"<br>";
}
?>
<div style="overflow-x:auto;padding:10px;background:#fff;border-radius:6px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;">
	<table id="<?php echo $_SESSION['userid']; ?>Programs" style="line-height:20px;overflow:hisdden;font-size:12px;margin-top:8px;font-family:Arial;" class="table-striped table table-hover table-borderless">	
	  <thead>
		<tr style="border-bottom:2px solid #d3d3d3;">
		  <th scope="col">#</th>
		  <th scope="col">Name</th>
		  <th scope="col">Vendor</th>
		  <th scope="col">Installed</th>
		</tr>
	  </thead>
	  <tbody>
		<?php
			$count = 0;
			//Sort The array by Name ASC
			usort($programs, function($a, $b) {
				return $a['Name'] <=> $b['Name'];
			});
			foreach($programs as $key=>$program){
				//ignore empty name
				if(trim($program['Name']) == ""){
					continue;
				}
				if($search!=""){
					if(stripos($program['Name'], $search) !== false){ }else{ continue; }
				}
				$count++;
		?>
			<tr>
			  <th scope="row"><?php echo $count;?></th>
			  <td><?php echo $program['Caption'];?></td>
			  <td><?php echo $program['Vendor'];?></td>
			  <!--<td><?php echo $program['InstallDate'];?></td>--><td>unknown</td>
			</tr>
			<?php }
				if($count == 0){ ?>
					<tr>
						<td colspan=6><center><h6>No programs found.</h6></center></td>
					</tr>
			<?php }?>
	   </tbody>
	</table>
</div><br><br>
<div style="padding:20px;margin-bottom:-1px;" class="card">
	<div class="row" style="padding:15px;">
		<div class="col-md-10">
			<h5 style="color:#0c5460">
				Startup Programs (<?php echo count($startup);?>)
			</h5>
			<span style="font-size:12px;color:#666;">
				Last Update: <?php echo ago($json['startup_lastUpdate']);?>
			</span>
		</div>
		<div style="text-align:right;" class="col-md-2">
			<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="$('#olderData_content').html(older_data_modal);olderData('<?php echo $computerID; ?>','Startup','null');">
				<i class="fas fa-scroll"></i>
			</button>
		</div>
	</div>
</div>
<div style="overflow-x:auto;padding:10px;background:#fff;border-radius:6px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;">
	<table id="<?php echo $_SESSION['userid']; ?>Startup" style="line-height:20px;overflow:auto;font-size:12px;margin-top:8px;font-family:Arial;" class="table-striped table table-hover table-borderless">	
	  <thead>
		<tr style="border-bottom:2px solid #d3d3d3;">
		  <th scope="col">#</th>
		  <th scope="col">Name</th>
		  <th scope="col">Location</th>
		</tr>
	  </thead>
	  <tbody>
		<?php
			$count = 0;
			$program="";
			foreach($startup as $key=>$program){
				//ignore empty name
				if(trim($program['Caption']) == ""){
					continue;
				}
				$count++;
		?>
			<tr>
			  <th scope="row"><?php echo $count;?></th>
			  <td><?php echo $program['Caption'];?></td>
			  <td><?php echo textOnNull($program['Location'],"Unknown");?></td>
			</tr>
			<?php }
				if($count == 0){ ?>
					<tr>
						<td colspan=6><center><h6>No startup programs found.</h6></center></td>
					</tr>
			<?php }?>
	   </tbody>
	</table>
</div>
<script>
	$(document).ready(function() {
		$('#<?php echo $_SESSION['userid']; ?>Programs').dataTable( {
			colReorder: true,
			stateSave: true
		} );
		$('#<?php echo $_SESSION['userid']; ?>Startup').dataTable( {
			colReorder: true,
			stateSave: true
		} );
	});
</script>
<script>
    $(".sidebarComputerName").text("<?php echo strtoupper($_SESSION['ComputerHostname']);?>");
</script>
<script>
	<?php if($online=="0"){ ?>
		toastr.remove();
		toastr.error('This computer appears to be offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
</script>