/*
 * Component Locator Chucky (PCB component locator)
 *
 * Written & released by M.F. Wieland (TSB)
 *
 * This project has initially been created for John "Chucky" Hertell
 *  
 * Licensed under the MIT License. See LICENSE file in the project root for full license information. 
 *
 * This file is part of the Component Locator Chucky
 */


// canvas 
var canvas, ctx;

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

var zoom = default_zoom;

var active_layer = 0; //0=top 1=bottom
var active_function = 0; //0=doPlotAll, 1=doPlot

var blink_active = false;
var blink_flipflop = false;
var blink_interval_hnd;
var blink_interval_time = 800;
var blink_color = 'Grey';


var active_component_type = 'C';
var active_components_dialog_updates = true;

var total_components_selected = 0;

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