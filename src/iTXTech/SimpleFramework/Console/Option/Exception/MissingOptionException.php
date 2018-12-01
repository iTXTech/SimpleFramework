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
