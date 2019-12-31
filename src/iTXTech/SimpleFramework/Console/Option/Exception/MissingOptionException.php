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

use iTXTech\SimpleFramework\Console\Option\OptionGroup;

class MissingOptionException extends ParseException{
	/** @var array */
	private $missingOptions;

	public function __construct(array $missingOptions){
		parent::__construct($this->createMessage($missingOptions));
		$this->missingOptions = $missingOptions;
	}

	/**
	 * @return string[]|OptionGroup[]
	 */
	public function getMissingOptions() : array{
		return $this->missingOptions;
	}

	private function createMessage(array $missingOptions) : string{
		$buf = "Missing required option" . (count($missingOptions) === 1 ? "" : "s") . ": ";
		$buf .= implode(", ", $missingOptions);

		return $buf;
	}
}
