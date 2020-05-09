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

	public const RRF_RT_REG_NONE = 0x00000001;
	public const RRF_RT_REG_SZ = 0x00000002;
	public const RRF_RT_REG_EXPAND_SZ = 0x00000004;
	public const RRF_RT_REG_BINARY = 0x00000008;
	public const RRF_RT_REG_DWORD = 0x00000010;
	public const RRF_RT_REG_MULTI_SZ = 0x00000020;
	public const RRF_RT_REG_QWORD = 0x00000040;
	public const RRF_RT_DWORD = self::RRF_RT_REG_BINARY | self::RRF_RT_REG_DWORD;
	public const RRF_RT_QWORD = self::RRF_RT_REG_BINARY | self::RRF_RT_REG_QWORD;
	public const RRF_RT_ANY = 0x0000ffff;

	public const REG_NONE = 0;
	public const REG_SZ = 1;
	public const REG_EXPAND_SZ = 2;
	public const REG_BINARY = 3;
	public const REG_DWORD = 4;
	public const REG_DWORD_BIG_ENDIAN = 5;
	public const REG_LINK = 6;
	public const REG_MULTI_SZ = 7;
	public const REG_QWORD = 11;
	// Waiting for someone who is interested in this stuff
	public const REG_RESOURCE_LIST = 8;
	public const REG_FULL_RESOURCE_DESCRIPTOR = 9;
	public const REG_RESOURCE_REQUIREMENTS_LIST = 10;

	public static FFI $user; // User32.dll
	public static FFI $kernel; // Kernel32.dll
	public static FFI $wininet; // WinInet.dll
	public static FFI $advapi; // Advapi32.dll
	public static FFI $shell; // Shell32.dll

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
typedef struct _SECURITY_ATTRIBUTES {
  DWORD  nLength;
  void* lpSecurityDescriptor;
  int   bInheritHandle;
} SECURITY_ATTRIBUTES, *PSECURITY_ATTRIBUTES, *LPSECURITY_ATTRIBUTES;
DWORD RegOpenKeyExA(DWORD hKey, char* lpSubKey, DWORD ulOptions, DWORD samDesired, DWORD* phkResult);
DWORD RegCloseKey(DWORD hKey);
DWORD RegFlushKey(DWORD hKey);
DWORD RegGetValueA(DWORD hkey, char* lpSubKey, char* lpValue, DWORD dwFlags, 
DWORD* pdwType, void* pvData, DWORD* pcbData);
DWORD RegCreateKeyExA(DWORD hKey, char* lpSubKey, DWORD Reserved, char* lpClass, DWORD dwOptions, 
DWORD samDesired, const LPSECURITY_ATTRIBUTES lpSecurityAttributes, DWORD* phkResult, DWORD* lpdwDisposition);
DWORD RegDeleteKeyExA(DWORD hKey, char* lpSubKey, DWORD samDesired, DWORD Reserved);
DWORD RegSetValueExA(DWORD hKey, char* lpValueName, DWORD Reserved, DWORD dwType, void* lpData, DWORD cbData);
DWORD RegDeleteValueA(DWORD hKey, char* lpValueName);
EOL, "Advapi32.dll");
		self::$shell = FFI::cdef(<<<EOL
typedef unsigned long DWORD;
typedef struct {
  DWORD     cbSize;
  DWORD     fMask;
  void*      hwnd;
  char*    lpVerb;
  char*    lpFile;
  char*    lpParameters;
  char*    lpDirectory;
  int       nShow;
  void* hInstApp;
  void* lpIDList;
  char*    lpClass;
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
	/**
	 * @return array [0] => Proxy Enabled, [1] => Proxy Address, [2] => Proxy Bypass
	 */
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
		$r = self::$advapi->RegOpenKeyExA($key, $sub, 0, $perm, FFI::addr($handler));
		if($r === 0){
			return $handler;
		}
		return $r;
	}

	/**
	 * @param int|CData $key
	 * @param string $sub
	 * @param int $option
	 * @param int $perm
	 *
	 * @return CData[]|int Failed => int, Succ => CData[0] => [HKEY, Disposition]
	 * @link https://docs.microsoft.com/en-us/windows/win32/api/winreg/nf-winreg-regcreatekeyexa
	 */
	public static function regCreateKey($key, string $sub, int $option = 0, int $perm = 0x02){
		if($key instanceof CData){
			$key = $key->cdata;
		}
		$handler = FFI::new("uint32_t");
		$op = FFI::new("uint32_t");
		$r = self::$advapi->RegCreateKeyExA($key, $sub, 0, null, $option, $perm, null, FFI::addr($handler),
			FFI::addr($op));
		if($r === 0){
			return [$handler, $op];
		}
		return $r;
	}

	public static function regDeleteKey($key, string $sub, int $sam = 0x0100){
		if($key instanceof CData){
			$key = $key->cdata;
		}
		return self::$advapi->RegDeleteKeyExA($key, $sub, $sam, 0);
	}

	/**
	 * @param int|CData $key
	 * @param string $value
	 * @param int $type
	 * @param int|array|string $data
	 *
	 * @return int
	 * @link https://docs.microsoft.com/en-us/windows/win32/api/winreg/nf-winreg-regsetvalueexa
	 */
	public static function regSetValue($key, string $value, int $type, $data = 0) : int{
		switch($type){
			case self::REG_SZ:
			case self::REG_EXPAND_SZ:
			case self::REG_LINK:
				$buffer = self::newStr($data);
				$len = strlen($data) + 1;
				break;
			case self::REG_MULTI_SZ:
				$buffer = self::newStr($data, 2);
				$len = strlen($data) + 2;
				break;
			case self::REG_QWORD:
				$buffer = self::$advapi->new("uint64_t");
				$buffer->cdata = $data;
				$len = 8;
				break;
			case self::REG_BINARY:
				$buffer = self::newArr("unsigned char", $data);
				$len = count($data);
				break;
			case self::REG_DWORD:
			case self::REG_DWORD_BIG_ENDIAN:
			default:
				$buffer = self::$advapi->new("DWORD");
				$buffer->cdata = $data;
				$len = 4;
		}
		if($key instanceof CData){
			$key = $key->cdata;
		}
		return self::$advapi->RegSetValueExA($key, $value, 0, $type, FFI::addr($buffer), $len);
	}

	public static function regDeleteValue($key, string $value){
		if($key instanceof CData){
			$key = $key->cdata;
		}
		return self::$advapi->RegDeleteValueA($key, $value);
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

	/**
	 * @param int $key
	 * @param string $sub
	 * @param string $name
	 * @param int $flags
	 * @param int|null $type
	 * @param int|null $length
	 *
	 * @return array|bool|string|int REG_BINARY => ByteArray, REG_SZ => string, Others => int, Failed => false
	 * @link https://docs.microsoft.com/en-us/windows/win32/api/winreg/nf-winreg-reggetvaluea
	 *
	 */
	public static function regGetValue(int $key, string $sub, string $name, int $flags = self::RRF_RT_ANY,
	                                   ?int &$type = null, ?int &$length = null){
		$t = self::$advapi->new("DWORD");
		$len = self::$advapi->new("DWORD");
		if($type == null or $length == null){
			self::$advapi->RegGetValueA($key, $sub, $name, $flags, FFI::addr($t), null, FFI::addr($len));
			$type = $t->cdata;
			$length = $len->cdata;
		}
		$str = false;
		$arr = false;
		switch($type){
			case self::REG_SZ:
			case self::REG_EXPAND_SZ:
			case self::REG_LINK:
			case self::REG_MULTI_SZ:
				$str = true;
				$buffer = self::$advapi->new("char[$length]");
				break;
			case self::REG_QWORD:
				$buffer = self::$advapi->new("uint64_t");
				break;
			case self::REG_BINARY:
				$arr = true;
				$buffer = self::$advapi->new("unsigned char[$length]");
				break;
			case self::REG_DWORD:
			case self::REG_DWORD_BIG_ENDIAN:
			default:
				$buffer = self::$advapi->new("DWORD");
		}
		$result = self::$advapi->RegGetValueA($key, $sub, $name, $flags, null, FFI::addr($buffer), FFI::addr($len));
		if($result !== 0){
			return false;
		}elseif($str){
			return FFI::string($buffer);
		}elseif($arr){
			$a = [];
			foreach($buffer as $v){
				$a[] = $v;
			}
			return $a;
		}
		return $buffer->cdata;
	}

	// Advapi functions ends
}
