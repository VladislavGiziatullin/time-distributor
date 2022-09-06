<?php

namespace vladgtr\TimeDistributor;

use DateTime;
use DateTimeZone;
use Exception;
use vladgtr\TimeDistributor\exceptions\ClosedMethod;
use vladgtr\TimeDistributor\exceptions\ImmutableObject;
use vladgtr\TimeDistributor\exceptions\ObjectNotNormalized;

class WorkDateTime extends DateTime
{
    public const UNIX_YEAR = 1970;
    public const UNIX_MONTH = 1;
    public const UNIX_DAY = 1;

    public const SECONDS_IN_MINUTE = 60;
    public const SECONDS_IN_HOUR = 3600;
    public const SECONDS_IN_DAY = 86400;

    public const FORMAT_ONLY_HOUR = 'H';
    public const FORMAT_ONLY_MINUTE = 'i';
    public const FORMAT_ONLY_SECOND = 's';
    public const FORMAT_ONLY_DAY_OF_WEEK_FULL_NAME = 'l';

    public const FORMAT_ONLY_YEAR = 'Y';
    public const FORMAT_ONLY_MONTH = 'm';
    public const FORMAT_ONLY_DAY = 'd';

    private $lock = false;

    private $normalized = null;

    /**
     * @throws Exception
     */
    public function __construct(int $hour, int $minutes, int $seconds = 0)
    {
        parent::__construct(null, null);
        parent::setDate(self::UNIX_YEAR, self::UNIX_MONTH, self::UNIX_DAY);
        parent::setTime($hour, $minutes, $seconds);
        if (parent::getTimestamp() === 0) {
            $this->setNextDay();
        }
        $this->lock = true;
    }

    /**
     * @throws ObjectNotNormalized
     */
    public function convertToDateTime(): DateTime
    {
        return new DateTime($this->format(self::RFC3339), $this->getTimezone());
    }

    public function convertToDateTimeWithDate(DateTime $dateTime): DateTime
    {
        return $this->convertToDateTime()
            ->setDate(
                $dateTime->format(WorkDateTime::FORMAT_ONLY_YEAR),
                $dateTime->format(WorkDateTime::FORMAT_ONLY_MONTH),
                $dateTime->format(WorkDateTime::FORMAT_ONLY_DAY)
            );
    }

    public static function createFromFormat($format, $time, DateTimeZone $timezone = null)
    {
        throw new ClosedMethod();
    }

    public static function createFromImmutable($dateTimeImmutable)
    {
        throw new ClosedMethod();
    }

    public static function __set_state($array)
    {
        throw new ClosedMethod();
    }

    public function setDate($year, $month, $day)
    {
        throw new ImmutableObject();
    }

    public function setTime($hour, $minute, $second = 0, $microseconds = 0)
    {
        throw new ImmutableObject();
    }

    public function modify($modify)
    {
        throw new ImmutableObject();
    }

    public function sub($interval)
    {
        if ($this->lock) {
            throw new ImmutableObject();
        }
        parent::sub($interval);
    }

    public function add($interval)
    {
        if ($this->lock) {
            throw new ImmutableObject();
        }
        parent::add($interval);
    }

    public function setISODate($year, $week, $day = 1)
    {
        throw new ImmutableObject();
    }

    public function setTimestamp($unixtimestamp)
    {
        throw new ImmutableObject();
    }

    public function setTimezone($timezone)
    {
        throw new ImmutableObject();
    }

    public function format($format)
    {
        $this->checkWorkDateTimeOnNormalized();
        return parent::format($format);
    }

    /**
     * @throws ObjectNotNormalized
     */
    public function diff($datetime2, $absolute = false)
    {
        $this->checkWorkDateTimeOnNormalized();
        return parent::diff($datetime2, $absolute);
    }

    /**
     * @throws ObjectNotNormalized
     */
    public function getOffset()
    {
        $this->checkWorkDateTimeOnNormalized();
        return parent::getOffset();
    }

    /**
     * @throws ObjectNotNormalized
     */
    public function getTimestamp()
    {
        $this->checkWorkDateTimeOnNormalized();

        return parent::getTimestamp();
    }

    public function markNormalized(): void
    {
        $this->normalized = true;
    }

    public function setNextDay(): void
    {
        if ($this->normalized) {
            return;
        }

        parent::modify('+1 day');
        $this->normalized = true;
    }

    /**
     * @throws ObjectNotNormalized
     */
    private function checkWorkDateTimeOnNormalized(): void
    {
        if ($this->normalized === false) {
            throw new ObjectNotNormalized();
        }
    }
}
