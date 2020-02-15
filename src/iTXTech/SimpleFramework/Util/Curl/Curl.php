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

namespace iTXTech\SimpleFramework\Util\Curl;

use iTXTech\SimpleFramework\Scheduler\AsyncTask;
use iTXTech\SimpleFramework\Scheduler\OnCompletionListener;
use iTXTech\SimpleFramework\Scheduler\Scheduler;
use iTXTech\SimpleFramework\Util\Util;

class Curl{
	public static $GLOBAL_PROXY = "";

	protected $curl;
	protected $curlOpts;

	protected $headers = [];
	public $url;

	/** @var Response */
	protected $response;
	/** @var Preprocessor */
	protected $preprocessor;

	private static $CURL_CLASS = Curl::class;

	public static function newInstance() : Curl{
		return new self::$CURL_CLASS;
	}


	public static function setCurlClass(string $class) : bool{
		if(is_a($class, Curl::class, true)){
			self::$CURL_CLASS = $class;
			return true;
		}
		return false;
	}

	public function __construct(){
		$this->reload();
		if(self::$GLOBAL_PROXY !== ""){
			$url = parse_url(self::$GLOBAL_PROXY);
			$schemes = [
				"http" => CURLPROXY_HTTP,
				"https" => CURLPROXY_HTTPS,
				"socks4" => CURLPROXY_SOCKS4,
				"socks5" => CURLPROXY_SOCKS5
			];
			$this->setProxy($url["host"] . ":" . $url["port"], $schemes[$url["scheme"] ?? ""] ?? "",
				$url["user"] ?? "", $url["pass"] ?? "");
		}
	}

	public function reload(){
		if(is_resource($this->curl)){
			curl_close($this->curl);
		}
		$this->curl = curl_init();
		$this->curlOpts = [];

		if(Util::getOS() === Util::OS_WINDOWS){
			$this->verifyCert(false);
		}
		$this->setOpt(CURLOPT_RETURNTRANSFER, 1);
		$this->setOpt(CURLOPT_HEADER, 1);
		$this->setTimeout(10);
		return $this;
	}

	public function setPreprocessor(Preprocessor $preprocessor){
		$this->preprocessor = $preprocessor;
		return $this;
	}

	public function verifyCert(bool $enable){
		$this->setOpt(CURLOPT_SSL_VERIFYHOST, $enable ? 1 : 0);
		$this->setOpt(CURLOPT_SSL_VERIFYPEER, $enable ? 1 : 0);
		return $this;
	}

	public function setProxy(string $address, int $type = CURLPROXY_HTTP, string $name = "", string $pass = ""){
		$this->setOpt(CURLOPT_PROXYTYPE, $type);
		$this->setOpt(CURLOPT_PROXY, $address);
		if($name !== ""){
			$this->setOpt(CURLOPT_PROXYUSERNAME, $name);
		}
		if($pass !== ""){
			$this->setOpt(CURLOPT_PROXYUSERPWD, $pass);
		}
		return $this;
	}

	public function getUrl(){
		return $this->url;
	}

	public function getResponse() : Response{
		return $this->response;
	}

	public function setUserAgent(string $ua){
		$this->setOpt(CURLOPT_USERAGENT, $ua);
		return $this;
	}

	public function setUrl(string $url){
		$this->url = $url;
		return $this;
	}

	public function setHeaders(array $arr){
		$this->headers = $arr;
		return $this;
	}

	public function setHeader($k, string $v = ""){
		if(is_string($k)){
			$this->headers[$k] = $v;
		}
		return $this;
	}


	/**
	 * @param Cookie[] $cookies
	 *
	 * @return $this
	 */
	public function setCookies(array $cookies){
		$payload = "";
		foreach($cookies as $cookie){
			$payload .= $cookie->getName() . "=" . $cookie->getValue() . "; ";
		}
		$payload = substr($payload, 0, strlen($payload) - 2);
		$this->setOpt(CURLOPT_COOKIE, $payload);
		return $this;
	}

	public function setReferer(string $referer){
		$this->setOpt(CURLOPT_REFERER, $referer);
		return $this;
	}

	public function setGet(array $get){
		$payload = '?';
		foreach($get as $key => $content){
			$payload .= urlencode($key) . '=' . urlencode($content) . '&';
		}
		$this->url .= substr($payload, 0, strlen($payload) - 1);
		return $this;
	}

	public function setPost(array $post){
		$payload = '';
		foreach($post as $key => $content){
			$payload .= urlencode($key) . '=' . urlencode($content) . '&';
		}
		$this->setOpt(CURLOPT_POST, 1);
		$this->setOpt(CURLOPT_POSTFIELDS, substr($payload, 0, strlen($payload) - 1));
		return $this;
	}

	public function setEncPost($post){
		$this->setOpt(CURLOPT_POST, 1);
		$this->setOpt(CURLOPT_POSTFIELDS, $post);
		return $this;
	}

	public function setTimeout(int $timeout){
		$this->setOpt(CURLOPT_CONNECTTIMEOUT, $timeout);
		$this->setOpt(CURLOPT_TIMEOUT, $timeout);
		return $this;
	}

	public function setOpt(int $option, $value){
		$this->curlOpts[$option] = $value;
		return $this;
	}

	/**
	 * If you are experiencing incomplete response, try this.
	 */
	public function fixIncompleteResponse(){
		return $this->setOpt(CURLOPT_ENCODING, "");
	}

	protected function buildRequest(){
		if($this->preprocessor !== null){
			$this->preprocessor->process($this);
		}
		$headers = [];
		foreach($this->headers as $k => $v){
			$headers[] = $k . ": " . $v;
		}
		$this->setOpt(CURLOPT_HTTPHEADER, $headers);
		$this->setOpt(CURLOPT_URL, $this->url);

		if(($interface = InterfaceSelector::select()) !== ""){
			$this->setOpt(CURLOPT_INTERFACE, $interface);
		}
	}

	public function exec(){
		$this->buildRequest();
		curl_setopt_array($this->curl, $this->curlOpts);
		$this->response = new Response(curl_exec($this->curl), curl_getinfo($this->curl), curl_errno($this->curl));
		$this->reload();
		return $this->response;
	}

	public function uploadFile(array $assoc = [], array $files = [],
	                           string $fileType = "application/octet-stream"){
		$body = [];
		// invalid characters for "name" and "filename"
		$disallow = ["\0", "\"", "\r", "\n"];

		// build normal parameters
		foreach($assoc as $k => $v){
			$k = str_replace($disallow, "_", $k);
			$body[] = implode("\r\n", [
				"Content-Disposition: form-data; name=\"{$k}\"",
				"",
				filter_var($v),
			]);
		}

		foreach($files as $k => $v){
			switch(true){
				case false === $v = realpath(filter_var($v)):
				case !is_file($v):
				case !is_readable($v):
					continue 2;
			}
			$data = file_get_contents($v);
			$v = explode(DIRECTORY_SEPARATOR, $v);
			$v = end($v);
			$k = str_replace($disallow, "_", $k);
			$v = str_replace($disallow, "_", $v);
			$body[] = implode("\r\n", [
				"Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$v}\"",
				"Content-Type: $fileType",
				"",
				$data,
			]);
		}

		// generate safe boundary
		do{
			$boundary = "---------------------" . md5(mt_rand() . microtime());
		}while(preg_grep("/{$boundary}/", $body));

		// add boundary for each parameters
		array_walk($body, function(&$part) use ($boundary){
			$part = "--{$boundary}\r\n{$part}";
		});

		// add final boundary
		$body[] = "--{$boundary}--";
		$body[] = "";


		return $this->setEncPost(implode("\r\n", $body))
			->setHeader("Expect", "")
			->setHeader("Content-Type", "multipart/form-data; boundary={$boundary}");
	}
}
