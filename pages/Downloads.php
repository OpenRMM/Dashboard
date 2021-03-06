<?php
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page']);


?>
<div style="margin-top:0px;padding:15px;margin-bottom:30px;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;border-radius:6px;" class="card card-sm">
	<h5 style="color:#0c5460">Downloads
		<a href="javascript:void(0)" title="Refresh" onclick="loadSection('Versions');" class="btn btn-sm" style="float:right;margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
			<i class="fas fa-sync"></i>
		</a>
		<a href="javascript:void(0)"  data-bs-toggle="modal" data-bs-target="#agentUpload" style="margin:5px;float:right;background:#0c5460;color:#d1ecf1;" class="btn btn-sm">
			<i class="fas fa-upload"></i> Upload Agent
		</a>		
	</h5>
	<p>Downloading older agent versions may expose the asset to bugs or have less features available. However, older versions may help with compatibility.
		<br><br>
		<span style="color:red">Note: This will download a generic agent. It will not be assigned to a <?php echo strtolower($msp); ?>.</span>
	</p>
	<hr>
	<h6 style="font-size:16px;">
		Latest Version:
		<b>
			<a href="../../download/">
				<?php echo textOnNull($siteSettings['general']['agent_latest_version'], "Unknown");?>
			</a>
		</b>
	</h6>
</div>
<div class="card table-card" id="printTable" >
	<div class="card-header">
		<h5>All Agent Versions</h5>
		<div class="card-header-right">
			<ul class="list-unstyled card-option">
				<li><i class="feather icon-maximize full-card"></i></li>
				<li><i class="feather icon-minus minimize-card"></i></li>
				<li><i class="feather icon-trash-2 close-card"></i></li>
			</ul>
		</div>
	</div>
	<div style="padding:10px;overflow-x:auto">
		<table id="<?php echo $_SESSION['userid']; ?>Downloads" style="line-height:10px;overflow:hidden;font-size:14px;margin-top:8px;font-family:Arial;" class="table-striped table table-hover table-borderless">
			<thead>
				<tr style="border-bottom:2px solid #d3d3d3;">
				<th scope="col">Filename</th>
				<th scope="col">Date Published</th>
				<th scope="col">Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$key = 0;
				if ($handle = opendir('../downloads/')) {
					while (false !== ($entry = readdir($handle))) {
						if ($entry != "." && $entry != "..") {
						$key++;
						?>
						<tr>
						<td>
							<?php echo $entry; ?>
							<?php echo (strpos($entry, $siteSettings['general']['agent_latest_version'])!==false ? " <b>- Latest</b>" : "");?>
						</td>
						<td><?php echo date("m/d/Y", filemtime("../downloads/".$entry)); ?></td>
						<td>
							<a class="btn btn-sm btn-primary" title="Download" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" href="../../?file=<?php echo urlencode($entry); ?>">
								<i class='fas fa-download'></i>
							</a>&nbsp;
							<?php if($_SESSION['userid']!="") { ?>
								<a title="Delete" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-danger btn-sm" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#versionModal" onclick="delVersion('<?php echo $entry; ?>')">
									<i class="fas fa-trash">&nbsp;</i>
								</a>
							<?php } ?>
						</td>
						</tr>
					<?php
						}
					}
					closedir($handle);
				}
				if($key == 0){ ?>
					<tr>
						<td colspan=4><center><h6>No files found.</h6></center></td>
					</tr>
				<?php }?>
			</tbody>
		</table>
	</div>
</div>
<script>
	function delVersion(Version){
		$("#delVersion_ID").val(Version);
	}
</script>
<script>
	$(document).ready(function() {
		$('#<?php echo $_SESSION['userid']; ?>Downloads').dataTable( {
			colReorder: true,
			"order": [0,'desc'],
			stateSave: true
		} );
	});
</script>
