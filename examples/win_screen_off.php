<?php

/*
 *
 * SimpleFramework
 *
 * Copyright (C) 2016-2021 iTX Technologies
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/*
SF_SCRIPT_REQUIREMENTS_STARTS
{"php":7.4,"exts":{"ffi":""},"os":"win","info":""}
SF_SCRIPT_REQUIREMENTS_ENDS
 */

use iTXTech\SimpleFramework\Util\Platform\WindowsPlatform;

require_once "../autoload.php";

WindowsPlatform::postMessage(-1, 0x0112, 0xF170, 2);
