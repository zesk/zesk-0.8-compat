<?php
/**
 * $URL: https://code.marketacumen.com/zesk/trunk/classes-deprecated/log.php $
 * @package zesk
 * @subpackage default
 * @author Kent Davidson <kent@marketacumen.com>
 * @copyright Copyright &copy; 2014, Market Acumen, Inc.
 */

zesk()->deprecated();

/**
 * Logging tools
 *
 * @author kent
 */
class log {
	
	/*
	 * Signals and processes
	 * Level 0: Fatal
	 * Level 1: Error
	 * Level 2: Warning
	 * Level 3: Notice
	 * Level 4: Debug
	 * Debugging
	 *
	 */
	const FATAL = 0;
	const ERROR = 1;
	const WARNING = 2;
	const NOTICE = 3;
	const DEBUG = 4;
	const ALL = 1000000; // 1M Dollars
	const LOG_DEFAULT = self::ERROR;
	
	/**
	 * Cache of modules for logging
	 *
	 * @var array
	 */
	private static $modules = null;
	
	/**
	 * Global shutoff for logging
	 *
	 * @var boolean
	 */
	static $disabled = false;
	
	/**
	 * Global setting for time zone used for logging
	 *
	 * @var boolean
	 */
	static $utc_time = false;
	
	/**
	 * Log levels
	 *
	 * @var array
	 */
	private static $levels = array(
		self::FATAL => "FATAL",
		self::ERROR => "ERROR",
		self::WARNING => "WARNING",
		self::NOTICE => "NOTICE",
		self::DEBUG => "DEBUG"
	);
	
	/**
	 * Level index => string
	 *
	 * @return array
	 */
	public static function levels() {
		return self::$levels;
	}
	public static function level_string() {
		return avalue(self::$levels, $level = self::level(), "*bad-level-$level*");
	}
	public static function level($level = null) {
		if ($level !== null) {
			if (is_string($level)) {
				$level = avalue(array_flip(self::levels()), strtoupper($level), $level);
			}
			zesk::set(__CLASS__ . '::level', intval($level));
		}
		return zesk::geti(__CLASS__ . '::level', self::LOG_DEFAULT);
	}
	
	/**
	 * Register hooks
	 */
	public static function hooks(zesk\Kernel $zesk) {
		$zesk->hooks->add(zesk\Hooks::hook_configured, __CLASS__ . '::configured');
	}
	
	/**
	 * Configured hook
	 */
	public static function configured() {
		self::level(zesk::get(__CLASS__ . '::level', self::LOG_DEFAULT));
		$file = zesk::get(__CLASS__ . '::file');
		if ($file) {
			self::file($file);
		}
		self::$utc_time = zesk::getb(__CLASS__ . '::utc_time');
	}
	
	/**
	 * Send a log message.
	 *
	 * Note that this call adds default, internal arguments before passing to the hooks.
	 *
	 * The internal variables names all begin with an underscore to prevent collision with internal
	 * variables names.
	 *
	 * @param unknown $message        	
	 * @param array $args        	
	 * @param string $level        	
	 */
	public static function send($message, array $args = array(), $level = null) {
		app()->logger->log(avalue(array(self::ERROR => "error", self::WARNING => "warning", self::NOTICE, "notice"), $level), $message, $args);
	}
	public static function disable($set = null) {
		zesk()->deprecated();
		if (is_bool($set)) {
			self::$disabled = $set;
		}
		return self::$disabled;
	}
	public static function file($filename = false, $mode = "a") {
		global $zesk;
		
		app()->module->load("Logger_File");
		
		/* @var $zesk \zesk\Kernel */
		$zesk->deprecated();
		$zesk->logger->register_handler("Module_Log_File", new zesk\Logger\File($filename, array(
			"mode" => $mode
		)));
	}
	/**
	 * Log a fatal error.
	 * Halts execution.
	 *
	 * @param string $message        	
	 * @param array $args        	
	 */
	public static function fatal($message, array $args = array()) {
		global $zesk;
		self::send($message, $args, self::FATAL);
		exit($zesk->hooks->call('exit'));
	}
	
	/**
	 * Log an error.
	 * Something which shouldn't happen, ever.
	 *
	 * @param string $message        	
	 * @param array $args        	
	 */
	public static function error($message, array $args = array()) {
		self::send($message, $args, self::ERROR);
	}
	/**
	 * Log an warning.
	 * Something which happens on occasion, but won't affect operation of the system.
	 *
	 * @param string $message        	
	 * @param array $args        	
	 */
	public static function warning($message, array $args = array()) {
		self::send($message, $args, self::WARNING);
	}
	
	/**
	 * Log a notice.
	 * Nice to know (something changed, data updated, etc.) are operation is normal.
	 *
	 * @param string $message        	
	 * @param array $args        	
	 */
	public static function notice($message, array $args = array()) {
		self::send($message, $args, self::NOTICE);
	}
	
	/**
	 * Debugging calls.
	 * Anything which is really for developers to see what's going on, not for normal users.
	 *
	 * @param string $message        	
	 * @param array $args        	
	 */
	public static function debug($message, array $args = array()) {
		self::send($message, $args, self::DEBUG);
	}
}

