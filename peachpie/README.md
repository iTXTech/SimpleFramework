# SimpleFramework for [PeachPie](https://github.com/peachpiecompiler/peachpie/)

* [PeachPie](https://github.com/peachpiecompiler/peachpie/) is a `PHP compiler to .NET`
* `SimpleFramework` aims to provide identical experience on both `ZendVM` and `PeachPie`, but we are currently working in progress.
* [SimpleFramework for PeachPie](https://github.com/iTXTech/SimpleFramework/tree/peachpie)

## Tools

* [Module Converter](module_converter.php) - *Filter source files and convert resource files into `php` files.*

## Load `SimpleFramework for PeachPie` module

```php
require_once "sf/sfloader.php";

use iTXTech\SimpleFramework\Initializer;
use iTXTech\SimpleFramework\Module\ModuleManager;

Initializer::initTerminal(); // init terminal support

$moduleManager = new ModuleManager(Initializer::getClassLoader(), __DIR__ . DIRECTORY_SEPARATOR, "");
$moduleManager->readModule("module_dir_to_load");
```
