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

if(extension_loaded("pthreads")){
	class ClassLoader extends \Threaded{

		/** @var string[] */
		private $lookup;
		/** @var string[] */
		private $classes;

		public function __construct(){
			$this->lookup = new \Threaded;
			$this->classes = new \Threaded;
		}

		/**
		 * Adds a path to the lookup list
		 *
		 * @param string $path
		 * @param bool   $prepend
		 */
		public function addPath($path, $prepend = false){

			foreach($this->lookup as $p){
				if($p === $path){
					return;
				}
			}

			if($prepend){
				$this->synchronized(function ($path){
					$entries = $this->getAndRemoveLookupEntries();
					$this->lookup[] = $path;
					foreach($entries as $entry){
						$this->lookup[] = $entry;
					}
				}, $path);
			}else{
				$this->lookup[] = $path;
			}
		}

		protected function getAndRemoveLookupEntries(){
			$entries = [];
			while($this->count() > 0){
				$entries[] = $this->shift();
			}
			return $entries;
		}

		/**
		 * Removes a path from the lookup list
		 *
		 * @param $path
		 */
		public function removePath($path){
			foreach($this->lookup as $i => $p){
				if($p === $path){
					unset($this->lookup[$i]);
				}
			}
		}

		/**
		 * Returns an array of the classes loaded
		 *
		 * @return string[]
		 */
		public function getClasses(){
			$classes = [];
			foreach($this->classes as $class){
				$classes[] = $class;
			}
			return $classes;
		}

		public function register($prepend = false){
			spl_autoload_register([$this, "loadClass"], true, $prepend);
		}

		/**
		 * Called when there is a class to load
		 *
		 * @param string $name
		 *
		 * @return bool
		 */
		public function loadClass($name){
			$path = $this->findClass($name);
			if($path !== null){
				include($path);
				if(!class_exists($name, false) and !interface_exists($name, false) and !trait_exists($name, false)){
					return false;
				}

				if(method_exists($name, "onClassLoaded") and (new ReflectionClass($name))->getMethod("onClassLoaded")->isStatic()){
					$name::onClassLoaded();
				}

				$this->classes[] = $name;

				return true;
			}

			return false;
		}

		/**
		 * Returns the path for the class, if any
		 *
		 * @param string $name
		 *
		 * @return string|null
		 */
		public function findClass($name){
			$components = explode("\\", $name);

			$baseName = implode(DIRECTORY_SEPARATOR, $components);


			foreach($this->lookup as $path){
				if(file_exists($path . DIRECTORY_SEPARATOR . $baseName . ".php")){
					return $path . DIRECTORY_SEPARATOR . $baseName . ".php";
				}
			}

			return null;
		}
	}
}else{
	class ClassLoader{

		/** @var string[] */
		private $lookup;
		/** @var string[] */
		private $classes;

		public function __construct(){
			$this->lookup = [];
			$this->classes = [];
		}

		/**
		 * Adds a path to the lookup list
		 *
		 * @param string $path
		 * @param bool   $prepend
		 */
		public function addPath($path, $prepend = false){

			foreach($this->lookup as $p){
				if($p === $path){
					return;
				}
			}

			if($prepend){
			}else{
				$this->lookup[] = $path;
			}
		}

		/**
		 * Removes a path from the lookup list
		 *
		 * @param $path
		 */
		public function removePath($path){
			foreach($this->lookup as $i => $p){
				if($p === $path){
					unset($this->lookup[$i]);
				}
			}
		}

		/**
		 * Returns an array of the classes loaded
		 *
		 * @return string[]
		 */
		public function getClasses(){
			$classes = [];
			foreach($this->classes as $class){
				$classes[] = $class;
			}
			return $classes;
		}

		public function register($prepend = false){
			spl_autoload_register([$this, "loadClass"], true, $prepend);
		}

		/**
		 * Called when there is a class to load
		 *
		 * @param string $name
		 *
		 * @return bool
		 */
		public function loadClass($name){
			$path = $this->findClass($name);
			if($path !== null){
				include($path);
				if(!class_exists($name, false) and !interface_exists($name, false) and !trait_exists($name, false)){
					return false;
				}

				if(method_exists($name, "onClassLoaded") and (new ReflectionClass($name))->getMethod("onClassLoaded")->isStatic()){
					$name::onClassLoaded();
				}

				$this->classes[] = $name;

				return true;
			}

			return false;
		}

		/**
		 * Returns the path for the class, if any
		 *
		 * @param string $name
		 *
		 * @return string|null
		 */
		public function findClass($name){
			$components = explode("\\", $name);

			$baseName = implode(DIRECTORY_SEPARATOR, $components);


			foreach($this->lookup as $path){
				if(file_exists($path . DIRECTORY_SEPARATOR . $baseName . ".php")){
					return $path . DIRECTORY_SEPARATOR . $baseName . ".php";
				}
			}

			return null;
		}
	}
}
