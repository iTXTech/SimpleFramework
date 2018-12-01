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

class AmbiguousOptionException extends UnrecognizedOptionException{
	/** @var array */
	private $matchingOptions;

	public function __construct(string $option, array $matchingOptions){
		parent::__construct($this->createMessage($option, $matchingOptions), $option);
		$this->matchingOptions = $matchingOptions;
	}

	public function getMatchingOptions() : array{
		return $this->matchingOptions;
	}

	private function createMessage(string $option, array $matchingOptions){
		$buf = "Ambiguous option:" . $option . "'  (could be: ";
		foreach($matchingOptions as $option){
			$buf .= "'" . $option . "', ";
		}
		$buf = substr($buf, 0, strlen($buf) - 2);
		return $buf;
	}
}
