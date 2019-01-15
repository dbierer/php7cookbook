<?php
namespace Application\I18n;

use ArrayObject;
use IntlCalendar;
use Exception;

class Calendar
{
	const DAY_1 = 'EEEEE';		// T
	const DAY_2 = 'EEEEEE';		// Tu
	const DAY_3 = 'EEE';		// Tue
	const DAY_FULL = 'EEEE';	// Tuesday
	const MONTH_1 = 'MMMMM';	// M
	const MONTH_3 = 'MMM';		// Mar
	const MONTH_FULL = 'MMMM';	// March
	const DEFAULT_ACROSS = 3;
	const HEIGHT_FULL = '150px';
	const HEIGHT_SMALL = '60px';
	
	const ERROR_NOT_DAY = 'Is not instance of Application\I18n\Day';
	
	protected $locale;
	protected $dateFormatter;
	protected $yearArray;
	protected $events = array();
	protected $height;

	public function __construct(Locale $locale)
	{
		$this->locale = $locale;
	}

	public function addEvent(Event $event)
	{
		$this->events[$event->id] = $event;
	}
	
	public function getYearArray()
	{
		return $this->yearArray;
	}
	
	public function buildYearArray($year, $timeZone = NULL)
	{
		$this->yearArray = array();
		for ($month = 1; $month <= 12; $month++) {
			$this->yearArray[$year][$month] = $this->buildMonthArray($year, $month, $timeZone);
		}
		return $this->yearArray;
	}
	
	/**
	 * Builds an array for this month
	 * Return value will be a multi-dimensional array where each sub-array has 7 elements representing 1 week
	 * If the dates fall outside of the week, values will be set to NULL
	 * 
	 * @param int $year
	 * @param int $month
	 * @param Application\I18n\Locale $locale
	 * @param string $timeZone
	 * @return array $month == [week_of_year => [1 => day_of_month, 2 => day_of_month, etc.]]
	 */
	public function buildMonthArray($year, $month, $timeZone = NULL)
	{
		$month -= 1; 	// IntlCalendar months are 0 based; Jan == 0, Feb == 1, etc.
		$day = 1;
		$first = TRUE;
		$value = 0;
		$monthArray = array();
		$cal = IntlCalendar::createInstance($timeZone, $this->locale->getLocaleCode());
		$cal->set($year, $month, $day);
		$maxDaysInMonth = $cal->getActualMaximum(IntlCalendar::FIELD_DAY_OF_MONTH);
		$formatter = $this->getDateFormatter();
		$formatter->setPattern('e');
		$firstDayIsWhatDow = $formatter->format($cal);
		while ($day <= $maxDaysInMonth) {
			for ($dow = 1; $dow <= 7; $dow++) {
				$cal->set($year, $month, $day);
				$weekOfYear = $cal->get(IntlCalendar::FIELD_WEEK_OF_YEAR);
				if ($weekOfYear > 52) $weekOfYear = 0;
				if ($first) {
					if ($dow == $firstDayIsWhatDow) {
						$first = FALSE;
						$value = $day++;
					} else {
						$value = NULL;
					}
				} else {
					if ($day <= $maxDaysInMonth) {
						$value = $day++;
					} else {
						$value = NULL;
					}
				}
				$dayObj = $this->processEvents(new Day($value), $cal);
				$monthArray[$weekOfYear][$dow] = $dayObj;
			}
		}
		return $monthArray;
	}

	protected function getDay($type, $cal)
	{
		$formatter = $this->getDateFormatter();
		$formatter->setPattern($type);
		return $formatter->format($cal);
	}
	
	protected function getDateFormatter()
	{
		if (!$this->dateFormatter) {
			$this->dateFormatter = $this->locale->getDateFormatter(Locale::DATE_TYPE_FULL);
		}
		return $this->dateFormatter;
	}

	protected function processEvents($dayObj, $cal)
	{
		if ($this->events && $dayObj()) {
			$calDateTime = $cal->toDateTime();
			foreach ($this->events as $id => $eventObj) {
				// is event end date past?
				$next = $eventObj->getNextDate($eventObj->nextDate);
				// if not FALSE that means event is still current
				if ($next) {
					// is event next date == today?
					if ($calDateTime->format('Y-m-d') == $eventObj->nextDate->format('Y-m-d')) {
						// yes: add event to Day + get next date
						$dayObj->events[$eventObj->id] = $eventObj;
						$eventObj->nextDate = $next;
					}
				}
			}
		}
		return $dayObj;
	}
	
	public function calendarForYear($year, 
									$timeZone = NULL, 
									$dayType = self::DAY_1, 
									$monthType = self::MONTH_3, 
									$across = self::DEFAULT_ACROSS)
	{
		if (!$this->yearArray) $this->buildYearArray($year, $timeZone);
		$yMax = (int) (12 / $across);
		$width = (int) (100 / $across);
		$output = '<table>' . PHP_EOL;
		$month = 1;
		for ($y = 1; $y <= $yMax; $y++) {
			$output .= '<tr>';
			for ($x = 1; $x <= $across; $x++) {
				$output .= '<td style="vertical-align:top;" width="' . $width . '%">' 
						 . $this->calendarForMonth($year, $month, $timeZone, $dayType, 
												   $monthType, $this->yearArray[$year][$month++]) 
						 . '</td>';
			}
			$output .= '</tr>' . PHP_EOL;
		}
		$output .= '</table>';
		return $output;
	}
	
	public function calendarForMonth($year, 
									 $month, 
									 $timeZone = NULL, 
									 $dayType = self::DAY_3, 
									 $monthType = self::MONTH_FULL,
									 $monthArray = NULL)
	{
		// init vars
		$first = 0;
		if (!$monthArray) 
			$monthArray = $this->yearArray[$year][$month]
						?? $this->buildMonthArray($year, $month, $timeZone);
		// create IntlCalendar instance
		$month--; 	// IntlCalendar months are 0 based; Jan == 0, Feb == 1, etc.
		$cal = IntlCalendar::createInstance($timeZone, $this->locale->getLocaleCode());
		$cal->set($year, $month, 1);
		$formatter = $this->getDateFormatter();
		$formatter->setPattern($monthType);
		// begin HTML
		$this->height = ($dayType == self::DAY_FULL) ? self::HEIGHT_FULL : self::HEIGHT_SMALL;
		$html = '<h1>' . $formatter->format($cal) . '</h1>';
		$header = '';
		$body   = '';
		foreach ($monthArray as $weekNum => $week) {
			if ($first++ == 1) {
				$header .= $this->getWeekHeaderRow($dayType, $cal, $year, $month, $week);
			}
			$body .= $this->getWeekDaysRow($dayType, $week);
		}
		$html .= '<table>' . PHP_EOL;
		$html .= $header;
		$html .= $body;
		$html .= '</table>' . PHP_EOL;
		return $html;					
	}
	
	protected function getWeekHeaderRow($type, $cal, $year, $month, $week)
	{
		$output = '<tr>';
		$width  = (int) (100/7);
		foreach ($week as $day) {
			$cal->set($year, $month, $day());
			$output .= '<th style="vertical-align:top;" width="' . $width . '%">' . $this->getDay($type, $cal) . '</th>';
		}
		$output .= '</tr>' . PHP_EOL;
		return $output;
	}

	protected function getWeekDaysRow($type, $week)
	{
		$output = '<tr style="height:' . $this->height . ';">';
		$width  = (int) (100/7);
		foreach ($week as $day) {
			$events = '';
			if ($day->events) {
				foreach ($day->events as $single) {
					$events .= '<br>' . $single->title;
					if ($type == self::DAY_FULL) {
						$events .= '<br><i>' . $single->description . '</i>';
					}
				}
			}
			$output .= '<td style="vertical-align:top;" width="' . $width . '%">' . $day() . $events . '</td>';
		}
		$output .= '</tr>' . PHP_EOL;
		return $output;
	}
		
}
