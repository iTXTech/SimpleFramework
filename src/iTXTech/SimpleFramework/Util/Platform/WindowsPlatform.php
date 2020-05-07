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

namespace iTXTech\SimpleFramework\Util\Platform;

class WindowsPlatform extends Platform{
	/** @var \FFI */
	private static $user;
	/** @var \FFI */
	private static $krnl;
	/** @var \FFI */
	private static $inet;

	public static function init(){
		self::$user = \FFI::cdef(<<<EOL
int SystemParametersInfoA(int uAction, int uParam, char* lpvParam, int fuWinIni);
int MessageBoxA(void* hWnd, char* lpText, char* lpCaption, unsigned int uType);
EOL
			, "User32.dll");
		self::$krnl = \FFI::cdef("int GetLastError();", "Kernel32.dll");
		self::$inet = \FFI::cdef(<<<EOL
typedef unsigned long DWORD;
typedef int BOOL;
typedef DWORD *LPDWORD;
typedef void *LPVOID;
typedef LPVOID HINTERNET;
typedef char* *LPSTR;
typedef struct {
  DWORD dwLowDateTime;
  DWORD dwHighDateTime;
} FILETIME, *PFILETIME, *LPFILETIME;
typedef struct {
  DWORD dwOption;
  union {
    DWORD    dwValue;
    LPSTR    pszValue;
    FILETIME ftValue;
  } Value;
} INTERNET_PER_CONN_OPTIONA, *LPINTERNET_PER_CONN_OPTIONA;
typedef struct {
  DWORD                       dwSize;
  LPSTR                       pszConnection;
  DWORD                       dwOptionCount;
  DWORD                       dwOptionError;
  LPINTERNET_PER_CONN_OPTIONA pOptions;
} INTERNET_PER_CONN_OPTION_LISTA, *LPINTERNET_PER_CONN_OPTION_LISTA;
typedef DWORD WINAPI_InternetOption;
BOOL InternetSetOptionA(HINTERNET hInternet, WINAPI_InternetOption dwOption, LPVOID lpBuffer, DWORD dwBufferLength);
BOOL InternetQueryOptionA(HINTERNET hInternet, WINAPI_InternetOption dwOption, LPVOID lpBuffer, LPDWORD lpdwBufferLength);
EOL
			, "Wininet.dll");
	}

	//User32 functions starts
	public static function messageBox(string $text, string $caption, int $type = 0) : int{
		self::checkExtension();
		return self::$user->MessageBoxA(null, $text, $caption, $type);
	}

	public static function setSystemParametersInfo(int $act, int $par, string $p, int $ini) : int{
		self::checkExtension();
		return self::$user->SystemParametersInfoA($act, $par, $p, $ini);
	}
	// User32 functions ends

	// Kernel32 functions starts
	public static function getLastError() : int{
		self::checkExtension();
		return self::$krnl->GetLastError();
	}
	// Kernel32 functions ends

	// WinInet functions, not working for now
	public static function getSystemProxyOptions() : \FFI\CData{
		self::checkExtension();
		$list = self::$inet->new("INTERNET_PER_CONN_OPTION_LISTA");
		$opt = self::$inet->new("INTERNET_PER_CONN_OPTIONA");
		$str = \FFI::new("char*", "");
		$opt->dwOption = 1;
		$opt->Value->pszValue = \FFI::addr($str);
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

		var_dump($list);
		return $list;
	}

	public static function setSystemProxyOptions(bool $direct, ?string $proxy = null, string $bypass = ""){
		self::checkExtension();
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
			$opt = self::$inet->new("INTERNET_PER_CONN_OPTIONA[" . ($bypass ? 3 : 2) . "]");
			$opt[0]->dwOption = 1;
			$opt[0]->Value->dwValue = 0x02;
			$opt[1]->dwOption = 2;
			$opt[1]->Value->pszValue = \FFI::addr(\FFI::new("char*", $proxy));
			if($bypass){
				$opt[2]->dwOption = 3;
				$opt[2]->Value->pszValue = \FFI::new("char**", $bypass);
			}
			$list->pOptions = self::$inet->cast(\FFI::typeof($list->pOptions), $opt);
		}
		self::$inet->InternetSetOptionA(null, 75, \FFI::addr($list), $size);
		self::$inet->InternetSetOptionA(null, 95, null, 0);//refresh
	}
}
