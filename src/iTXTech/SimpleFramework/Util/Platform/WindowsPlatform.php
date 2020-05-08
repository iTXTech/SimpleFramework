<?php

/** @noinspection PhpUndefinedMethodInspection */

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

namespace iTXTech\SimpleFramework\Util\Platform;

class WindowsPlatform extends Platform{
	private static \FFI $user;
	private static \FFI $krnl;
	private static \FFI $inet;

	public static function init(){
		self::checkExtension();
		self::$user = \FFI::cdef(<<<EOL
int SystemParametersInfoA(int uAction, int uParam, char* lpvParam, int fuWinIni);
int MessageBoxA(void* hWnd, char* lpText, char* lpCaption, unsigned int uType);
EOL, "User32.dll");
		self::$krnl = \FFI::cdef("int GetLastError();", "Kernel32.dll");
		self::$inet = \FFI::cdef(<<<EOL
typedef unsigned long DWORD;
typedef int BOOL;
typedef struct {
  DWORD dwLowDateTime;
  DWORD dwHighDateTime;
} FILETIME, *PFILETIME, *LPFILETIME;
typedef struct {
  DWORD dwOption;
  union {
    DWORD    dwValue;
    char*    pszValue;
    FILETIME ftValue;
  } Value;
} INTERNET_PER_CONN_OPTIONA, *LPINTERNET_PER_CONN_OPTIONA;
typedef struct {
  DWORD                       dwSize;
  char*                       pszConnection;
  DWORD                       dwOptionCount;
  DWORD                       dwOptionError;
  LPINTERNET_PER_CONN_OPTIONA pOptions;
} INTERNET_PER_CONN_OPTION_LISTA, *LPINTERNET_PER_CONN_OPTION_LISTA;
BOOL InternetSetOptionA(void* hInternet, DWORD dwOption, void* lpBuffer, DWORD dwBufferLength);
BOOL InternetQueryOptionA(void* hInternet, DWORD dwOption, void* lpBuffer, DWORD* lpdwBufferLength);
EOL, "Wininet.dll");
	}

	//User32 functions starts
	public static function messageBox(string $text, string $caption, int $type = 0) : int{
		return self::$user->MessageBoxA(null, $text, $caption, $type);
	}

	public static function setSystemParametersInfo(int $act, int $par, string $p, int $ini) : int{
		return self::$user->SystemParametersInfoA($act, $par, $p, $ini);
	}
	// User32 functions ends

	// Kernel32 functions starts
	public static function getLastError() : int{
		return self::$krnl->GetLastError();
	}
	// Kernel32 functions ends

	// WinInet functions starts
	public static function getSystemProxyOptions(){
		$list = self::$inet->new("INTERNET_PER_CONN_OPTION_LISTA");
		$opt = self::$inet->new("INTERNET_PER_CONN_OPTIONA");
		$opt->dwOption = 1;
		$pointer = self::newStr("");
		$opt->Value->pszValue = \FFI::addr($pointer);
		$list->dwSize = \FFI::sizeof($list);
		$list->pszConnection = null;
		$list->dwOptionCount = 1;
		$list->dwOptionError = 0;
		$list->pOptions = \FFI::addr($opt);
		$listptr = \FFI::addr($list);

		$int = self::$inet->new("DWORD");
		$int->cdata = \FFI::sizeof($list) * 2;
		$ptr = \FFI::addr($int);

		self::$inet->InternetQueryOptionA(null, 75, $listptr, $ptr);
		// TODO: see https://bugs.php.net/bug.php?id=79571 , bug related to union
		\FFI::free($list->pOptions);
	}

	public static function setSystemProxyOptions(bool $direct, ?string $proxy = null, string $bypass = ""){
		$list = self::$inet->new("INTERNET_PER_CONN_OPTION_LISTA");
		$size = \FFI::sizeof($list);
		$list->dwSize = $size;
		$list->pszConnection = null;
		if($direct){
			$list->dwOptionCount = 1;
			$opt = self::$inet->new("INTERNET_PER_CONN_OPTIONA");
			$opt->dwOption = 1;
			$opt->Value->dwValue = 0x01;
			$list->pOptions = \FFI::addr($opt);
		}else{
			$list->dwOptionCount = 2;
			$opt = self::$inet->new("INTERNET_PER_CONN_OPTIONA[3]");
			$opt[0]->dwOption = 1;
			$opt[0]->Value->dwValue = 0x02;
			$opt[1]->dwOption = 2;
			$opt[1]->Value->pszValue = self::newStr($proxy);
			$opt[2]->dwOption = 3;
			$opt[2]->Value->pszValue = self::newStr($bypass);
			$list->pOptions = self::$inet->cast(\FFI::typeof($list->pOptions), $opt);
		}
		self::$inet->InternetSetOptionA(null, 75, \FFI::addr($list), $size);
		self::$inet->InternetSetOptionA(null, 95, null, 0);//refresh
		\FFI::free($list->pOptions);
	}
	// WinInet functions ends
}
