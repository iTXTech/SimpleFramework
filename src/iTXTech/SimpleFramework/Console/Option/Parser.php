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

namespace iTXTech\SimpleFramework\Console\Option;

use iTXTech\SimpleFramework\Console\Option\Exception\AlreadySelectedException;
use iTXTech\SimpleFramework\Console\Option\Exception\AmbiguousOptionException;
use iTXTech\SimpleFramework\Console\Option\Exception\MissingArgumentException;
use iTXTech\SimpleFramework\Console\Option\Exception\MissingOptionException;
use iTXTech\SimpleFramework\Console\Option\Exception\ParseException;
use iTXTech\SimpleFramework\Console\Option\Exception\UnrecognizedOptionException;
use iTXTech\SimpleFramework\Util\StringUtil;
use iTXTech\SimpleFramework\Util\Util;

class Parser{
	/** @var CommandLine */
	protected $cmd;
	/** @var Options */
	protected $options;
	protected $stopAtNonOption;
	protected $currentToken;
	/** @var Option */
	protected $currentOption;
	protected $skipParsing;

	protected $expectedOpts = [];

	private $allowPartialMatching;

	public function __construct(){
		$this->allowPartialMatching = true;
	}

	/**
	 * @param Options $options
	 * @param array|null $arguments
	 * @param bool $stopAtNonOption
	 * @param array|null $properties
	 *
	 * @return CommandLine
	 * @throws ParseException
	 */
	public function parse(Options $options, array $arguments = null,
	                      bool $stopAtNonOption = false, array $properties = null) : CommandLine{
		$this->options = $options;
		$this->stopAtNonOption = $stopAtNonOption;
		$this->skipParsing = false;
		$this->currentOption = null;
		$this->expectedOpts = $options->getRequiredOptions();

		foreach($options->getOptionGroups() as $group){
			$group->setSelected(null);
		}

		$this->cmd = new CommandLine();

		if($arguments !== null){
			foreach($arguments as $argument){
				$this->handleToken($argument);
			}
		}

		$this->checkRequiredArgs();

		$this->handleProperties($properties);

		$this->checkRequiredOptions();

		return $this->cmd;
	}

	/**
	 * @param array|null $properties
	 *
	 * @throws ParseException
	 */
	private function handleProperties(?array $properties){
		if($properties === null){
			return;
		}

		foreach($properties as $option => $v){
			$opt = $this->options->getOption($option);
			if($opt === null){
				throw new UnrecognizedOptionException("Default option wasn't defined", $option);
			}
			$group = $this->options->getOptionGroup($opt);
			$selected = ($group !== null and $group->getSelected() !== null);

			if(!$this->cmd->hasOption($option) and !$selected){
				if($opt->hasArg()){
					if($opt->getValues() === null or strlen($opt->getValues()) === 0){
						$opt->addValueForProcessing($v);
					}
				}elseif(!in_array(strtolower($v), ["yes", "true", "1"])){
					continue;
				}

				$this->handleOption($opt);
				$this->currentOption = null;
			}
		}
	}

	/**
	 * @throws MissingOptionException
	 */
	protected function checkRequiredOptions(){
		if(!empty($this->expectedOpts)){
			throw new MissingOptionException($this->expectedOpts);
		}
	}

	/**
	 * @throws MissingArgumentException
	 */
	private function checkRequiredArgs(){
		if($this->currentOption !== null && $this->currentOption->requiresArg()){
			throw new MissingArgumentException($this->currentOption);
		}
	}

	/**
	 * @param string $token
	 *
	 * @throws ParseException
	 */
	private function handleToken(string $token){
		$this->currentToken = $token;

		if($this->skipParsing){
			$this->cmd->addArg($token);
		}elseif($token === "--"){
			$this->skipParsing = true;
		}elseif($this->currentOption !== null and $this->currentOption->acceptsArg() and $this->isArgument($token)){
			$this->currentOption->addValueForProcessing(Util::stripLeadingAndTrailingQuotes($token));
		}elseif(StringUtil::startsWith($token, "--")){
			$this->handleLongOption($token);
		}elseif(StringUtil::startsWith($token, "-") and $token !== "-"){
			$this->handleShortAndLongOption($token);
		}else{
			$this->handleUnknownToken($token);
		}

		if($this->currentOption !== null && !$this->currentOption->acceptsArg()){
			$this->currentOption = null;
		}
	}

	private function isArgument(string $token) : bool{
		return !$this->isOption($token) || is_numeric($token);
	}

	private function isOption(string $token) : bool{
		return $this->isLongOption($token) || $this->isShortOption($token);
	}

	private function isShortOption(string $token) : bool{
		if(!StringUtil::startsWith($token, "-") || strlen($token) === 1){
			return false;
		}

		$pos = strpos($token, "=");
		$optName = ($pos === false ? substr($token, 1) : substr($token, 1, $pos));
		if($this->options->hasShortOption($optName)){
			return true;
		}

		return strlen($optName) > 1 and $this->options->hasShortOption($optName{0});
	}

	private function isLongOption(string $token) : bool{
		if(!StringUtil::startsWith($token, "-") || strlen($token) === 1){
			return false;
		}

		$pos = strpos($token, "=");
		$t = ($pos === false ? $token : substr($token, 0, $pos));

		if(!empty($this->getMatchingLongOptions($t))){
			return true;
		}elseif($this->getLongPrefix($token) !== null and !StringUtil::startsWith($token, "--")){
			return true;
		}

		return false;
	}

	/**
	 * Handles an unknown token. If the token starts with a dash an
	 * UnrecognizedOptionException is thrown. Otherwise the token is added
	 * to the arguments of the command line. If the stopAtNonOption flag
	 * is set, this stops the parsing and the remaining tokens are added
	 * as-is in the arguments of the command line.
	 *
	 * @param string $token
	 *
	 * @throws UnrecognizedOptionException
	 */
	private function handleUnknownToken(string $token){
		if(StringUtil::startsWith($token, "-") and strlen($token) > 1 and !$this->stopAtNonOption){
			throw new UnrecognizedOptionException("Unrecognized option: " . $token, $token);
		}

		$this->cmd->addArg($token);
		if($this->stopAtNonOption){
			$this->skipParsing = true;
		}
	}

	/**
	 * @param string $token
	 *
	 * @throws ParseException
	 */
	private function handleLongOption(string $token){
		if(strpos($token, "=") === false){
			$this->handleLongOptionWithoutEqual($token);
		}else{
			$this->handleLongOptionWithEqual($token);
		}
	}

	/**
	 * @param string $token
	 *
	 * @throws ParseException
	 */
	private function handleLongOptionWithoutEqual(string $token){
		$matchingOpts = $this->getMatchingLongOptions($token);
		if(empty($matchingOpts)){
			$this->handleUnknownToken($this->currentToken);
		}elseif(count($matchingOpts) > 1 and !$this->options->hasLongOption($token)){
			throw new AmbiguousOptionException($token, $matchingOpts);
		}else{
			$key = $this->options->hasLongOption($token) ? $token : $matchingOpts[0];
			$this->handleOption($this->options->getOption($key));
		}
	}

	/**
	 * @param string $token
	 *
	 * @throws ParseException
	 */
	private function handleLongOptionWithEqual(string $token){
		list($opt, $value) = explode("=", $token, 2);
		$matchingOpts = $this->getMatchingLongOptions($opt);
		if(empty($matchingOpts)){
			$this->handleUnknownToken($this->currentToken);
		}elseif(count($matchingOpts) > 1 and !$this->options->hasLongOption($token)){
			throw new AmbiguousOptionException($token, $matchingOpts);
		}else{
			$key = $this->options->hasLongOption($opt) ? $opt : $matchingOpts[0];
			$option = $this->options->getOption($key);

			if($option->acceptsArg()){
				$this->handleOption($option);
				$this->currentOption->addValueForProcessing($value);
				$this->currentOption = null;
			}else{
				$this->handleUnknownToken($this->currentToken);
			}
		}
	}

	/**
	 * @param string $token
	 *
	 * @throws ParseException
	 */
	private function handleShortAndLongOption(string $token){
		$t = Util::stripLeadingHyphens($token);
		$pos = strpos($t, "=");

		if(strlen($t) === 1){
			if($this->options->hasShortOption($t)){
				$this->handleOption($this->options->getOption($t));
			}else{
				$this->handleUnknownToken($token);
			}
		}elseif($pos === false){
			if($this->options->hasShortOption($t)){
				$this->handleOption($this->options->getOption($t));
			}elseif(!empty($this->getMatchingLongOptions($t))){
				$this->handleLongOptionWithoutEqual($token);
			}else{
				$opt = $this->getLongPrefix($t);

				if($opt !== null and $this->options->getOption($opt)->acceptsArg()){
					$this->handleOption($this->options->getOption($opt));
					$this->currentOption->addValueForProcessing(substr($t, strlen($opt)));
					$this->currentOption = null;
				}elseif($opt !== null and $this->isJavaProperty($t)){
					$this->handleOption($this->options->getOption($opt{0}));
					$this->currentOption->addValueForProcessing(substr($t, 1));
					$this->currentOption = null;
				}else{
					$this->handleConcatenatedOptions($token);
				}
			}
		}else{
			list($opt, $value) = explode("=", $t, 2);

			if(strlen($opt) === 1){
				$option = $this->options->getOption($opt);
				if($option !== null and $option->acceptsArg()){
					$this->handleOption($option);
					$this->currentOption->addValueForProcessing($value);
					$this->currentOption = null;
				}else{
					$this->handleUnknownToken($t);
				}
			}elseif($this->isJavaProperty($opt)){
				$this->handleOption($this->options->getOption($opt{0}));
				$this->currentOption->addValueForProcessing(substr($opt, 1));
				$this->currentOption->addValueForProcessing($value);
				$this->currentOption = null;
			}else{
				$this->handleLongOptionWithEqual($token);
			}
		}
	}

	private function getLongPrefix(string $token) : ?string{
		$t = Util::stripLeadingHyphens($token);
		$opt = null;
		for($i = strlen($t) - 2; $i > 1; $i--){
			$prefix = substr($t, 0, $i);
			if($this->options->hasLongOption($prefix)){
				$opt = $prefix;
				break;
			}
		}

		return $opt;
	}

	private function isJavaProperty(string $token){
		$opt = $token{0};
		$option = $this->options->getOption($opt);

		return $option !== null && ($option->getArgs() >= 2 or $option->getArgs() === Option::UNLIMITED_VALUES);
	}

	/**
	 * @param Option $option
	 *
	 * @throws ParseException
	 */
	private function handleOption(Option $option){
		$this->checkRequiredArgs();

		$option = clone $option;

		$this->updateRequiredOptions($option);

		$this->cmd->addOption($option);

		if($option->hasArg()){
			$this->currentOption = $option;
		}else{
			$this->currentOption = null;
		}
	}

	/**
	 * @param Option $option
	 *
	 * @throws AlreadySelectedException
	 */
	private function updateRequiredOptions(Option $option){
		if($option->isRequired()){
			unset($this->expectedOpts[$option->getKey()]);
		}

		if(($group = $this->options->getOptionGroup($option)) !== null){
			if($group->isRequired()){
				unset($this->expectedOpts[spl_object_hash($group)]);
			}

			$group->setSelected($option);
		}
	}

	private function getMatchingLongOptions(string $token) : array{
		if($this->allowPartialMatching){
			return $this->options->getMatchingOptions($token);
		}else{
			$matches = [];
			if($this->options->hasLongOption($token)){
				$option = $this->options->getOption($token);
				$matches[] = $option->getLongOpt();
			}
			return $matches;
		}
	}

	/**
	 * @param string $token
	 *
	 * @throws ParseException
	 */
	protected function handleConcatenatedOptions(string $token){
		for($i = 1; $i < strlen($token) - 1; $i++){
			$c = $token{$i};
			if($this->options->hasOption($c)){
				$this->handleOption($this->options->getOption($c));
				if($this->currentOption !== null and strlen($token) != $i + 1){
					$this->currentOption->addValueForProcessing(substr($token, $i + 1));
					break;
				}
			}else{
				$this->handleUnknownToken($this->stopAtNonOption and ($i > 1 ? substr($token, $i) : $token));
				break;
			}
		}
	}
}
