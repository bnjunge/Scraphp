<?php

namespace Scraphp;

/*
 * Licensed under The GNU GENERAL PUBLIC LICENSE V3
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Terry Lin <terrylin.developer@gmail.com>
 * @version 1.0 ($Rev: 1 $)
 */

define('SCRAPHP_DIR', __DIR__);

class ScraphpCore
{
	public $is_proxy  = false; // Set "true" if you would like to use proxy ips.
	public $is_cookie = false; // Set "true" if you would like to receive and send Cookie.

	// set "200" to receive complete pages only. / set 0 = all pages, even page not found.
	public $accept_status_code = 200;

    // set cookie string customly, fake maybe.
    // notice that if this value is set, cookie file will not be used.
	public $cookie_string;

	// define file path
	public $file_path_cookie = '';
	public $file_dir_logs = '';

	// primary CURLOPT settings
	public $proxy_ip;
	public $proxy_type = 'http'; // http, sock4, sock5
	public $proxy_port;
	public $proxy_username;
	public $proxy_password;

	// default CURLOPT settings
	// http://php.net/manual/en/curl.constants.php
	// for advanced setting please use curl_setopt()
	// http://php.net/manual/en/function.curl-setopt.php
	public $curlopt = array();

	// http header information
	protected $header  = array();

	// example: user_agent, http_referrer
	protected $browser = array();

	// Command Line Interface
	public static $is_cli      = true;
	public static $debug_level = 'notice'; // error, notice, warning
	public static $is_log      = false; // Set "true" to enable file logging.

	// For debug message
	public $project_name   = 'Scraphp project';
	public $project_domain = '';
	public $project_url    = '';
	public $target_url     = '';

	/**
	 * ScraphpCore constructor.
	 * 
	 * @param array $config
     */
	public function __construct($config = array()) {
		$this->debug_message('notice', 'ScraphpCore is loaded.');
	}

	/**
	 * @param bool $bool
     */
	public function set_cli($bool) {
		self::$is_cli = $bool;
	}

	/**
	 * @param string $string | notice, warning, error
     */
	public function set_debug_level($string) {
		self::$debug_level = $string;
	}

	/**
	 * @param bool $bool
     */
	public function set_log($bool) {
		self::$is_log = $bool;
	}

	/**
	 * @param string $level
	 * @param string $message
     */
	public function debug_message($level = 'notice', $message) {
		$is_show = false;
		$is_log_to_file = false;
		$log_message = '';

		$date = date("Y-m-d H:i:s");

		if ((self::$debug_level == 'error' and $level == 'error') or (self::$debug_level == 'warning' and $level != 'notice') or (self::$debug_level == 'notice')) {
			$is_show = true;
			$is_log_to_file = true;
		}

		if (empty($this->target_url)) {
			$target_text = '';
		} else {
			$target_text = "at {$this->target_url}";
		}

		if ($is_show) {
			$log_message = "[{$this->project_name}] [{$date}]\n{$level}: {$message} {$target_text}\n";
			if (self::$is_cli) {
				echo $log_message;
			} else {
				// Display output in browser
				if (!headers_sent()) {
					header('Content-type: text/html; charset=utf-8');
				}
				if ($level == 'error') {
					$level_text = '<strong style="color:#a40000">' . $level . '</strong>';
				} else if ($level == 'warning') {
					$level_text = '<strong style="color:#eb6100">' . $level . '</strong>';
				} else {
					$level_text = '<strong style="color:#448aca">' . $level . '</strong>';
				}
				echo "[<strong>{$this->project_name}</strong>] [{$date}] {$level_text}: {$message} {$target_text}<br />";
				ob_flush();
				flush();
			}
		}
		if (self::$is_log and $is_log_to_file) {
			if (empty($this->file_dir_logs)) {
				$this->file_dir_logs = SCRAPHP_DIR . '/logs';
			}
			date_default_timezone_set('GMT');
			$date = date('Y-m-d');

			$filepath = "{$this->file_dir_logs}/{$this->project_name}-{$date}.txt";
			// Opening file with pointer at the end of the file
			$handle = fopen($filepath, "a+");  
			 // Writing it in the log file
			fwrite($handle, $log_message . "\n\n");
			fclose($handle);
		}
	}
}
