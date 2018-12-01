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

use iTXTech\SimpleFramework\Util\StringUtil;
use iTXTech\SimpleFramework\Util\Util;

class Options{
	/** a map of the options with the character key */
	/** @var Option[] */
	private $shortOpts = [];

	/** a map of the options with the long key */
	/** @var Option[] */
	private $longOpts = [];

	/** a map of the required options */
	/** @var string|OptionGroup[] */
	private $requiredOpts = [];

	/** a map of the option groups */
	/** @var OptionGroup[] */
	private $optionGroups = [];

	/**
	 * Add the specified option group.
	 *
	 * @param $group OptionGroup
	 *
	 * @return $this
	 */
	public function addOptionGroup(OptionGroup $group){
		if($group->isRequired()){
			$this->requiredOpts[spl_object_hash($group)] = $group;
		}
		foreach($group->getOptions() as $option){
			$option->setRequired(false);
			$this->addOption($option);
			$this->optionGroups[$option->getKey()] = $group;
		}

		return $this;
	}

	/**
	 * Lists the OptionGroups that are members of this Options instance.
	 *
	 * @return OptionGroup[]
	 */
	public function getOptionGroups(){
		return $this->optionGroups;
	}

	/**
	 * Add an option that contains a short-name and a long-name.
	 *
	 * @param string $opt Short single-character name of the option.
	 * @param string $longOpt Long multi-character name of the option.
	 * @param bool $hasArg flag signalling if an argument is required after this option
	 * @param string $description Self`-documenting description
	 *
	 * @return $this
	 *
	 * @throws \Exception
	 */
	public function addRequiredOption(string $opt, string $longOpt, bool $hasArg, string $description){
		$option = new Option($opt, $longOpt, $hasArg, $description);
		$option->setRequired(true);
		$this->addOption($option);
		return $this;
	}

	/**
	 * Adds an option instance
	 *
	 * @param $opt Option
	 *
	 * @return $this
	 */
	public function addOption(Option $opt){
		$key = $opt->getKey();

		// add it to the long option list
		if($opt->hasLongOpt()){
			$this->longOpts[$opt->getLongOpt()] = $opt;
		}

		// if the option is required add it to the required list
		if($opt->isRequired()){
			unset($this->requiredOpts[$key]);
			$this->requiredOpts[$key] = $key;
		}

		$this->shortOpts[$key] = $opt;

		return $this;
	}

	/**
	 * Retrieve a read-only list of options in this set
	 *
	 * @return Option[]
	 */
	public function getOptions() : array{
		return $this->shortOpts;
	}

	/**
	 * Returns the required options.
	 *
	 * @return string|OptionGroup[]
	 */
	public function getRequiredOptions() : array{
		return $this->requiredOpts;
	}

	/**
	 * Retrieve the {@link Option} matching the long or short name specified.
	 *
	 * @param string $opt
	 *
	 * @return Option
	 */
	public function getOption(string $opt) : ?Option{
		$opt = Util::stripLeadingHyphens($opt);

		if(isset($this->shortOpts[$opt])){
			return $this->shortOpts[$opt];
		}

		return $this->longOpts[$opt] ?? null;
	}

	/**
	 * Returns the options with a long name starting with the name specified.
	 *
	 * @param string $opt
	 *
	 * @return string[]
	 */
	public function getMatchingOptions(string $opt) : array{
		$opt = Util::stripLeadingHyphens($opt);

		$matchingOpts = [];

		// for a perfect match return the single option only
		if(isset($this->longOpts[$opt])){
			return [$opt];
		}

		foreach($this->longOpts as $key => $longOpt){
			if(StringUtil::startsWith($key, $opt)){
				$matchingOpts[] = $key;
			}
		}

		return $matchingOpts;
	}

	/**
	 * Returns whether the named {@link Option} is a member of this {@link Options}.
	 *
	 * @param string $opt
	 *
	 * @return bool
	 */
	public function hasOption(string $opt) : bool{
		$opt = Util::stripLeadingHyphens($opt);

		return isset($this->shortOpts[$opt]) || isset($this->longOpts[$opt]);
	}

	/**
	 * Returns whether the named {@link Option} is a member of this {@link Options}.
	 *
	 * @param string $opt
	 *
	 * @return bool
	 */
	public function hasLongOption(string $opt) : bool{
		$opt = Util::stripLeadingHyphens($opt);

		return isset($this->longOpts[$opt]);
	}

	/**
	 * Returns whether the named {@link Option} is a member of this {@link Options}.
	 *
	 * @param string $opt
	 *
	 * @return bool
	 */
	public function hasShortOption(string $opt){
		$opt = Util::stripLeadingHyphens($opt);

		return isset($this->shortOpts[$opt]);
	}

	/**
	 * Returns the OptionGroup the <code>opt</code> belongs to.
	 *
	 * @param Option $opt
	 *
	 * @return OptionGroup
	 */
	public function getOptionGroup(Option $opt) : ?OptionGroup{
		return $this->optionGroups[$opt->getKey()] ?? null;
	}
}
