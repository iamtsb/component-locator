# **Component Locator Chucky** 

version 1.41 - by M.F. Wieland (TSB)



### What can you do with this ?

With the locator you can make a visual representation of a PCB (TOP  & BOTTOM) and highlight selected components.

This make it easy to build a DIY project.



### Files needed

- pcbdata/[project_folder]/BottomView.png
  *Bottom picture of the PCB*
- pcbdata/[project_folder]/TopView.png
  *Top picture of the PCB*
- pcbdata/[project_folder]/components.txt
  *component list*
- pcbdata/[project_folder]/config.inc.php
  *config file, See: pcbdata/config.inc.php*

<u>Optional files</u>

- pcbdata/[project_folder]/BottomView_alt.png
  *Alternative bottom picture of the PCB with traces for example*
- pcbdata/[project_folder]/TopView_alt.png
  *Alternative top picture of the PCB with traces for example*



### Supported applications / Components.txt formats

- SprintLayout
- KiCad



Export component lists from the supported application to create the components.txt file.

See manual of application how to make this export.

Kicad export: *PCBLayout editor, File, Fabrication Outputs, Footprint Position (.pos) File*



##### SprintLayout

Values/Fields:

* Name,Value,Layer,Comment,Pos-X,Pos-Y,Rot,Package,No

Every value is separated with a tab from each other.



##### KiCad

Values/Fields:

* Ref, Val, Package, PosX, PosY, Rot, Side

Every value is separated with a tab from each other.



##### 


### config.inc.php settings
See: pcbdata/config.inc.php



### Demo files

I added some demo project files. 

*The demo files are not for production use, so the chucky boards are just demo files and can deviate from the current project files from chucky.*



- Chucky A3660 - board (SprintLayout) (demo with 2 images for each layer)
- Chucky R1200 - board (SprintLayout)
- Demo file by me (Kicad)



### Live pages

- http://locator.reamiga.info/ (production files)
- https://tsb.space/tools/clc/ (demo site)

