<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page'],$computerID);

$json = getComputerData($computerID, array("services"));
$query = "SELECT online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];
$date = strtotime($json['general_lastUpdate']);
if($date < strtotime('-1 days')) {
	$online="0";
}
$services = $json['services']['Response'];
$error = $json['services_error'];
?>
<div style="padding:20px;margin-bottom:-1px;" class="card">
	<div class="row" style="padding:15px;">
		<div class="col-md-9">
			<h5 style="color:#0c5460">
				Services (<?php echo count($services);?>)		
			</h5>
			<span style="font-size:12px;color:#666;">
				Last Update: <?php echo ago($json['services_lastUpdate']);?>
			</span>
		</div>
		<div style="text-align:right;" class="col-md-3">
			<div class="btn-group">
				<button style="background:#0c5460;color:#d1ecf1" onclick="loadSection('Asset_Services');" type="button" class="btn btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
				<button style="background:#0c5460;color:#d1ecf1" type="button" class="btn dropdown-toggle-split btn-sm" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<i class="fas fa-sort-down"></i>
				</button>
				<div class="dropdown-menu">
					<a onclick="force='true'; loadSection('Asset_Services','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>
				</div>
			</div>
			<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="$('#olderData_content').html(older_data_modal);olderData('<?php echo $computerID; ?>','Services','null');">
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
	<table id="<?php echo $_SESSION['userid']; ?>Services" style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table-striped table table-hover table-borderless">
	  <thead>
		<tr style="border-bottom:2px solid #d3d3d3;">
		  <th scope="col">#</th>
		  <th scope="col">Name</th>
		  <th scope="col">Display Name</th>
		  <th scope="col">Description</th>
		  <th scope="col">Actions</th>
		</tr>
	  </thead>
	  <tbody>
		<?php
			foreach($services as $key=>$service){
				$state = $service['State'];
				if($state=="Running"){$color=$siteSettings['theme']['Color 4'];}
				if($state=="Stopped"){$color="maroon";}

				$count++;
		?>
			<tr>
			  <th scope="row"><?php echo $count;?></th>
			  <td><?php echo textOnNull($service['Caption'], "[No Name]");?></td>
			  <td><?php echo textOnNull(substr($service['DisplayName'],0,35), "Not Set");?></td>
			  <td><?php echo textOnNull(strlen($service['Description']) > 70 ? substr($service['Description'],0,70)."..." : $service['Description'], "Not Set");?></td>
			  <td>
				  <?php if($state=="Stopped"){ ?>
					<button title="Start Sevice" class="btn btn-sm btn-success" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" onclick='sendCommand("net start <?php echo $name[1]; ?>", "Kill <?php echo $proc['Name']; ?> service");'>
						<i style="font-size:12px;" class="fas fa-play"></i>
					</button>
				  <?php }elseif($state="Running"){ ?>
					<button title="Stop Service" class="btn btn-sm btn-danger"style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" onclick='sendCommand("net stop <?php echo $name[1]; ?> /y", "Kill <?php echo $proc['Name']; ?> service");'>
						<i style="font-size:15px;" class="fas fa-times"></i> 
					</button>
				  <?php } ?>
			  </td>
		<?php }
			if($count == 0){ ?>
				<tr>
					<td colspan=5>
						<center><h6>No services found.</h6></center>
					</td>
				</tr>
		<?php }?>
	   </tbody>
	</table>
</div>
<script>
	$('#searchInputServices').keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){
			search($('#searchInputServices').val(),'Services','<?php echo $computerID; ?>');
		}
	});
</script>
<script>
	$(document).ready(function() {
		$('#<?php echo $_SESSION['userid']; ?>Services').dataTable( {
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