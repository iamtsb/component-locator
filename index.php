<?php

	/*
	 * Component Locator Chucky (PCB component locator)
	 *
	 * Written & released by M.F. Wieland (TSB)
	 *
	 * Version 1.41
	 * Release date: 11-12-2020
	 *
	 * This project has initially been created for John "Chucky" Hertell
	 *  
	 * Licensed under the MIT License. See LICENSE file in the project root for full license information. 
	 *
	 * This file is part of the Component Locator Chucky
	 */

	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	
	include('version.inc.php');

?>

<!DOCTYPE HTML>
<html>
<head>

	<meta charset="utf-8">
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	
	<title>Component Locator Chucky - select project</title>

	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	
	<style>
		html,body { padding:0; margin:0;background:#000;color:white;width:  100%;height: 100%;font-family: sans-serif; }

		#loader { position:fixed; top:0px; bottom:0px; left:0px; right:0px; }
		#loader img { position:fixed; top:50%; left:50%; margin-top:-150px; margin-left: -78px; }
		#guru { position:fixed; top:20px; left:50%; margin-left: -250px; }
		#bottombar { position:fixed; bottom:0px; right:0px; left:0px;height:40px; background-color:#304D5E;line-height:40px;padding-left:20px;font-size:10px;min-width:980px; }
		#credits {  position:fixed; right:0px;bottom:0px;margin-right:20px; }

		#background { 
			
			position:fixed;
			top:0px;
			bottom:0px;
			left:0px;
			right:0px;
			
			opacity:0.6; 
			
			background: url() no-repeat center center fixed; 
			-webkit-background-size: cover;
			-moz-background-size: cover;
			-o-background-size: cover;
			background-size: cover;		
		}

		  
	</style>
	
	<script>

		var options;
	
		function open_project( ) {
			
			window.location = 'locator.php?project='+options[0];
		}
		
		function update_stuff() {
			
			
			options = $("#project").val().split(";");
		
			$("#background").css('backgroundImage','url(pcbdata/'+options[0]+'/TopView.png)');
			if( options[1]!='' ) 
				$("#projecpage_btn").show();
			else 
				$("#projecpage_btn").hide();
			
		}
		
		function open_project_page(){
			
			window.open(options[1]);
		}
		
		$(window).ready( function() {
			
			if (navigator.appName == 'Microsoft Internet Explorer' ||  !!(navigator.userAgent.match(/Trident/) || navigator.userAgent.match(/rv:11/)) || (typeof $.browser !== "undefined" && $.browser.msie == 1)) {
				
				$("#guru").show();
				return false;
			}

			
			update_stuff();
		});

	</script>

<body>
<div id=background></div>
<img id=guru src="images/guru.gif" style="display:none" />

<div id=loader>
	<center>
		<h1>Component Locator Chucky <?php echo $version;?></h1>
		<h3>Select project</h3>
		
	
		<select id=project onchange=update_stuff()>
<?php
	/*
	 *
	 * PCB component locator code is written by TSB ( M.F. Wieland )
	 * This project has been created for John "Chucky" Hertell
	 *
	 * (c) 2018-2020
	*/
	
	
	
	$dirs = array_filter(glob('pcbdata/*'), 'is_dir');
	foreach( $dirs as $dirname ) {
		
		$configfile = $dirname.'/config.inc.php';
		if( file_exists($configfile) ) {
			
			include($configfile);
			$project = str_replace("pcbdata/","",$dirname);
			echo "\t\t<option value=\"$project;$project_url\">$project_name</option>\n";
			
		}
	}
	
?>
		</select>
		
		<button onclick=open_project()>Component locator</button>
		<button id=projecpage_btn onclick=open_project_page() style="display:none">Project page</button><br />
		
		<img src="images/boingball-tsb-commodore.gif" />
	</center>
</div>
<div id=bottombar>
	<span id=credits>
		Component Locator Chucky<?php echo $version;?> - by TSB ( M.F. Wieland ) - (c) 2018-2020
	</span>
</div>

</body>
</html>