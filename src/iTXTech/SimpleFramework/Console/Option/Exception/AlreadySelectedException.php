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

namespace iTXTech\SimpleFramework\Console\Option\Exception;

use iTXTech\SimpleFramework\Console\Option\Option;
use iTXTech\SimpleFramework\Console\Option\OptionGroup;

class AlreadySelectedException extends ParseException{
	/** @var OptionGroup */
	private $group;
	/** @var Option */
	private $option;

	public function __construct(OptionGroup $group, Option $option){
		parent::__construct("The option '" . $option->getKey() . "' was specified but an option from this group "
			. "has already been selected: '" . $group->getSelected() . "'");
		$this->group = $group;
		$this->option = $option;
	}

	/**
	 * Returns the option that was added to the group and triggered the exception.
	 *
	 * @return Option
	 */
	public function getOption() : Option{
		return $this->option;
	}

	/**
	 * Returns the option group where another option has been selected.
	 *
	 * @return OptionGroup
	 */
	public function getGroup() : OptionGroup{
		return $this->group;
	}
}
