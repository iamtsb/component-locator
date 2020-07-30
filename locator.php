<?php

	/*
	 * locator.php
	 * 
	 * PCB component locator code is written & released by M.F. Wieland (TSB)
	 * This project has initially been created for John "Chucky" Hertell
	 * 
	 * Version 1.3
	 * Release date: 30-7-2020
	 * 
	 * This work is licensed under the terms of the MIT license.  
	 * For a copy, see <https://opensource.org/licenses/MIT>.
	 *
	 * See LICENSE file for more license information
	 */
	
	/*
	 * disable cache..
	 */
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	
	/*
	 * Load version info
	 */
	include('version.inc.php');

	/*
	 * Check if all files exists
	 */
	$GLOBALS['config']['rootdir_pcbdata'] = 'pcbdata/'.$_GET['project'];

	if( !file_exists($GLOBALS['config']['rootdir_pcbdata'].'/config.inc.php') ) {
		
		header('location: index.php');
	}
	
	$file_errors = 0;
	$files[] = "images/boingball-tsb-commodore.gif";
	$files[] = "images/guru.gif";
	$files[] = $GLOBALS['config']['rootdir_pcbdata']."/config.inc.php";
	$files[] = $GLOBALS['config']['rootdir_pcbdata']."/BottomView.png";
	$files[] = $GLOBALS['config']['rootdir_pcbdata']."/TopView.png";
	$files[] = $GLOBALS['config']['rootdir_pcbdata']."/components.txt";
	
	$errors = "<u><b>Missing files:</b></u><br>";
	foreach( $files as $filename ) {
	
		if(!file_exists($filename) || !is_readable($filename)) {
			
			$file_errors++;
			$errors.= "- $filename<br>";
			
		}
	}
	
	if( $file_errors>0 ) {
		
		echo $errors;
		exit;
	}
	
	/*
	 * Everything is fine.. go on..
	 */
	 
	function read_config() {
		
		include($GLOBALS['config']['rootdir_pcbdata'].'/config.inc.php');

		$GLOBALS['config']['project_custom_prefix'] = ($project_custom_prefix=='') ? "Project" : $project_custom_prefix;
		$GLOBALS['config']['project_name'] = ($project_name=='') ? "" : $GLOBALS['config']['project_custom_prefix'] . ": $project_name | ";

		$GLOBALS['config']['project_engineer_custom_prefix'] = ($project_engineer_custom_prefix=='') ? "PCB by" : $project_engineer_custom_prefix;
		$GLOBALS['config']['project_engineer'] = ($project_engineer=='') ? "" : $GLOBALS['config']['project_engineer_custom_prefix'] . ": $project_engineer | ";

		$GLOBALS['config']['project_url'] = ($project_name=='') ? "" : $project_url;

		$GLOBALS['config']['image_dpi'] = ($image_dpi=='') ? 200 : $image_dpi;
		$GLOBALS['config']['component_file_type'] = ($component_file_type=='') ? 0 : $component_file_type;

		$GLOBALS['config']['x_correction_top'] = ($x_correction_top=='') ? 0 : $x_correction_top;
		$GLOBALS['config']['y_correction_top'] = ($y_correction_top=='') ? 0 : $y_correction_top;
		$GLOBALS['config']['x_correction_bottom'] = ($x_correction_bottom=='') ? 0 : $x_correction_bottom;
		$GLOBALS['config']['y_correction_bottom'] = ($y_correction_bottom=='') ? 0 : $y_correction_bottom;

		$GLOBALS['config']['default_zoom'] = ($default_zoom=='') ? 0 : $default_zoom;
	}
	read_config();
	
	function debugger( $input ) {
		
		echo "\n<!--\n";
		echo print_r($input, true);
		echo "\n-->\n";
		
	}
	
	function component_parser($filename,$delimiter=';') {
		
		if(!file_exists($filename) || !is_readable($filename)) {
			
			return FALSE;
		}

		$handle = fopen($filename, "r");
		$contents = fread($handle, filesize($filename));
		fclose($handle);
		
		switch( $GLOBALS['config']['component_file_type'] ) {
			
			/*
			 * SprintLayout component.txt format
			 */ 
			case 0 : 
			
				// change tabs to ;
				$content = str_replace("\t",";",$contents);
				if( preg_match_all("/^\*.*\bname\b.*\bvalue\b.*\blayer\b.*$/im",$content,$m)>0 ) {
					
					// remove all lines starting with *
					$content = preg_replace("/^\*.*$(?:\r\n|\n)?/im","",$content);
					// add header field
					$content = str_replace(array('*',' '),'',$m[0][0])."\n".$content;
					
					// parse data
					$Data = str_getcsv($content, "\n"); //parse the rows 
					foreach($Data as &$row) {
						
						$rowData = str_getcsv($row, ";");
						
						if(!$header)
							$header = str_getcsv($row, ";"); 
						else
						$data[] = array_combine($header, $rowData);
					}
					
					return $data;
				}

				break;

			/*
			 * Kicad component.txt (pos) format
			 */ 
			case 1 : 
				
				
				if( preg_match_all("/^.*\bref\b.*\bval\b.*$/im",$contents,$m)>0 ) {
				
					// filter contents
					$content = str_replace('# ','',$m[0][0])."\n".$contents;
					$content = preg_replace("/[[:blank:]]+/",";",$content);
					$content = preg_replace("/^#.*$(?:\r\n|\n)?/im","",$content);
					
					// parse data
					$Data = str_getcsv($content, "\n"); //parse the rows 
					foreach($Data as &$row) {
						
						// change labels to right labels..
						$row = str_ireplace( array('Ref','Val','PosX','PosY','Side','bottom','top'), array('Name','Value','Pos-X','Pos-Y','Layer','Bottom','Top'),$row);
						$rowData = str_getcsv($row, ";");
						
						if(!$header) {
							
							$header = str_getcsv($row, ";"); 
						}
						else {
							
							$data[] = array_combine($header, $rowData);
						}
					}
					
					return $data;
				}
			
				break;
		}
	}

	/* Name;Value;Layer;Comment;Pos-X;Pos-Y;Rot;Package;No	*/
	function get_layer_data( $layer='Top' ) {
		
		$csv = component_parser($GLOBALS['config']['rootdir_pcbdata'].'/components.txt');
		
		$tpl = '<option value=\'{"x":%s,"y":%s,"rot":"%s","value":"%s","name":"%s"}\'>%s</option>\n';
		foreach( $csv as $data ) {
			
			if( $data['Name']!='' && $data['Pos-X']!='' && $data['Pos-Y']!='' && $data['Pos-X']!='---' && $data['Pos-Y']!='---' && $data['Layer']==$layer && $data['Value']!='' ) {
				
				$valuesComponentValues.= sprintf($tpl, $data['Pos-X'], $data['Pos-Y'], str_replace('R','',$data['Rot']), $data['Value'], $data['Name'], $data['Name']);
			}
		}
		
		/*
		 * Create component data array
		 */ 
		$tpl = '<option value=\'{"x":%s,"y":%s,"rot":"%s","value":"%s","name":"%s"}\'>%s</option>\n';
		foreach( $csv as $data ) {
			
			if( $data['Name']!='' && $data['Pos-X']!='' && $data['Pos-Y']!='' && $data['Pos-X']!='---' && $data['Pos-Y']!='---' && $data['Layer']==$layer && $data['Value']!='' ) {
					
				// filter component code letter(s)
				$pattern = '/^[A-Za-z]+/';
				preg_match($pattern, $data['Name'], $matches, PREG_OFFSET_CAPTURE);
				$key_val = $matches[0][0];
				
				if( $key_val=='E' ) {
					
					$tmp_key = $data['Name'][strlen($data['Name'])-1];
					switch( strtoupper($tmp_key) ) {
						
						case "R" : $key_val = $tmp_key;break;
						case "C" : $key_val = $tmp_key;break;
					}
					
					if( $data['Value']=='FB' ) $key_val = 'FB';
				}
				
				$dataCollected[$key_val][$data['Value']][] = array( $data['Value'], $data['Pos-X'], $data['Pos-Y'], str_replace('R','',$data['Rot']), $data['Name']);
			}
		}
		
		/*
		 * Create component category array
		 */ 
		$tpl = '<option value=\'%s\'>%s</option>\n';
		$others=0;
		foreach( $dataCollected as $key=>$data ) {
			
				$value = "";
				$skip = false;
				switch( $key ) {
					
					case 'C'  : $value = 'Capacitors'; break;
					case 'CN'  : $value = 'Connectors'; break;
					case 'J'  : $value = 'Headers/Jumpers'; break;
					case 'D'  : $value = 'Diodes'; break;
					case 'E'  : $value = 'E-code'; break;
					case 'FB'  : $value = 'Ferrite Bead'; break;
					case 'Q'  : $value = 'Transistors'; break;
					case 'R'  : $value = 'Resistors'; break;
					case 'U'  : $value = 'IC\'s'; break;
					case 'X'  : $value = 'Oscillators/Crystals'; break;
					case 'FB' : $value = 'Ferrite beads'; break;
					case 'XC' : $key = 'C'; $skip = true; break;
					case 'XR' : $key = 'R'; $skip = true; break;
					case 'XU' : $key = 'U'; $skip = true; break;
					case 'JP'  : $key = 'J';$skip=true; break;
					case 'ER'  : $key = 'R'; $skip = true; break;
					case 'EC'  : $key = 'C'; $skip = true; break;
					default   : $value = 'Various'; $key='Various'; if( $others>0 ) { $skip=true; } $others++; 
					
				}
				if( $skip==false ) $component_types.= sprintf($tpl, $key, $value);
				
				foreach( $data as $key2=>$data_array ) {
					
					foreach( $data_array as $d2 ) {
						
						$values[$key][$key2][] = '{"value":"'.$d2[0].'", "x":'.$d2[1].', "y":'.$d2[2].', "rot":"'.$d2[3].'", "name":"'.$d2[4].'"}';
					}
				}
		}
		
		return array($valuesComponentValues,$component_types,$values );
	}
	
	$data_top = get_layer_data('Top');
	$component_values_top = $data_top[0];
	$component_types_top = $data_top[1];
	$values_js_top =  json_encode( $data_top[2] );

	$data_bottom = get_layer_data('Bottom');
	$component_values_bottom = $data_bottom[0];
	$component_types_bottom = $data_bottom[1];
	$values_js_bottom =  json_encode( $data_bottom[2] );
	
?>

<!-- 
* PCB component locator code is written by TSB ( M.F. Wieland )
* This project is initial been created for John "Chucky" Hertell
*
* (c) 2018
-->
<!DOCTYPE HTML>
<html>
<head>

	<meta charset="utf-8">
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	
	<title>Component Locator Chucky - <?php echo $project_name; ?></title>

	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	
	<style>
		html,body { padding:0; margin:0;background:#000;color:white;width:  100%;height: 100%;font-family: sans-serif; }
		#locatorCanvas { position:absolute;border:1px solid #222;background:#222;left:0px;top:40px;cursor:pointer; }
		#compValues { position:fixed; left:0px;top:0px;font-size:16px;}
		#currentValue {  position:fixed; left:0px;bottom:0px;font-size:16px;color:white;height:40px;line-height:40px;text-align:right; margin-left:20px;font-weight:bold;}
		#credits {  position:fixed; right:0px;bottom:0px;margin-right:20px; }
		#toolbar { position:fixed; top:0px; right:0px; left:0px;height:40px; background-color:#F72229;line-height:40px;padding-left:20px;min-width:980px; }
		#bottombar { position:fixed; bottom:0px; right:0px; left:0px;height:40px; background-color:#304D5E;line-height:40px;padding-left:20px;font-size:12px;min-width:980px; }

		/* prevent text selection */
		body {
			-webkit-touch-callout: none;
			-webkit-user-select: none;
			-khtml-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
			user-select: none;
			min-width:980px;
		}
		#loader { position:fixed; top:0px; bottom:0px; left:0px; right:0px; }
		#loader img { position:fixed; top:50%; left:50%; margin-top:-150px; margin-left: -78px; }
		#guru { position:fixed; top:20px; left:50%; margin-left: -250px; }
	</style>
	
	<script>
	
		// canvas 
		var canvas, ctx;
		
		var x_correction_top = <?php echo $GLOBALS['config']['x_correction_top']; ?>;
		var y_correction_top = <?php echo $GLOBALS['config']['y_correction_top']; ?>;

		var x_correction_bottom = <?php echo $GLOBALS['config']['x_correction_bottom']; ?>;
		var y_correction_bottom = <?php echo $GLOBALS['config']['y_correction_bottom']; ?>;

		var dot_size = 12;
		var dot_color = '#F72229';
		var dot_border_width = 3; 
		
		var font_size = 12;
		var font_color = 'White';
		var font_back_color = 'Black';
		
		var canvas_width;
		var canvas_height;
		var scrollbar_width;
		
		var active_img = new Image();

		var image_dpi = <?php echo $GLOBALS['config']['image_dpi']; ?>;
		var default_zoom = <?php echo $GLOBALS['config']['default_zoom']; ?>;
		var zoom = default_zoom;
		
		var active_layer = 0; //0=top 1=bottom
		var active_function = 0; //0=doPlotAll, 1=doPlot
		
		var blink_active = false;
		var blink_flipflop = false;
		var blink_interval_hnd;
		var blink_interval_time = 800;
		var blink_color = 'Grey';
		
		var values_top = <?php echo "$values_js_top;"; ?>;
		var values_bottom = <?php echo "$values_js_bottom;"; ?>;
		
		var active_component_type = 'C';
		var active_components_dialog_updates = true;
		
		var total_components_selected = 0;
		
		var rootdir_pcbdata = '<?php echo $GLOBALS['config']['rootdir_pcbdata'];?>';
		
		var clicked = false, click_x, click_y;
		$(document).on({
			'mousemove': function(e) {
				clicked && update_scroll_pos(e);
			},
			'mousedown': function(e) {
				clicked = true;
				click_y = e.pageY;
				click_x = e.pageX;
				$('#locatorCanvas').css('cursor', 'move');
			},
			'mouseup': function() {
				clicked = false;
				$('#locatorCanvas').css('cursor', 'pointer');
			}
		});

		var update_scroll_pos = function(e) {
			
			$('#locatorCanvas').css('cursor', 'move');
			$(window).scrollTop($(window).scrollTop() + (click_y - e.pageY));
			$(window).scrollLeft($(window).scrollLeft() + (click_x - e.pageX));
		}		
		
		$(window).resize( function() {
			
			set_zoom(0);
		});
		
		function blink_toggle() {
			
			active_components_dialog_updates = false;
			
			if( !blink_active ) {
				
				blink_active = true;
				$("#blink_toggle_btn").html("Stop blink");
				
				blink_interval_hnd = setInterval( function(){ 
					blink_flipflop = ( blink_flipflop ) ? false : true;
					active_components_dialog_updates = false;
					exec_active_function(); 
				}, blink_interval_time );
			}
			else {
				blink_active = false;
				blink_flipflop = false;
				$("#blink_toggle_btn").html("Blink");
				clearInterval( blink_interval_hnd );
				exec_active_function();
			}
		}
		
		function exec_active_function() {
			
			if(active_function==0) {
				
				plot_multi_components();
			}
			else {
				
				plot_single_component(active_value);
			}
		}
		
		function select_layer( selected_layer ) {
			
			active_components_dialog_updates = true;
			
			active_layer = selected_layer;
			
			if( active_layer==0 ) {
				
				$("#valueComponents").html( $("#component_values_top").html() );
				$("#component_type").html( $("#component_types_top").html() );
			}
			else {
				
				$("#valueComponents").html( $("#component_values_bottom").html() );
				$("#component_type").html( $("#component_types_bottom").html() );
			}
			
			//init
			fill_component_select( $("#component_type").val() );
			plot_multi_components( $("#component_values").val() );
		}
		
		function set_zoom( zoom_val ) {
			
			if( zoom_val==0 ) {
				
				if( active_img.width>=window.innerWidth ) {
					
					zoom = 1/((window.innerWidth-(scrollbar_width+2))/active_img.width); 
				}
				else zoom = 1;
			}
			else {
				
				zoom = zoom_val;
			}
			exec_active_function();
		}
		
		function set_dot_color( color_val ) {
			
			dot_color = color_val;
			
			active_components_dialog_updates = false;
			exec_active_function();
		}
		
		function show_active_components( ) {
			
			var obj = $("#highlighted_components_dialogs");
			
			obj.dialog({position:{ my: "top+50px", at: "right", of: $('#toolbar') }});
		}

		function highlight_component(x,y,cName,cVal,valueBox) {
			
			var inch = 1/25.4;
			var dpi = image_dpi;
			
			var x_correction = ( active_layer==0 ) ? x_correction_top : x_correction_bottom;
			var y_correction = ( active_layer==0 ) ? y_correction_top : y_correction_bottom;
			
			x = Math.abs( Math.floor( (((x+x_correction+0.25)*inch)*dpi )/zoom));
			y = Math.abs( Math.floor( (((y+y_correction)*inch)*dpi )/zoom));
			
			var txt = (cName!='') ? 'Current part: ' +cName + ' / '+cVal : 'Current parts ( <span onclick=show_popup()>'+total_components_selected+' )</span> : ' +cVal;
			$("#currentValue").html(txt);
			
			ctx.beginPath();
			ctx.rect(x-Math.floor((dot_size/2)/zoom), y-Math.floor((dot_size/2)/zoom), Math.floor(dot_size/zoom), Math.floor(dot_size/zoom) );
			ctx.fillStyle = ( blink_active && blink_flipflop ) ? blink_color : dot_color;
			ctx.fill();
			ctx.lineWidth = dot_border_width;
			ctx.strokeStyle = ( blink_active && blink_flipflop ) ? blink_color : dot_color;
			ctx.stroke();
			
			if( valueBox ) {
				
				var rectHeight = font_size+5;
				var rectWidth = (font_size*cVal.length)+10;
				var rectYOffset = 35;
				
				var xText = x;
				var yText = y;
				
				ctx.beginPath();
				ctx.rect(xText-rectWidth, yText-rectYOffset, rectWidth, rectHeight);
				ctx.fillStyle = font_back_color;
				ctx.fill();
				ctx.stroke();

				ctx.fillStyle = font_color;
				ctx.font = "bold "+font_size+"px Arial";
				ctx.fillText(cVal,x-rectWidth+15,y-rectYOffset+(rectHeight/2)+4);
			}
		}
		
		function plot_single_component( value ) {
			
			active_function = 1;
			active_value = value;
			
			active_img.src = ( active_layer==0 ) ? rootdir_pcbdata+'/TopView.png' : rootdir_pcbdata+'/BottomView.png';
			active_img.onload = function(){
				
				canvas_width = Math.floor(active_img.width/zoom);
				canvas_height = Math.floor(active_img.height/zoom);
				
				ctx.canvas.width = canvas_width;
				ctx.canvas.height = canvas_height;
				
				//add bottom bar height to canvas height
				ctx.canvas.height+= $("#bottombar").height();

				ctx.drawImage(active_img, 0, 0,active_img.width/zoom,active_img.height/zoom);
				var obj = jQuery.parseJSON( value );
				
				highlight_component(obj.x,obj.y,obj.name,obj.value,true);
				
				total_components_selected = 0;
			}
		}
		
		function plot_multi_components() {
			
			active_function = 0;
			
			active_img.src = ( active_layer==0 ) ? rootdir_pcbdata+'/TopView.png' : rootdir_pcbdata+'/BottomView.png';
			active_img.onload = function(){
				
				canvas_width = Math.floor(active_img.width/zoom);
				canvas_height = Math.floor(active_img.height/zoom);
				
				ctx.canvas.width = canvas_width;
				ctx.canvas.height = canvas_height;
				
				//add bottom bar height to canvas height
				ctx.canvas.height+= $("#bottombar").height();
				
				ctx.drawImage(active_img, 0, 0,canvas_width,canvas_height);
				
				var component_value = $("#component_values").val();
				var values = ( active_layer==0 ) ? values_top : values_bottom;
				
				total_components_selected = 0;
				var active_components = 'Check which components are ready.<br />';
				$.each(values, function (index, value) {
					
					if( index==active_component_type ) {
					
						$.each(value, function (index, value) {
							
							if(index==component_value) {
								
								$.each(value, function (index, value) {
									
									var obj = jQuery.parseJSON( value );
									total_components_selected++;
									highlight_component(obj.x,obj.y,'',obj.value,false);
									active_components+= '<input type="checkbox"> '+obj.name+' ('+obj.value+')'+'<br />';
								});
							}
						});
					}
				});
				if( active_components_dialog_updates )	$( "#highlighted_components_dialogs" ).html(active_components);
			}
		}		
		
		function prepare_plot_multi_components() {
			
			active_components_dialog_updates = true;
			plot_multi_components();
		}
		
		function fill_component_select( component_type ) {
			
			active_component_type = component_type;
			
			$('#component_values').html('');
			var values = ( active_layer==0 ) ? values_top : values_bottom;
			
			$.each(values, function (index, value) {
					
				if( index==component_type ) {
					
					$.each(value, function (index, value) {
							
						$('#component_values').append('<option value="'+index+'">'+index+'</option>');
					});
				}
			});
			
			$("#component_values").append($("#component_values option").remove().sort(function(a, b) {
				var at = $(a).text(), bt = $(b).text();
				return (at > bt)?1:((at < bt)?-1:0);
			}));
			
			$("#component_values")[0].selectedIndex = 0;
			$("#component_values").focus();

			plot_multi_components( $("#component_values").val() );
		}
		
		$(window).ready( function() {
			
			if (navigator.appName == 'Microsoft Internet Explorer' ||  !!(navigator.userAgent.match(/Trident/) || navigator.userAgent.match(/rv:11/)) || (typeof $.browser !== "undefined" && $.browser.msie == 1)) {
				
				$("#guru").show();
				return false;
			}
			
			//calc scrollbar width
			var w1 = $("body").width();
			$("body").css("overflowY","scroll");
			scrollbar_width = w1 - $("body").width();
			$("body").css("overflowY","auto");
			
			canvas = document.getElementById("locatorCanvas");
			if( canvas && canvas.getContext ) {
				
				ctx = canvas.getContext("2d");
			}	
			$("#loader").fadeIn();
			
			//preload images..
			$(active_img).attr({ src: rootdir_pcbdata+'/TopView.png' }).load(function() { });
			
			
			var tmp_img = new Image();
			$(tmp_img).attr({ src: rootdir_pcbdata+'/BottomView.png' }).load(function() {
				
				$("#locator").fadeIn( function() {
					set_zoom( default_zoom );
				});
			
				// select layer
				select_layer(0);
			});
		});

	</script>
</head>

<body>
<img id=guru src="images/guru.gif" style="display:none" />

<div id=loader style="display:none">
</div>
<div id=locator style="display:none">
	<canvas id="locatorCanvas" width="200" height="100"></canvas>
	<div id=toolbar>
		<button onclick="window.location='index.php'">project selector</button>
		<span id=component_values_top style="display:none"><?php echo $component_values_top; ?></span>
		<span id=component_types_top style="display:none"><?php echo $component_types_top; ?></span>

		<span id=component_values_bottom style="display:none"><?php echo $component_values_bottom; ?></span>
		<span id=component_types_bottom style="display:none"><?php echo $component_types_bottom; ?></span>

		Zoom:
		<select id=zoom_val onchange=set_zoom(this.value)>
			<option value='0'>Fit to window</option>
			<option value='1'>100%</option>
			<option value='2'>50%</option>
			<option value='3'>25%</option>
		</select>
		PhatPix:
		<select onchange=set_dot_color(this.value)>
			<option value='#F72229'>Red</option>
			<option value='#2070FF'>Blue</option>
			<option value='#40AFFF'>Blue (light)</option>
			<option value='#0CE81B'>Green</option>
			<option value='#FFF726'>Yellow</option>
			<option value='#B8E817'>Lime</option>
			<option value='#C938FF'>Purple</option>
			<option value='White'>White</option>
		</select>
		Layer:
		<select onchange=select_layer(this.value)>
			<option value=0>Top</option>
			<option value=1>Bottom</option>
		</select>
		
		Single component: <select id=valueComponents onchange=plot_single_component(this.value)></select>	

		Multi: 
		<select id=component_type onchange=fill_component_select(this.value)></select>	

		<select id=component_values onchange=prepare_plot_multi_components()></select>	
		<button id=blink_toggle_btn onclick=blink_toggle()>blink</button>	
	</div>
	<div id=bottombar>
		<span id=credits>
			<?php 
				echo $GLOBALS['config']['project_name'];
				echo $GLOBALS['config']['project_engineer'];
			?> 
			Component Locator Chucky <?php echo $version;?> - by TSB ( M.F. Wieland ) - (c) 2018-2020
		</span>
		<span id=currentValue>##</span>
	</div>
	
	<div id="highlighted_components_dialogs" title="Highlighted components" style="display:none;">
	</div>
</div>
</body>
</html>
<?php
	echo "<!-- page loaded on: ".time()."-->";
?>