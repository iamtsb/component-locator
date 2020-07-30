<?php

	/*
	 * config.inc.php
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
	 * Project Information 
	 */
	$project_name = "Project Name";
	$project_url = "";
	$project_engineer = "Your name";
	
	/*
	 * Custom prefix label for the projectname & engineer field
	 *
	 * If not defined:
	 * project_custom_prefix = Project
	 * project_engineer_custom_prefix = PCB by
	 *
	 */
	$project_custom_prefix = "Project";
	$project_engineer_custom_prefix = "Design/PCB by";
	
	/*
	 * Image DPI value ( default = 200 )
	 *
	 * $image_dpi = dpi of the pcb top / bottom images
	 *
	 */
	 $image_dpi = 400;
	
	/*
	 * Correction values for x & y-axis ( in mm )
	 *
	 * $x_correction_top = x-axis correction value for top layer, measured from top-left of the pcb
	 * $y_correction_top = y-axis correction value for top layer, measured from top-left of the pcb
	 *
	 * $x_correction_bottom = x-axis correction value for bottom layer, measured from top-right of the pcb
	 * $y_correction_bottom = y-axis correction value for bottom layer, measured from top-right of the pcb
	 *
	 * the x & y correction values for top & bottom layer must be flipped.
	 * x = -31.15 => becomes: 31.15
	 * y = 84.46  => becomes: -84.46
	 *
	 */
	 $x_correction_top = -0.10; 
	 $y_correction_top = -0.05;	

	 $x_correction_bottom = -49.05;
	 $y_correction_bottom = 0;
	
	/*
	 * Default zoom value
	 *
	 * $default_zoom = init zoom setting
	 *
	 * 0 = Fit to screen
	 * 1 = 100%
	 * 2 = 50%
	 * 3 = 25%
	 *
	 */
	 $default_zoom = 0;
	
	/* 
	 * Component Layout File
	 *
	 * 0 = SprintLayout
	 * 1 = Kicad
	 *
	*/
	$component_file_type = 1;

?>