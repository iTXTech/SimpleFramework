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

use iTXTech\SimpleFramework\Console\Option\Option;

class UnrecognizedOptionException extends ParseException{
	/** @var Option */
	private $option;

	public function __construct(string $message, string $option){
		parent::__construct($message);
		$this->option = $option;
	}

	/**
	 * Returns the unrecognized option.
	 *
	 * @return Option
	 */
	public function getOption() : Option{
		return $this->option;
	}
}
