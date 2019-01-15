<?php
namespace Application\Filter;

/**
 * Generic class which contains messages
 */
class Messages
{
	const MESSAGE_UNKNOWN = 'Unknown';
	public static $messages;
	public static function setMessages(array $messages)
	{
		self::$messages = $messages;
	}
	public static function setMessage($key, $message)
	{
		self::$messages[$key] = $message;
	}
	public static function getMessage($key)
	{
		return self::$messages[$key] ?? self::MESSAGE_UNKNOWN;
	}
}
