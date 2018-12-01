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

class OptionGroup{

	/** hold the options */
	/** @var Option[] */
	private $optionMap = [];

	/** the name of the selected option */
	private $selected;

	/** specified whether this group is required */
	private $required;

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
	 * @return bool
	 */
	public function setSelected(?Option $option) : bool{
		if($option == null){
			// reset the option previously selected
			$this->selected = null;
			return true;
		}

		// if no option has already been selected or the
		// same option is being reselected then set the
		// selected member variable
		if($this->selected == null || $this->selected === $option->getKey()){
			$this->selected = $option->getKey();
		}else{
			return false;
		}
		return true;
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
}
