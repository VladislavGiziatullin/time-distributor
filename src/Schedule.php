<?php

namespace vladgtr\TimeDistributor;

use vladgtr\TimeDistributor\exceptions\DayOfWeekNotFound;
use vladgtr\TimeDistributor\exceptions\WeekDaysNotFound;

class Schedule
{
    public const MONDAY = 'monday';
    public const TUESDAY = 'tuesday';
    public const WEDNESDAY = 'wednesday';
    public const THURSDAY = 'thursday';
    public const FRIDAY = 'friday';
    public const SATURDAY = 'saturday';
    public const SUNDAY = 'sunday';

    public const DAYS_OF_WEEK = [
        1 => self::MONDAY,
        2 => self::TUESDAY,
        3 => self::WEDNESDAY,
        4 => self::THURSDAY,
        5 => self::FRIDAY,
        6 => self::SATURDAY,
        7 => self::SUNDAY,
    ];

    /**
     * @var ScheduleDay|null
     */
    private $monday;

    /**
     * @var ScheduleDay|null
     */
    private $tuesday;

    /**
     * @var ScheduleDay|null
     */
    private $wednesday;

    /**
     * @var ScheduleDay|null
     */
    private $thursday;

    /**
     * @var ScheduleDay|null
     */
    private $friday;

    /**
     * @var ScheduleDay|null
     */
    private $saturday;

    /**
     * @var ScheduleDay|null
     */
    private $sunday;

    /**
     * @var ScheduleDay[]
     */
    private $list;

    /**
     * @throws WeekDaysNotFound
     */
    public function __construct(
        ?ScheduleDay $monday = null,
        ?ScheduleDay $tuesday = null,
        ?ScheduleDay $wednesday = null,
        ?ScheduleDay $thursday = null,
        ?ScheduleDay $friday = null,
        ?ScheduleDay $saturday = null,
        ?ScheduleDay $sunday = null
    ) {
        if ($monday === null
            && $tuesday === null
            && $wednesday === null
            && $thursday === null
            && $friday === null
            && $saturday === null
            && $sunday === null
        ) {
            throw new WeekDaysNotFound();
        }

        $this->monday = $monday;
        $this->tuesday = $tuesday;
        $this->wednesday = $wednesday;
        $this->thursday = $thursday;
        $this->friday = $friday;
        $this->saturday = $saturday;
        $this->sunday = $sunday;

        $this->list = [
            self::MONDAY => $this->monday,
            self::TUESDAY => $this->tuesday,
            self::WEDNESDAY => $this->wednesday,
            self::THURSDAY => $this->thursday,
            self::FRIDAY => $this->friday,
            self::SATURDAY => $this->saturday,
            self::SUNDAY => $this->sunday,
        ];
    }

    public static function formatDayOfWeekName(string $dayOfWeek): string
    {
        return trim(strtolower($dayOfWeek));
    }

    public static function checkValidDayOfWeek(string $dayOfWeek): void
    {
        if (!in_array(self::formatDayOfWeekName($dayOfWeek), self::DAYS_OF_WEEK, true)) {
            throw new DayOfWeekNotFound();
        }
    }

    /**
     * @throws DayOfWeekNotFound
     */
    public function getDayOfWeekByName(string $dayOfWeek): ?ScheduleDay
    {
        self::checkValidDayOfWeek($dayOfWeek);
        $dayOfWeek = self::formatDayOfWeekName($dayOfWeek);

        return $this->list[$dayOfWeek];
    }
}
