<?php

	/*
	 * Component Locator Chucky (PCB component locator)
	 *
	 * Written & released by M.F. Wieland (TSB)
	 *
	 * Version 1.3
	 * Release date: 30-7-2020
	 *
	 * This project has initially been created for John "Chucky" Hertell
	 *  
	 * Licensed under the MIT License. See LICENSE file in the project root for full license information. 
	 *
	 * This file is part of the Component Locator Chucky
	 */


	/*
	 * Project Information 
	 */
	$project_name = "USBSWITCH";
	$project_url = "";
	$project_engineer = "M.F. Wieland";
	
	/*
	 * Custom prefix label for the projectname & engineer field
	 *
	 * If not defined:
	 * project_custom_prefix = Project
	 * project_engineer_custom_prefix = PCB by
	 */
	$project_custom_prefix = "Project";
	$project_engineer_custom_prefix = "Design/PCB by";
	
	/*
	 * Image DPI value ( default = 200 )
	 */
	 $image_dpi = 400;
	
	/*
	 * Correction values for x & y-axis ( in mm )
	 */
	 $x_correction_top = -0.10; 
	 $y_correction_top = -0.05;	

	 $x_correction_bottom = -49.05;
	 $y_correction_bottom = 0;
	
	/*
	 * Default zoom value
	 *
	 * 0 = Fit to screen
	 * 1 = 100%
	 * 2 = 50%
	 * 3 = 25%
	 */
	 $default_zoom = 0;
	
	/* 
	 * Component Layout File
	 *
	 * 0 = SprintLayout
	 * 1 = Kicad
	*/
	$component_file_type = 1;

?>