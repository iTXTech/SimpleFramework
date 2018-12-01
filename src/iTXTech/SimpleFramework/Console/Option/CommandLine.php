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

namespace iTXTech\SimpleFramework\Console\Option;

use iTXTech\SimpleFramework\Util\Util;

class CommandLine{

	/** the unrecognized options/arguments */
	/** @var string[] */
	private $args = [];

	/** the processed options */
	/** @var Option[] */
	private $options = [];

	/**
	 * Query to see if an option has been set.
	 *
	 * @param Option|string $option
	 *
	 * @return bool
	 */
	public function hasOption($option){
		if(is_string($option)){
			$option = $this->resolveOption($option);
		}
		foreach($this->options as $opt){
			if($opt->equals($option)){
				return true;
			}
		}
		return false;
	}

	/**
	 * Retrieve the first argument, if any, of this option.
	 *
	 * @param Option|string $option
	 * @param string $defaultValue
	 *
	 * @return string
	 */
	public function getOptionValue($option, ?string $defaultValue = null) : ?string{
		if(is_string($option)){
			$option = $this->resolveOption($option);
		}
		if($option === null){
			return null;
		}

		$values = $this->getOptionValues($option);
		return ($values == null) ? $defaultValue : $values[0];
	}

	/**
	 * Retrieves the array of values, if any, of an option.
	 *
	 * @param Option|string $option
	 *
	 * @return string[]
	 * @since 1.5
	 */
	public function getOptionValues($option){
		if(is_string($option)){
			$option = $this->resolveOption($option);
		}
		$values = [];

		foreach($this->options as $processedOption){
			if($processedOption->equals($option)){
				$values = array_merge($values, $processedOption->getValues());
			}
		}

		return $values;
	}

	/**
	 * Retrieves the option object given the long or short option as a String
	 *
	 * @param string $opt
	 *
	 * @return Option|null
	 */
	private function resolveOption(string $opt){
		$opt = Util::stripLeadingHyphens($opt);
		foreach($this->options as $option){
			if($opt === $option->getOpt()){
				return $option;
			}
			if($opt === $option->getLongOpt()){
				return $option;
			}
		}

		return null;
	}

	/**
	 * Retrieve any left-over non-recognized options and arguments
	 *
	 * @return string[]
	 */
	public function getArgs(){
		return $this->args;
	}

	/**
	 * Add left-over unrecognized option/argument.
	 *
	 * @param string $arg
	 */
	public function addArg(string $arg){
		$this->args[] = $arg;
	}

	/**
	 * Add an option to the command line.  The values of the option are stored.
	 *
	 * @param Option $opt
	 */
	public function addOption(Option $opt){
		$this->options[] = $opt;
	}

	/**
	 * Returns an array of the processed Options.
	 *
	 * @return Option[]
	 */
	public function getOptions(){
		return $this->options;
	}
}
