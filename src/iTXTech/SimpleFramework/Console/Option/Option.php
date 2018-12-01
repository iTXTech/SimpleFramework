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
 * @author iTXTech
 * @link https://itxtech.org
 *
 */

namespace iTXTech\SimpleFramework\Console\Option;

class Option{
	/** constant that specifies the number of argument values has not been specified */
	public const UNINITIALIZED = -1;

	/** constant that specifies the number of argument values is infinite */
	public const UNLIMITED_VALUES = -2;

	private $opt;
	private $description;
	private $longOpt;
	private $argName;
	private $required;
	private $optionalArg;
	private $numberOfArgs = self::UNINITIALIZED;
	private $valuesep;
	/** @var string[] */
	private $values = [];

	/**
	 * Option constructor.
	 *
	 * @param $builder
	 * @param string $longOpt
	 * @param bool $hasArg
	 * @param string $description
	 *
	 * @throws \Exception
	 */
	public function __construct($builder, string $longOpt = null, bool $hasArg = false, string $description = null){
		if($builder instanceof OptionBuilder){
			$this->argName = $builder->argName;
			$this->description = $builder->description;
			$this->longOpt = $builder->longOpt;
			$this->numberOfArgs = $builder->numberOfArgs;
			$this->opt = $builder->opt;
			$this->optionalArg = $builder->optionalArg;
			$this->required = $builder->required;
			$this->valuesep = $builder->valuesep;
		}else{
			OptionValidator::validateOption($builder);
			$this->opt = $builder;
			$this->longOpt = $longOpt;
			if($hasArg){
				$this->numberOfArgs = 1;
			}
			$this->description = $description;
		}
	}

	/**
	 * @param bool $required
	 */
	public function setRequired(bool $required){
		$this->required = $required;
	}

	/**
	 * Returns the id of this Option.  This is only set when the
	 * Option shortOpt is a single character.  This is used for switch
	 * statements.
	 *
	 * @return int
	 */
	public function getId() : int{
		return ord($this->getKey(){0});
	}

	/**
	 * Returns the 'unique' Option identifier.
	 *
	 * @return string
	 */
	public function getKey() : string{
		// if 'opt' is null, then it is a 'long' option
		return ($this->opt == null) ? $this->longOpt : $this->opt;
	}

	/**
	 * Retrieve the name of this Option.
	 *
	 * It is this String which can be used with
	 * {@link CommandLine#hasOption(String opt)} and
	 * {@link CommandLine#getOptionValue(String opt)} to check
	 * for existence and argument.
	 *
	 * @return string
	 */
	public function getOpt() : string{
		return $this->opt;
	}

	/**
	 * Retrieve the long name of this Option.
	 *
	 * @return string
	 */
	public function getLongOpt() : string{
		return $this->longOpt;
	}

	/**
	 * Whether this Option can have an optional argument
	 *
	 * @return bool
	 */
	public function hasOptionalArg() : bool{
		return $this->optionalArg;
	}

	/**
	 * Query to see if this Option has a long name
	 *
	 * @return bool
	 */
	public function hasLongOpt() : bool{
		return $this->longOpt != null;
	}

	/**
	 * Query to see if this Option requires an argument
	 *
	 * @return bool
	 */
	public function hasArg() : bool{
		return $this->numberOfArgs > 0 || $this->numberOfArgs == self::UNLIMITED_VALUES;
	}

	/**
	 * Retrieve the self-documenting description of this Option
	 *
	 * @return string
	 */
	public function getDescription() : string{
		return $this->description;
	}

	/**
	 * Query to see if this Option is mandatory
	 *
	 * @return boolean flag indicating whether this Option is mandatory
	 */
	public function isRequired() : bool{
		return $this->required;
	}

	/**
	 * Gets the display name for the argument value.
	 *
	 * @return string
	 */
	public function getArgName() : string{
		return $this->argName;
	}

	/**
	 * Returns whether the display name for the argument value has been set.
	 *
	 * @return bool
	 */
	public function hasArgName() : bool{
		return $this->argName != null && strlen($this->argName) > 0;
	}

	/**
	 * Query to see if this Option can take many values.
	 *
	 * @return bool
	 */
	public function hasArgs() : bool{
		return $this->numberOfArgs > 1 || $this->numberOfArgs == self::UNLIMITED_VALUES;
	}

	/**
	 * Returns the value separator character.
	 *
	 * @return string
	 */
	public function getValueSeparator() : string{
		return $this->valuesep;
	}

	/**
	 * Return whether this Option has specified a value separator.
	 *
	 * @return bool
	 */
	public function hasValueSeparator() : bool{
		return $this->valuesep !== null;
	}

	/**
	 * Returns the number of argument values this Option can take.
	 *
	 * @return int
	 */
	public function getArgs() : int{
		return $this->numberOfArgs;
	}

	/**
	 * Adds the specified value to this Option.
	 *
	 * @param string $value
	 *
	 * @throws \Exception
	 */
	public function addValueForProcessing(string $value){
		if($this->numberOfArgs == self::UNINITIALIZED){
			throw new \Exception("NO_ARGS_ALLOWED");
		}
		$this->processValue($value);
	}

	/**
	 * Processes the value.  If this Option has a value separator
	 * the value will have to be parsed into individual tokens.  When
	 * n-1 tokens have been processed and there are more value separators
	 * in the value, parsing is ceased and the remaining characters are
	 * added as a single token.
	 *
	 * @param string
	 *
	 * @throws \Exception
	 */
	private function processValue(string $value){
		// this Option has a separator character
		if($this->hasValueSeparator()){
			// get the separator character
			$sep = $this->getValueSeparator();

			// store the index for the value separator
			$index = strpos($value, $sep);

			// while there are more value separators
			while($index != -1){
				// next value to be added
				if(count($this->values) == $this->numberOfArgs - 1){
					break;
				}

				// store
				$this->add(substr($value, 0, $index));

				// parse
				$value = substr($value, $index + 1);

				// get new index
				$index = strpos($value, $sep);
			}
		}

		// store the actual value or the last value that has been parsed
		$this->add($value);
	}

	/**
	 * Add the value to this Option.  If the number of arguments
	 * is greater than zero and there is enough space in the list then
	 * add the value.  Otherwise, throw a runtime exception.
	 *
	 * @param string $value
	 *
	 * @throws \Exception
	 */
	private function add(string $value){
		if(!$this->acceptsArg()){
			throw new \Exception("Cannot add value, list full.");
		}

		// store value
		$this->values[] = $value;
	}

	/**
	 * Returns the specified value of this Option or
	 * <code>null</code> if there is no value.
	 *
	 * @param int $index
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
	public function getValue(int $index = 0) : string{
		return $this->hasNoValues() ? null : $this->values[$index];
	}

	/**
	 * Returns the value/first value of this Option or the
	 * <code>defaultValue</code> if there is no value.
	 *
	 * @param $defaultValue string
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
	public function getValueDef(string $defaultValue) : string{
		$value = $this->getValue();

		return ($value != null) ? $value : $defaultValue;
	}

	/**
	 * @return string[]
	 */
	public function getValues() : array{
		return $this->values;
	}

	/**
	 * Returns whether this Option has any values.
	 *
	 * @return bool
	 */
	private function hasNoValues() : bool{
		return count($this->values) === 0;
	}

	/**
	 * Clear the Option values. After a parse is complete, these are left with
	 * data in them and they need clearing if another parse is done.
	 */
	public function clearValues(){
		$this->values = [];
	}

	/**
	 * Tells if the option can accept more arguments.
	 *
	 * @return false if the maximum number of arguments is reached
	 */
	public function acceptsArg() : bool{
		return ($this->hasArg() || $this->hasArgs() || $this->hasOptionalArg()) &&
			($this->numberOfArgs <= 0 || count($this->values) < $this->numberOfArgs);
	}

	/**
	 * Tells if the option requires more arguments to be valid.
	 *
	 * @return false if the option doesn't require more arguments
	 */
	public function requiresArg() : bool{
		if($this->optionalArg){
			return false;
		}

		if($this->numberOfArgs == self::UNLIMITED_VALUES){
			return empty($this->values);
		}
		return $this->acceptsArg();
	}
}
