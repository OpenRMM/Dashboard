<?php
	include("../Includes/db.php");
	$computerID = (int)$_GET['ID'];
	$search = $_GET['search'];
	$showDate = $_GET['Date'];

	//if(!$exists){ exit("<br><center><h4>No Computer Selected</h4><p>To Select A Computer, Please Visit The <a class='text-dark' href='index.php'><u>Dashboard</u></a></p></center><hr>"); }

	$json = getComputerData($computerID , array("DefaultPrograms"), $showDate);

	$programs = $json['DefaultPrograms'];
	$error = $json['DefaultPrograms_error'];

?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 1'];?>">
			Default Programs (<?php echo count($programs);?>)
		</h4>
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;">
				Last Update: <?php echo ago($json['DefaultPrograms_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS g:i A", strtotime($json['DefaultPrograms_lastUpdate']));?>
			</span>
		<?php }?>
	</div>
	<div style="text-align:right;" class="col-md-2">
		<a href="#" title="Refresh" onclick="loadSection('DefaultPrograms');" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;">
			<i class="fas fa-sync"></i>
		</a>
		<a href="#" title="Select Date" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
			<i class="far fa-calendar-alt"></i>
		</a>
	</div>
</div>
<div style="padding:10px;background:#fff;border-radius:6px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;">
	<table id="dataTable" style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover  table-borderless">
	  <thead>
		<tr style="border-bottom:2px solid #d3d3d3;">
		  <th scope="col">#</th>
		  <th scope="col">Program</th>
		  <th scope="col">File Extension</th>
		  <th scope="col">Clear Default  (beta)</th>
		</tr>
	  </thead>
	  <tbody>
		<?php
			$count = 0;

			//Sort The array by Name ASC
			usort($programs, function($a, $b) {
				return $a['Program'] <=> $b['Program'];
			});

			foreach($programs as $key=>$program){

				if($search!=""){
					if(stripos($program['Ext'], $search) !== false){ }else{ continue; }
				}
				$count++;
		?>
			<tr>
			  <th scope="row"><?php echo $count;?></th>
			  <td><?php echo textOnNull($program["Program"],"N/A");?></td>
			  <td><?php echo textOnNull($program["Ext"],"N/A");?></td>
			  <td>
				<button onclick='sendCommand("cmd", "assoc <?php echo $program['Ext']; ?>=", "Clear default for <?php echo $program['Ext']; ?> filetype");' class="btn btn-danger btn-sm" style="margin-top:-2px;" title="May Require A Reboot">
					<i class="fas fa-eraser"></i>
				</button>
			  </td>
			</tr>
		<?php }
			if($count == 0){ ?>
				<tr>
					<td colspan=4><center><h5>No Default Programs Found.</h5></center></td>
				</tr>
		<?php }?>
	   </tbody>
	</table>
</div>
<script>
	$('#searchInputDefaultPrograms').keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){
			search($('#searchInputDefaultPrograms').val(),'DefaultPrograms','<?php echo $computerID; ?>');
		}
	});
</script>
	<script>
		$(document).ready(function() {
			  $('#dataTable').DataTable();
		});
	</script>
