<?php

/*
 *
 * SimpleFramework
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace iTXTech\SimpleFramework\Module;

abstract class Packer{
	public function processFile(\Phar $phar, string $file, string $path){
		$phar->addFile($file, $path);
	}

	public function end(\Phar $phar){
	}
}
