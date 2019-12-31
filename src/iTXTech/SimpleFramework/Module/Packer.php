<?php

/*
 *
 * SimpleFramework
 *
 * Copyright (C) 2016-2020 iTX Technologies
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

namespace iTXTech\SimpleFramework\Module;

use iTXTech\SimpleFramework\Util\StringUtil;

abstract class Packer{
	public const VARIANT_TYPICAL = 0;
	public const VARIANT_COMPOSER = 1;

	public function processFile(int $variant, \Phar $phar, string $file, string $path){
		if($variant == self::VARIANT_COMPOSER or
			($variant == self::VARIANT_TYPICAL
				and !StringUtil::startsWith($path, "vendor")
				and !StringUtil::endsWith($path, "composer.lock")
				and !StringUtil::endsWith($path, "composer.json"))){
			$phar->addFile($file, $path);
		}
	}

	public function end(\Phar $phar){
	}
}
