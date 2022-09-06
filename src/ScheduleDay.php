<?php

namespace vladgtr\TimeDistributor;

use vladgtr\TimeDistributor\exceptions\DayOfWeekNotFound;

class ScheduleDay
{
    /**
     * @var string Schedule::DAYS_OF_WEEK
     */
    private $fullName;

    /**
     * @var ScheduleWorkTimeRangeTuple
     */
    private $timeRanges;

    /**
     * @throws DayOfWeekNotFound
     */
    public function __construct(string $fullName, ScheduleWorkTimeRange $requiredTimeRange, ScheduleWorkTimeRange ...$timeRange)
    {
        Schedule::checkValidDayOfWeek($fullName);

        $this->fullName = Schedule::formatDayOfWeekName($fullName);
        $this->timeRanges = new ScheduleWorkTimeRangeTuple(...array_merge([$requiredTimeRange], $timeRange));
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getScheduleWorkTimeRange(): ScheduleWorkTimeRange
    {
        return $this->timeRanges->current();
    }
}
