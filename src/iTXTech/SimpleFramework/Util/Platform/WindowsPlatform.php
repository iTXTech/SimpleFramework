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

use FFI;
use FFI\CData;

class WindowsPlatform extends Platform{
	public const HKEY_CLASSES_ROOT = 0x80000000;
	public const HKEY_CURRENT_USER = 0x80000001;
	public const HKEY_LOCAL_MACHINE = 0x80000002;
	public const HKEY_USERS = 0x80000003;
	public const HKEY_CURRENT_CONFIG = 0x80000005;

	private static FFI $user; // User32.dll
	private static FFI $kernel; // Kernel32.dll
	private static FFI $wininet; // WinInet.dll
	private static FFI $advapi; // Advapi32.dll
	private static FFI $shell; // Shell32.dll

	public static function init(){
		self::checkExtension();
		self::$user = FFI::cdef(<<<EOL
int SystemParametersInfoA(int uAction, int uParam, void* lpvParam, int fuWinIni);
int MessageBoxA(void* hWnd, char* lpText, char* lpCaption, unsigned int uType);
EOL, "User32.dll");
		self::$kernel = FFI::cdef("int GetLastError();", "Kernel32.dll");
		self::$wininet = FFI::cdef(<<<EOL
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
		self::$advapi = FFI::cdef(<<<EOL
typedef unsigned long DWORD;
DWORD RegOpenKeyExA(DWORD hKey, const char* lpSubKey, DWORD ulOptions, DWORD samDesired, DWORD* phkResult);
DWORD RegCloseKey(DWORD hKey);
DWORD RegFlushKey(DWORD hKey);
DWORD RegGetValueA(DWORD hkey, const char* lpSubKey, const char* lpValue, DWORD dwFlags, 
DWORD* pdwType, void* pvData, DWORD* pcbData);
EOL, "Advapi32.dll");
		self::$shell = FFI::cdef(<<<EOL
typedef unsigned long DWORD;
typedef struct {
  DWORD     cbSize;
  DWORD     fMask;
  void*      hwnd;
  const char*    lpVerb;
  const char*    lpFile;
  const char*    lpParameters;
  const char*    lpDirectory;
  int       nShow;
  void* hInstApp;
  void* lpIDList;
  const char*    lpClass;
  void*      hkeyClass;
  DWORD     dwHotKey;
  union {
    void* hIcon;
    void* hMonitor;
  } DUMMYUNIONNAME;
  void*    hProcess;
} SHELLEXECUTEINFOA, *LPSHELLEXECUTEINFOA;
int ShellExecuteExA(SHELLEXECUTEINFOA *pExecInfo);
EOL, "Shell32.dll");
	}

	//User32 functions starts
	public static function messageBox(string $text, string $caption, int $type = 0) : int{
		return self::$user->MessageBoxA(null, $text, $caption, $type);
	}

	/**
	 * @param int $action
	 * @param int $uiParam
	 * @param string|CData $param
	 * @param int $ini
	 *
	 * @return int
	 * @link https://docs.microsoft.com/en-us/windows/win32/api/winuser/nf-winuser-systemparametersinfoa
	 *
	 */
	public static function systemParametersInfo(int $action, int $uiParam, $param, int $ini) : int{
		return self::$user->SystemParametersInfoA($action, $uiParam, $param, $ini);
	}
	// User32 functions ends

	// Kernel32 functions starts
	public static function getLastError() : int{
		return self::$kernel->GetLastError();
	}
	// Kernel32 functions ends

	// WinInet functions starts
	public static function getSystemProxyOptions() : array{
		$list = self::$wininet->new("INTERNET_PER_CONN_OPTION_LISTA");
		$list->dwSize = FFI::sizeof($list);
		$list->pszConnection = null;
		$list->dwOptionCount = 3;
		$opt = self::$wininet->new("INTERNET_PER_CONN_OPTIONA[3]");
		$opt[0]->dwOption = 1;
		$opt[1]->dwOption = 2;
		$opt[2]->dwOption = 3;
		$list->pOptions = self::$wininet->cast(FFI::typeof($list->pOptions), $opt);

		$int = self::$wininet->new("DWORD");
		$int->cdata = $list->dwSize;

		self::$wininet->InternetQueryOptionA(null, 75, FFI::addr($list), FFI::addr($int));

		return [$opt[0]->Value->dwValue,
			FFI::string($opt[1]->Value->pszValue),
			FFI::string($opt[2]->Value->pszValue)];
	}

	public static function setSystemProxyOptions(bool $direct, ?string $proxy = null, string $bypass = ""){
		$list = self::$wininet->new("INTERNET_PER_CONN_OPTION_LISTA");
		$list->dwSize = FFI::sizeof($list);
		$list->pszConnection = null;
		if($direct){
			$list->dwOptionCount = 1;
			$opt = self::$wininet->new("INTERNET_PER_CONN_OPTIONA[1]");
			$opt[0]->dwOption = 1;
			$opt[0]->Value->dwValue = 0x01;
		}else{
			$list->dwOptionCount = 2;
			$opt = self::$wininet->new("INTERNET_PER_CONN_OPTIONA[3]");
			$opt[0]->dwOption = 1;
			$opt[0]->Value->dwValue = 0x02;
			$opt[1]->dwOption = 2;
			$opt[1]->Value->pszValue = self::newStr($proxy);
			$opt[2]->dwOption = 3;
			$opt[2]->Value->pszValue = self::newStr($bypass);
		}
		$list->pOptions = self::$wininet->cast(FFI::typeof($list->pOptions), $opt);
		self::$wininet->InternetSetOptionA(null, 75, FFI::addr($list), $list->dwSize);
		self::$wininet->InternetSetOptionA(null, 95, null, 0);//refresh
	}
	// WinInet functions ends

	// Shell functions begins
	public static function shellExecute(string $file, array $params, string $verb = "runas", int $show = 1) : int{
		$proc = FFI::new("int");
		$info = self::$shell->new("SHELLEXECUTEINFOA");
		$info->cbSize = FFI::sizeof($info);
		$info->fMask = 0;
		$info->hwnd = null;
		$info->lpVerb = self::newStr($verb);
		$info->lpFile = self::newStr($file);
		$info->lpParameters = self::newStr(implode(" ", $params));
		$info->lpDirectory = null;
		$info->nShow = $show; //SW_SHOWNORMAL
		$info->hInstApp = null;
		$info->lpIDList = null;
		$info->lpClass = null;
		$info->hkeyClass = null;
		$info->dwHotKey = null;
		$info->DUMMYUNIONNAME->hIcon = null;
		$info->DUMMYUNIONNAME->hMonitor = null;
		$info->hProcess = FFI::addr($proc);
		return self::$shell->ShellExecuteExA(FFI::addr($info));
	}
	// Shell functions ends

	// Advapi functions begins

	/**
	 * @param int $key HKey to open
	 * @param string $sub SubKey to open
	 * @param int $perm Access Rights
	 *
	 * @return int|CData
	 * @link https://docs.microsoft.com/en-us/windows/win32/sysinfo/registry-key-security-and-access-rights
	 *
	 * @link https://docs.microsoft.com/en-us/windows/win32/api/winreg/nf-winreg-regopenkeyexa
	 */
	public static function regOpenKey(int $key, string $sub, int $perm = 0x02){
		$handler = FFI::new("uint32_t");
		$ptr = FFI::addr($handler);
		$r = self::$advapi->RegOpenKeyExA($key, $sub, 0, $perm, $ptr);
		if($r === 0){
			return $handler;
		}
		return $r;
	}

	public static function regCloseKey($key) : int{
		if($key instanceof CData){
			$key = $key->cdata;
		}
		return self::$advapi->RegCloseKey($key);
	}

	public static function regFlushKey($key) : int{
		if($key instanceof CData){
			$key = $key->cdata;
		}
		return self::$advapi->RegFlushKey($key);
	}

	// Advapi functions ends
}
