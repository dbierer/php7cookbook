<?php
namespace Application\I18n;

use DateTime;
use DatePeriod;
use DateInterval;
use InvalidArgumentException;

class Event
{
	
	const INTERVAL_DAY = 'P%dD';
	const INTERVAL_WEEK = 'P%dW';
	const INTERVAL_MONTH = 'P%dM';

	const FLAG_FIRST = 'FIRST';		// 1st of the month

	const ERROR_INVALID_END  = 'Need to supply either # occurrences or an end date';
	const ERROR_INVALID_DATE = 'Date must be a string i.e. YYYY-mm-dd or DateTime instance';
	const ERROR_INVALID_INTERVAL = 'Interval must take the form "P\d+(D | W | M)"';

	// key properties
	public $id;
	public $flag;
	public $value;
	public $title;
	public $locale;
	public $interval;
	public $description;
	public $occurrences;

	// dates
	public $nextDate;
	protected $endDate;
	protected $startDate;
	
	/**
	 * Builds Event instance
	 * 
	 * @param string $title = title of the event
	 * @param string $description = description of the event
	 * @param string | DateTime $startDate = when does this event start
	 * @param string $interval = INTERVAL_DAY | INTERVAL_WEEK | etc.
	 * @param int $value = # days, weeks, months in this interval
	 * @param int $occurrences = how many occurrences of the event
	 * @param string | DateTime $endDate = when does this event end
	 * @param string $flag = FLAG_FIRST or FLAG_LAST
	 */
	public function __construct($title, 
								$description, 
								$startDate, 
								$interval, 
								$value, 
								$occurrences = NULL, 
								$endDate = NULL, 
								$flag = NULL)
	{
		// init vars
		$this->id = md5($title . $interval . $value) . sprintf('%04d', rand(0,9999));
		$this->flag = $flag;
		$this->value = $value;
		$this->title = $title;
		$this->description = $description;
		$this->occurrences = $occurrences;
		// build interval
		try {
			$this->interval = new DateInterval(sprintf($interval, $value));
		} catch (Exception $e) {
			error_log($e->getMessage());
			throw new InvalidArgumentException(self::ERROR_INVALID_INTERVAL);
		}
		// store / calc dates
		$this->startDate = $this->stringOrDate($startDate);
		if ($endDate) {
			$this->endDate = $this->stringOrDate($endDate);
		} elseif ($occurrences) {
			$this->endDate = $this->calcEndDateFromOccurrences();
		} else {
			throw new InvalidArgumentException(self::ERROR_INVALID_END);
		}
		$this->nextDate = $this->startDate;
	}

	/**
	 * Determines if input is string or DateTime
	 * Returns right away if $date === NULL
	 * 
	 * @param mixed DateTime | string $date | NULL
	 * @return DateTime $dateObj | NULL
	 * @throws InvalidArgumentException
	 */
	protected function stringOrDate($date)
	{
		if ($date === NULL) { 
			$newDate = NULL;
		} elseif ($date instanceof DateTime) {
			$newDate = $date;
		} elseif (is_string($date)) {
			$newDate = new DateTime($date);
		} else {
			throw new InvalidArgumentException(self::ERROR_INVALID_END);
		}
		return $newDate;
	}

	/**
	 * This is used if $occurrences is set, so we'll know the end date
	 * 
	 * @return DateTime $endDate
	 */
	protected function calcEndDateFromOccurrences()
	{
		$endDate = new DateTime('now');
		$period = new DatePeriod($this->startDate, $this->interval, $this->occurrences);
		foreach ($period as $date) {
			$endDate = $date;
		}		
		return $endDate;
	}
	
	public function __toString()
	{
		return $this->title;
	}

	/**
	 * Calculates next date for this event
	 * 
	 * @param DateTime $today
	 * @return boolean FALSE if past end date || DateTime $nextDate
	 */
	public function getNextDate(DateTime $today)
	{
		// bail out of no more occurrences or past end date
		if ($today > $this->endDate) {
			return FALSE;
		}
		$next = clone $today;
		$next->add($this->interval);
		return $next;
	}

}
