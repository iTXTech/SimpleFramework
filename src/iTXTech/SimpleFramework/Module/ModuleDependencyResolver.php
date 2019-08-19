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

interface ModuleDependencyResolver{
	/**
	 * @param Module $module
	 * @return bool
	 */
	public function resolveDependencies(Module $module): bool;

	public function init();
}
