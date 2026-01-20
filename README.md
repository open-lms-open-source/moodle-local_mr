# Open LMS Framework Local plugin

The Open LMS Framework offers enhanced functionality for Moodle plugin development.
It is required by several of the plugins that have been open sourced by BlackBoard and Open LMS.

This plugin was contributed by the Open LMS Product Development team. Open LMS is an education technology company
dedicated to bringing excellent online teaching to institutions across the globe. We serve colleges and universities,
schools and organizations by supporting the software that educators use to manage and deliver instructional content to
learners in virtual classrooms.

## Installation

You only need to unzip the content of the plugin into _/wwwroot/local_.

### Setup Guide

To get additional features, follow these steps:

* Display framework documentation

  1. Add the following code to your config.php: define('MR_DOCS', 1);
  2. This will display the following link: Site Administration > MR Framework > Docs

For more information about the configuration and usage, please see https://docs.moodle.org/dev/Blackboard_Open_LMS_Framework#Quick_Start_Guide

## Flags

### The  `local_mr_lock_default_backend`  flag.

### The  `local_mr_redis_server`  flag.

### The  `reportviewsql`  flag.

### The  `local_mr_lock_default_timetolive`  flag.

## License

Copyright (c) 2021 Open LMS (https://www.openlms.net)

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see [http://www.gnu.org/licenses/](http://www.gnu.org/licenses/).