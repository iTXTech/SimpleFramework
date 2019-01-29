# SimpleFramework

[![License](https://img.shields.io/github/license/iTXTech/SimpleFramework.svg)](https://github.com/iTXTech/SimpleFramework/blob/master/LICENSE)
[![Stable](https://img.shields.io/badge/stable-2.1.0(6)-brightgreen.svg)](https://github.com/iTXTech/SimpleFramework/releases/tag/v2.1.0)
[![Developing](https://img.shields.io/badge/dev-2.2.0(7)-blue.svg)]()

__A fast, lightweighted and extensible php command line framework.__

## Introduction

SimpleFramework is a php command line framework.

Here are some features:

* Module dependency resolver
* Console & Command support
* AsyncTask and Thread support
* Network & Configuration utilities
* All APIs can be used in a single script - *See examples*

Contributions are welcomed.

### See also

* [Development Roadmap](https://github.com/iTXTech/SimpleFramework/issues/3)

### Command Line

```powershell
PS X:\>.\sf -h
```

```bash
$ ./sf -h
```

## Requirements

* [PHP](https://secure.php.net/) >= 7.2 - *Note that only cli sapi is supported*
* [pthreads](https://github.com/krakjoe/pthreads) - *Highly Recommended*

### Integrated support

* [php-yaml](https://github.com/php/pecl-file_formats-yaml) - `Config`
* [swoole](https://github.com/swoole/swoole-src) - `SwooleLoggerHandler`

## Get SimpleFramework

* __[Releases](https://github.com/iTXTech/SimpleFramework/releases)__

## Modules

* __[SimpleGUI](https://github.com/PeratX/SimpleGUI)__ - The GUI SDK based on [php-gui](https://github.com/gabrielrcouto/php-gui) for SimpleFramework(CLI).
* __[TesseractBridge](https://github.com/PeratX/TesseractBridge)__ - The bridge between Tesseract-OCR and SimpleFramework.
* __[SFQRCode](https://github.com/PeratX/SFQRCode)__ - PHPQRCode port to SimpleFramework.
* __[SimpleHtmlDom](https://github.com/PeratX/SimpleHtmlDom)__ - Simple HTML DOM Parser port to SimpleFramework, optimized for pages which cannot be correctly parsed by DOMDocument.
* __[LeetQQ](https://github.com/PhQAgent/LeetQQ)__ - SmartQQ bot framework for PHP and SimpleFramework.

## License

    Copyright (C) 2016-2019 iTX Technologies

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
