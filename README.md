# **Component Locator Chucky** 

version 1.3 - by M.F. Wieland (TSB)



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



### Supported applications / Components.txt formats

- SprintLayout
- KiCad



Export component lists from the supported application to create the components.txt file.

See manual of application how to make this export.



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


