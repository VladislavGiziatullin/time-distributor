<?php

namespace vladgtr\TimeDistributor;

class ScheduleWorkTimeRangeTuple extends BaseTuple
{
    public function __construct(ScheduleWorkTimeRange ...$value)
    {
        parent::__construct(...$value);
    }

    public function current(): ScheduleWorkTimeRange
    {
        return parent::current();
    }
}
