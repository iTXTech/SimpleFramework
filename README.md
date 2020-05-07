# SimpleFramework

[![License](https://img.shields.io/github/license/iTXTech/SimpleFramework.svg)](https://github.com/iTXTech/SimpleFramework/blob/master/LICENSE)
[![Stable](https://img.shields.io/badge/stable-2.1.0(6)-brightgreen.svg)](https://github.com/iTXTech/SimpleFramework/releases/tag/v2.1.0)
[![Developing](https://img.shields.io/badge/dev-2.2.0(7)-blue.svg)]()

__Powerful, lightweight and extensible php command line framework.__

## Introduction

SimpleFramework is a php command line framework.

Here are some features:

* Module Dependency Resolver (`composer` is also compatible)
* Built-in Console and Commands
* AsyncTask Scheduler and Multi-threading
* Network and Configuration Utilities
* All APIs can be used in a single script - *See examples*
* Module HotPatch (Requires `runkit7`)
* Command Line Options
* Coroutine Swoole Logger (Requires `Swoole 4.4+`)
* OS Integrations (Some Requires `PHP 7.4` and `FFI`)

Contributions are welcome.

### See also

* [Development Roadmap](https://github.com/iTXTech/SimpleFramework/issues/3)

### Command Line

`./sf -h`

### Module HotPatch

```json
{
  "name": "Example",
  "version": "1.0",
  "api": 6,
  "description": "Just an example",
  "author": "iTX Technologies",
  "main": "Example\\Main",
  "website": "https://itxtech.org",
  "dependency": [
    {
      "name": "Example/ExampleModule",
      "version": "1.0.0",
      "optional": true
    }
  ],
  "hotPatch": [
    {
      "class": "Example\\Main",
      "method": "foo"
    }
  ]
}
```

```php
//only support this code style!
public function foo(string $arg0, int $arg1) : int{
    echo $arg0;
    return $arg1++;
}
```

```bash
> module hotpatch Example

HotPatch for Example took 0.001 s
```

## Requirements

[Build PHP for SimpleFramework](https://github.com/iTXTech/php-build-scripts)

* [PHP](https://secure.php.net/) >= 7.2
* [pthreads](https://github.com/krakjoe/pthreads) - *Multi-threading library for PHP. Highly Recommended*
* [runkit7](https://github.com/runkit7/runkit7) - *Module HotPatch*

**Note that now swoole will break pthreads, do not use them together.**

### Integrated support

* [php-yaml](https://github.com/php/pecl-file_formats-yaml) - `Config`
* [swoole](https://github.com/swoole/swoole-src) - `SwooleLoggerHandler`

## Get SimpleFramework

* __[Releases](https://github.com/iTXTech/SimpleFramework/releases)__ - Stable release, PHAR packed

or

* `$ git clone https://github.com/iTXTech/SimpleFramework.git` - Get latest development environment for **FUN**

## License

    Copyright (C) 2016-2020 iTX Technologies

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
