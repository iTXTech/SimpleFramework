# SimpleFramework

[![License](https://img.shields.io/github/license/iTXTech/SimpleFramework.svg)](https://github.com/iTXTech/SimpleFramework/blob/master/LICENSE)
[![Stable](https://img.shields.io/badge/stable-2.2.1(8)-brightgreen.svg)](https://github.com/iTXTech/SimpleFramework/releases/tag/v2.2.1)
[![Developing](https://img.shields.io/badge/dev-3.0.0(9)-blue.svg)]()

__Powerful, lightweight and extensible php command line framework.__

## Introduction

Features:

* Module Dependency Resolver (Compatible with `composer`)
* Built-in Console and Commands
* AsyncTask Scheduler and Multi-threading
* Network and Configuration Utilities
* All APIs can be used in a single script - *See examples*
* Module HotPatch (Requires `runkit7`)
* Command Line Options
* OS Integrations (Requires `PHP 7.4` and `FFI`)
* Ultra lightweight PHAR (< 100KB)

Contributions are welcome.

### See also

* [Development Roadmap](https://github.com/iTXTech/SimpleFramework/issues/3)
* [SimpleFramework Wiki](https://github.com/iTXTech/SimpleFramework/wiki)

## Requirements

[Build PHP for SimpleFramework](https://github.com/iTXTech/php-build-scripts)

* [PHP](https://www.php.net/) >= 7.2
* [pthreads](https://github.com/krakjoe/pthreads) - *Multi-threading library for PHP*
* [runkit7](https://github.com/runkit7/runkit7) - *Module HotPatch*

Fully support: PHP 7.2, PHP 7.3

Partially support: PHP 7.4, PHP 8.0, PeachPie

**Note that now swoole will break pthreads, do not use them together.**

### Integrated support

* [php-yaml](https://github.com/php/pecl-file_formats-yaml) - `Config`

## Get SimpleFramework

* __[Releases](https://github.com/iTXTech/SimpleFramework/releases)__ - Stable release, PHAR format

or

* `$ git clone https://github.com/iTXTech/SimpleFramework.git` - Get the latest development environment for **FUN**

## License

    Copyright (C) 2016-2021 iTX Technologies

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
