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

use iTXTech\SimpleFramework\Console\Option\Exception\AlreadySelectedException;

class OptionGroup{

	/** hold the options */
	/** @var Option[] */
	private $optionMap = [];

	/** the name of the selected option */
	private $selected;

	/** specified whether this group is required */
	private $required = false;

	/**
	 * Add the specified <code>Option</code> to this group.
	 *
	 * @param Option $option
	 *
	 * @return $this
	 */
	public function addOption(Option $option){
		$this->optionMap[$option->getKey()] = $option;
		return $this;
	}

	/**
	 * @return Option[]
	 */
	public function getOptions() : array{
		// the values are the collection of options
		return $this->optionMap;
	}

	/**
	 * Set the selected option of this group to <code>name</code>.
	 *
	 * @param $option Option
	 *
	 * @throws AlreadySelectedException
	 */
	public function setSelected(?Option $option){
		if($option == null){
			// reset the option previously selected
			$this->selected = null;
			return;
		}

		// if no option has already been selected or the
		// same option is being reselected then set the
		// selected member variable
		if($this->selected == null || $this->selected === $option->getKey()){
			$this->selected = $option->getKey();
		}else{
			throw new AlreadySelectedException($this, $option);
		}
	}

	/**
	 * @return string
	 */
	public function getSelected() : string{
		return $this->selected;
	}

	/**
	 * @param bool $required
	 */
	public function setRequired(bool $required){
		$this->required = $required;
	}

	/**
	 * Returns whether this option group is required.
	 *
	 * @return bool
	 */
	public function isRequired() : bool{
		return $this->required;
	}

	public function __toString(){
		$buf = "[";
		foreach($this->getOptions() as $option){
			if($option->getOpt() !== null){
				$buf .= "-" . $option->getOpt();
			}else{
				$buf .= "--" . $option->getLongOpt();
			}

			if($option->getDescription() !== null){
				$buf .= " " . $option->getDescription();
			}

			$buf .= ", ";
		}
		$buf = substr($buf, 0, strlen($buf));

		return $buf;
	}
}
