<?php

/*
 *
 * SimpleFramework
 *
 * Copyright (C) 2016-2021 iTX Technologies
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

namespace iTXTech\SimpleFramework\Util\Curl;

class Response{
	public const HTTP_HEADER_SEPARATOR = "\r\n\r\n";

	private $successful;

	private $httpVersion;
	private $httpCode;
	private $httpCodeName;

	private $header;
	private $body;

	private $multipleHeaders;

	/** @var Cookie[] */
	private $cookies;
	/** @var string[] */
	private $headers;
	private $errno;

	public function __construct($buffer, array $info, int $errno){
		$this->errno = $errno;
		$this->successful = $buffer !== false;
		if($this->successful){
			$headerSize = $info["header_size"];
			$this->header = substr($buffer, 0, $headerSize);
			$this->body = substr($buffer, $headerSize);

			//parse headers
			$headers = explode(self::HTTP_HEADER_SEPARATOR, trim($this->header));
			$this->multipleHeaders = count($headers) > 1;
			//drop redundant header, like 302
			//to get these headers, do getRawHeader and parse manually
			$headers = explode("\r\n", end($headers));
			list($this->httpVersion, $this->httpCode, $this->httpCodeName) = explode(" ", array_shift($headers));
			$this->headers = [];
			foreach($headers as $header){
				$parts = explode(": ", $header, 2);
				$k = $parts[0];
				$v = $parts[1] ?? "";
				if(!isset($this->headers[$k])){
					$this->headers[$k] = $v;
				}else{
					if(is_array($this->headers[$k])){
						$this->headers[$k][] = $v;
					}else{
						$this->headers[$k] = [$this->headers[$k], $v];
					}
				}
			}

			//parse cookies
			$this->cookies = [];
			if(isset($this->headers["Set-Cookie"])){
				if(is_array($this->headers["Set-Cookie"])){
					foreach($this->headers["Set-Cookie"] as $cookie){
						$this->cookies[] = new Cookie($cookie);
					}
				}else{
					$this->cookies[] = new Cookie($this->headers["Set-Cookie"]);
				}
			}
		}
	}

	public function isSuccessful() : bool{
		return $this->successful;
	}

	public function hasMultipleHeaders() : bool{
		return $this->multipleHeaders;
	}

	public function getHttpVersion(){
		return $this->httpVersion;
	}

	public function getHttpCode(){
		return $this->httpCode;
	}

	public function getHttpCodeName(){
		return $this->httpCodeName;
	}

	public function getRawHeader(){
		return $this->header;
	}

	public function getHeaders() : array{
		return $this->headers;
	}

	public function getHeader(string $name) : ?string{
		return $this->headers[$name] ?? null;
	}

	public function getBody(){
		return $this->body;
	}

	public function getCookies() : array{
		return $this->cookies;
	}

	public function getCookie(string $name) : ?Cookie{
		return $this->cookies[$name] ?? null;
	}

	public function getErrno() : int{
		return $this->errno;
	}

	public function hasError(){
		return ($this->errno === 0) ? true : false;
	}

	public function __toString(){
		if(!$this->successful){
			return "";
		}
		return $this->header . self::HTTP_HEADER_SEPARATOR . $this->body;
	}
}
