<?php

/**
 * SimpleFramework
 * The fast, light-weighted, easy-to-extend php framework.
 *
 * Some classes are based on project PocketMine-MP.
 * List: ConsoleReader, Terminal, TextFormat, Logger, Util, Config, ClassLoader
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PeratX
 */

namespace sf\module;

use sf\SimpleFramework;

//Multi-thread is recommended for plugin design.
interface Module{
	public function __construct(SimpleFramework $framework);
    
    public function load();
    
    public function unload();
    
    public function isLoaded() : bool;
    
    public function doTick(int $currentTick);
    
    //No space!
    public function getName() : string;
    
    public function getVersion() : string;
    
    public function getDescription() : string;
}