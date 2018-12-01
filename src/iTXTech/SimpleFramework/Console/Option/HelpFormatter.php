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

class HelpFormatter{
	public const DEFAULT_VALUE = -1;

	/** default number of characters per line */
	public const DEFAULT_WIDTH = 74;

	/** default padding to the left of each line */
	public const DEFAULT_LEFT_PAD = 1;

	/** number of space characters to be prefixed to each description line */
	public const DEFAULT_DESC_PAD = 3;

	/** the string to display at the beginning of the usage statement */
	public const DEFAULT_SYNTAX_PREFIX = "usage: ";

	/** default prefix for shortOpts */
	public const DEFAULT_OPT_PREFIX = "-";

	/** default prefix for long Option */
	public const DEFAULT_LONG_OPT_PREFIX = "--";

	/**
	 * default separator displayed between a long Option and its value
	 **/
	public const DEFAULT_LONG_OPT_SEPARATOR = " ";

	/** default name for an argument */
	public const DEFAULT_ARG_NAME = "arg";

	// -------------------------------------------------------------- Attributes

	/**
	 * number of characters per line
	 */
	public $defaultWidth = self::DEFAULT_WIDTH;

	/**
	 * amount of padding to the left of each line
	 */
	public $defaultLeftPad = self::DEFAULT_LEFT_PAD;

	/**
	 * the number of characters of padding to be prefixed
	 * to each description line
	 */
	public $defaultDescPad = self::DEFAULT_DESC_PAD;

	/**
	 * the string to display at the beginning of the usage statement
	 */
	public $defaultSyntaxPrefix = self::DEFAULT_SYNTAX_PREFIX;

	/**
	 * the new line string
	 */
	public $defaultNewLine = PHP_EOL;

	/**
	 * the shortOpt prefix
	 */
	public $defaultOptPrefix = self::DEFAULT_OPT_PREFIX;

	/**
	 * the long Opt prefix
	 */
	public $defaultLongOptPrefix = self::DEFAULT_LONG_OPT_PREFIX;

	/**
	 * the name of the argument
	 */
	public $defaultArgName = self::DEFAULT_ARG_NAME;

	/**
	 * Comparator used to sort the options when they output in help text
	 *
	 * Defaults to case-insensitive alphabetical sorting by option key
	 */
	//protected Comparator<Option> optionComparator = new OptionComparator();

	/** The separator displayed between the long option and its value. */
	private $longOptSeparator = self::DEFAULT_LONG_OPT_SEPARATOR;

	/**
	 * Sets the 'width'.
	 *
	 * @param int $width
	 */
	public function setWidth(int $width){
		$this->defaultWidth = $width;
	}

	/**
	 * Returns the 'width'.
	 *
	 * @return int
	 */
	public function getWidth() : int{
		return $this->defaultWidth;
	}

	/**
	 * Sets the 'leftPadding'.
	 *
	 * @param int $padding
	 */
	public function setLeftPadding(int $padding){
		$this->defaultLeftPad = $padding;
	}

	/**
	 * Returns the 'leftPadding'.
	 *
	 * @return int
	 */
	public function getLeftPadding() : int{
		return $this->defaultLeftPad;
	}

	/**
	 * Sets the 'descPadding'.
	 *
	 * @param int $padding
	 */
	public function setDescPadding(int $padding){
		$this->defaultDescPad = $padding;
	}

	/**
	 * Returns the 'descPadding'.
	 *
	 * @return int
	 */
	public function getDescPadding(){
		return $this->defaultDescPad;
	}

	/**
	 * Sets the 'syntaxPrefix'.
	 *
	 * @param string $prefix
	 */
	public function setSyntaxPrefix(string $prefix){
		$this->defaultSyntaxPrefix = $prefix;
	}

	/**
	 * Returns the 'syntaxPrefix'.
	 *
	 * @return string
	 */
	public function getSyntaxPrefix() : string{
		return $this->defaultSyntaxPrefix;
	}

	/**
	 * Sets the 'newLine'.
	 *
	 * @param string
	 */
	public function setNewLine(string $newline){
		$this->defaultNewLine = $newline;
	}

	/**
	 * Returns the 'newLine'.
	 *
	 * @return string
	 */
	public function getNewLine() : string{
		return $this->defaultNewLine;
	}

	/**
	 * Sets the 'optPrefix'.
	 *
	 * @param string $prefix
	 */
	public function setOptPrefix(string $prefix){
		$this->defaultOptPrefix = $prefix;
	}

	/**
	 * Returns the 'optPrefix'.
	 *
	 * @return string
	 */
	public function getOptPrefix(){
		return $this->defaultOptPrefix;
	}

	/**
	 * Sets the 'longOptPrefix'.
	 *
	 * @param string $prefix
	 */
	public function setLongOptPrefix(string $prefix){
		$this->defaultLongOptPrefix = $prefix;
	}

	/**
	 * Returns the 'longOptPrefix'.
	 *
	 * @return string
	 */
	public function getLongOptPrefix(){
		return $this->defaultLongOptPrefix;
	}

	/**
	 * Set the separator displayed between a long option and its value.
	 * Ensure that the separator specified is supported by the parser used,
	 * typically ' ' or '='.
	 *
	 * @param string $longOptSeparator
	 */
	public function setLongOptSeparator(string $longOptSeparator){
		$this->longOptSeparator = $longOptSeparator;
	}

	/**
	 * Returns the separator displayed between a long option and its value.
	 *
	 * @return string
	 */
	public function getLongOptSeparator() : string{
		return $this->longOptSeparator;
	}

	/**
	 * Sets the 'argName'.
	 *
	 * @param string
	 */
	public function setArgName(string $name){
		$this->defaultArgName = $name;
	}

	/**
	 * Returns the 'argName'.
	 *
	 * @return string
	 */
	public function getArgName(){
		return $this->defaultArgName;
	}

	/**
	 * Print the help for options with the specified
	 * command line syntax.
	 *
	 * @param string $cmdLineSyntax
	 * @param Options $options
	 * @param bool $autoUsage
	 * @param int $width
	 * @param int $leftPad
	 * @param int $descPad
	 * @param string|null $header
	 * @param string|null $footer
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function generateHelp(string $cmdLineSyntax, Options $options, bool $autoUsage = false,
	                             int $width = self::DEFAULT_VALUE, int $leftPad = self::DEFAULT_VALUE,
	                             int $descPad = self::DEFAULT_VALUE, string $header = null, string $footer = null){
		if($width == self::DEFAULT_VALUE){
			$width = $this->getWidth();
		}
		if($leftPad == self::DEFAULT_VALUE){
			$leftPad = $this->getLeftPadding();
		}
		if($descPad == self::DEFAULT_VALUE){
			$descPad = $this->getDescPadding();
		}

		if($cmdLineSyntax == null || strlen($cmdLineSyntax) == 0){
			throw new \Exception("cmdLineSyntax not provided");
		}

		$text = "";

		if($autoUsage){
			$this->generateUsage($text, $width, $cmdLineSyntax, $options);
		}else{
			$this->generateUsage($text, $width, $cmdLineSyntax);
		}

		if($header != null && strlen(trim($header)) > 0){
			$this->generateWrapped($text, $width, 0, $header);
		}

		$this->generateOptions($text, $width, $options, $leftPad, $descPad);

		if($footer != null && strlen(trim($footer)) > 0){
			$this->generateWrapped($text, $width, 0, $footer);
		}

		return $text;
	}

	/**
	 * Prints the usage statement for the specified application.
	 *
	 * @param string $text
	 * @param int $width
	 * @param string $app
	 * @param Options|null $options
	 */
	public function generateUsage(string &$text, int $width, string $app, Options $options = null){
		if($options === null){
			$argPos = strpos($app, " ") + 1;
			$this->generateWrapped($text, $width, strlen($this->getSyntaxPrefix()) + $argPos, $this->getSyntaxPrefix() . $app);
			return;
		}
		$buff = $this->getSyntaxPrefix() . $app . " ";

		$processedGroups = [];
		$opts = $options->getOptions();
		ksort($opts);
		foreach($opts as $option){
			$group = $options->getOptionGroup($option);
			if($group !== null){
				if(!isset($processedGroups[spl_object_hash($group)])){
					$processedGroups[spl_object_hash($group)] = $group;
				}
				$this->appendOptionGroup($buff, $group);
			}else{
				$this->appendOption($buff, $option, $option->isRequired());
			}
			$buff .= " ";
		}
		$buff = substr($buff, 0, strlen($buff) - 1);

		$this->generateWrapped($text, $width, strpos($buff, " ") + 1, $buff);
	}

	private function appendOptionGroup(string &$buff, OptionGroup $group){
		if(!$group->isRequired()){
			$buff .= "[";
		}

		$opts = $group->getOptions();
		ksort($opts);

		foreach($opts as $option){
			$this->appendOption($buff, $option, true);
			$buff .= " | ";
		}

		$buff = substr($buff, 0, strlen($buff) - 3);

		if(!$group->isRequired()){
			$buff .= "]";
		}
	}

	private function appendOption(string &$buff, Option $option, bool $required){
		if(!$required){
			$buff .= "[";
		}

		if($option->getOpt() != null){
			$buff .= "-" . $option->getOpt();
		}else{
			$buff .= "--" . $option->getLongOpt();
		}
		if($option->hasArg() and ($option->getArgName() === null || strlen($option->getArgName()) !== 0)){
			$buff .= $option->getOpt() === null ? $this->longOptSeparator : " ";
			$buff .= "<" . ($option->getArgName() !== null ? $option->getArgName() : $this->getArgName()) . ">";
		}

		if(!$required){
			$buff .= "]";
		}
	}

	/**
	 * Generate the help for the specified Options to the specified writer,
	 * using the specified width, left padding and description padding.
	 *
	 * @param string $text
	 * @param int $width
	 * @param Options $options
	 * @param int $leftPad
	 * @param int $descPad
	 */
	public function generateOptions(string &$text, int $width, Options $options, int $leftPad, int $descPad){
		$this->renderOptions($text, $width, $options, $leftPad, $descPad);
		$text .= PHP_EOL;
	}

	public function generateWrapped(string &$text, int $width, int $nextLineTabStop, string $txt){
		$this->renderWrappedTextBlock($text, $width, $nextLineTabStop, $txt);
		$text .= PHP_EOL;
	}

	protected function renderOptions(string &$buffer, int $width, Options $options, int $leftPad, int $descPad){
		$lpad = $this->createPadding($leftPad);
		$dpad = $this->createPadding($descPad);

		// first create list containing only <lpad>-a,--aaa where
		// -a is opt and --aaa is long opt; in parallel look for
		// the longest opt string this list will be then used to
		// sort options ascending
		$max = 0;
		$prefixes = [];
		$opts = $options->getOptions();
		ksort($opts);

		foreach($opts as $option){
			$optBuf = "";
			if($option->getOpt() === null){
				$optBuf .= $lpad . "   " . $this->getLongOptPrefix() . $option->getLongOpt();
			}else{
				$optBuf .= $lpad . $this->getOptPrefix() . $option->getOpt();
				if($option->hasLongOpt()){
					$optBuf .= "," . $this->getLongOptPrefix() . $option->getLongOpt();
				}
			}

			if($option->hasArg()){
				$argName = $option->getArgName();
				if($argName !== null and strlen($argName) === 0){
					$optBuf .= " ";
				}else{
					$optBuf .= $option->hasLongOpt() ? $this->longOptSeparator : " ";
					$optBuf .= "<" . ($argName !== null ? $option->getArgName() : $this->getArgName()) . ">";
				}
			}

			$prefixes[] = $optBuf;
			$max = max(strlen($optBuf), $max);
		}

		$x = 0;

		foreach($opts as $option){
			$optBuf = $prefixes[$x++];

			if(strlen($optBuf) < $max){
				$optBuf .= $this->createPadding($max - strlen($optBuf));
			}

			$optBuf .= $dpad;

			$nextLineTabStop = $max + $descPad;

			if($option->getDescription() !== null){
				$optBuf .= $option->getDescription();
			}

			$this->renderWrappedText($buffer, $width, $nextLineTabStop, $optBuf);

			$buffer .= $this->getNewLine();

		}

		$buffer = substr($buffer, 0, strlen($buffer) - strlen($this->getNewLine()));
	}

	protected function renderWrappedText(string &$buffer, int $width, int $nextLineTabStop, string $text){
		$pos = $this->findWrapPos($text, $width, 0);

		if($pos == -1){
			$buffer .= rtrim($text);
			return;
		}

		$buffer .= rtrim(substr($text, 0, $pos)) . $this->getNewLine();

		if($nextLineTabStop >= $width){
			// stops infinite loop happening
			$nextLineTabStop = 1;
		}

		// all following lines must be padded with nextLineTabStop space characters
		$padding = $this->createPadding($nextLineTabStop);

		while(true){
			$text = $padding . trim(substr($text, $pos));
			$pos = $this->findWrapPos($text, $width, 0);

			if($pos == -1){
				$buffer .= $text;
				return;
			}

			if(strlen($text) > $width and $pos === $nextLineTabStop - 1){
				$pos = $width;
			}

			$buffer .= rtrim(substr($text, 0, $pos)) . $this->getNewLine();
		}
	}

	private function renderWrappedTextBlock(string &$buffer, int $width, int $nextLineTabStop, string $text){
		$lines = explode("\n", $text);
		$firstLine = true;
		foreach($lines as $line){
			if(!$firstLine){
				$buffer .= $this->getNewLine();
			}else{
				$firstLine = false;
			}
			$this->renderWrappedText($buffer, $width, $nextLineTabStop, $line);
		}
	}

	protected function findWrapPos(string $text, int $width, int $startPos){
		// the line ends before the max wrap pos or a new line char found
		$pos = strpos($text, "\n", $startPos);
		if($pos !== false && $pos <= $width){
			return $pos + 1;
		}

		$pos = strpos($text, "\t", $startPos);
		if($pos !== false && $pos <= $width){
			return $pos + 1;
		}

		if($startPos + $width >= strlen($text)){
			return -1;
		}

		// look for the last whitespace character before startPos+width
		for($pos = $startPos + $width; $pos >= $startPos; --$pos){
			$c = $text{$pos};
			if($c == ' ' || $c == '\n' || $c == '\r'){
				break;
			}
		}

		// if we found it - just return
		if($pos > $startPos){
			return $pos;
		}

		// if we didn't find one, simply chop at startPos+width
		$pos = $startPos + $width;

		return $pos == strlen($text) ? -1 : $pos;
	}

	protected function createPadding(int $len) : string{
		return str_repeat(" ", $len);
	}
}
