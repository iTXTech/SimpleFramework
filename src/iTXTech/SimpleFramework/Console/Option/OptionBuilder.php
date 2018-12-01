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

class OptionBuilder{
	public $opt;
	public $description = "";
	public $longOpt;
	public $argName;
	public $required = false;
	public $optionalArg;
	public $numberOfArgs = Option::UNINITIALIZED;
	public $valuesep;

	/**
	 * OptionBuilder constructor.
	 *
	 * @param $opt
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct($opt){
		OptionValidator::validateOption($opt);
		$this->opt = $opt;
	}

	/**
	 * Sets the display name for the argument value.
	 *
	 * @param string $argName
	 *
	 * @return $this
	 */
	public function argName(string $argName){
		$this->argName = $argName;
		return $this;
	}

	/**
	 * Sets the description for this option.
	 *
	 * @param string $description
	 *
	 * @return $this
	 */
	public function desc(string $description){
		$this->description = $description;
		return $this;
	}

	/**
	 * Sets the long name of the Option.
	 *
	 * @param string $longOpt
	 *
	 * @return $this
	 */
	public function longOpt(string $longOpt){
		$this->longOpt = $longOpt;
		return $this;
	}

	/**
	 * Sets the number of argument values the Option can take.
	 *
	 * @param int $numberOfArgs
	 *
	 * @return $this
	 */
	public function numberOfArgs(int $numberOfArgs){
		$this->numberOfArgs = $numberOfArgs;
		return $this;
	}

	/**
	 * Sets whether the Option can have an optional argument.
	 *
	 * @param bool $isOptional
	 *
	 * @return $this
	 */
	public function optionalArg(bool $isOptional){
		$this->optionalArg = $isOptional;
		return $this;
	}

	/**
	 * Sets whether the Option is mandatory.
	 *
	 * @param bool $required
	 *
	 * @return $this
	 */
	public function required(bool $required = true){
		$this->required = $required;
		return $this;
	}

	public function valueSeparator(string $sep = "="){
		$this->valuesep = $sep;
		return $this;
	}

	/**
	 * Indicates if the Option has an argument or not.
	 *
	 * @param bool $hasArg
	 *
	 * @return $this
	 */
	public function hasArg(bool $hasArg = true){
		// set to UNINITIALIZED when no arg is specified to be compatible with OptionBuilder
		$this->numberOfArgs = $hasArg ? 1 : Option::UNINITIALIZED;
		return $this;
	}

	/**
	 * Indicates that the Option can have unlimited argument values.
	 *
	 * @return $this
	 */
	public function hasArgs(){
		$this->numberOfArgs = Option::UNLIMITED_VALUES;
		return $this;
	}

	/**
	 * Constructs an Option with the values declared by this {@link Builder}.
	 *
	 * @return Option
	 * @throws \InvalidArgumentException
	 */
	public function build() : Option{
		if($this->opt == null && $this->longOpt == null){
			throw new \InvalidArgumentException("Either opt or longOpt must be specified");
		}
		return new Option($this);
	}
}
