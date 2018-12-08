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

namespace iTXTech\SimpleFramework\Util\Curl;

class Response{
	public const HTTP_HEADER_SEPARATOR = "\r\n\r\n";

	private $successfully;

	private $httpVersion;
	private $httpCode;
	private $httpCodeName;

	private $header;
	private $body;

	/** @var Cookie[] */
	private $cookies;
	/** @var string[] */
	private $headers;

	public function __construct($content){
		$this->successfully = $content !== false;
		if($this->successfully){
			list($this->header, $this->body) = explode(self::HTTP_HEADER_SEPARATOR, $content, 2);

			//parse headers
			$headers = explode("\r\n", $this->header);
			list($this->httpVersion, $this->httpCode, $this->httpCodeName) = explode(" ", array_shift($headers));
			$this->headers = [];
			foreach($headers as $header){
				list($k, $v) = explode(": ", $header, 2);
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

	public function isSuccessfully() : bool{
		return $this->successfully;
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

	public function __toString(){
		if(!$this->successfully){
			return "";
		}
		return $this->header . self::HTTP_HEADER_SEPARATOR . $this->body;
	}
}
