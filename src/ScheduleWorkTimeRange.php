<?php

namespace vladgtr\TimeDistributor;

use DateTime;
use Exception;
use vladgtr\TimeDistributor\exceptions\ObjectNotNormalized;

class ScheduleWorkTimeRange
{
    /**
     * @var WorkDateTime
     */
    private $startWorkDateTime;

    /**
     * @var WorkDateTime
     */
    private $endWorkDateTime;

    /**
     * ScheduleWorkTimeRange constructor.
     *
     * @param WorkDateTime $startWorkDateTime
     * @param WorkDateTime $endWorkDateTime
     */
    public function __construct(WorkDateTime $startWorkDateTime, WorkDateTime $endWorkDateTime)
    {
        if ($startWorkDateTime > $endWorkDateTime) {
            $endWorkDateTime->setNextDay();
        }
        $startWorkDateTime->markNormalized();
        $endWorkDateTime->markNormalized();

        $this->startWorkDateTime = $startWorkDateTime;
        $this->endWorkDateTime = $endWorkDateTime;
    }

    public function getStartWorkDateTime(): WorkDateTime
    {
        return clone $this->startWorkDateTime;
    }

    public function getEndWorkDateTime(): WorkDateTime
    {
        return clone $this->endWorkDateTime;
    }

    /**
     * @throws ObjectNotNormalized
     * @throws Exception
     */
    public function getWorkDateTimeFromDateTime(DateTime $dateTime): WorkDateTime
    {
        $workDateTime = new WorkDateTime(
            (int)$dateTime->format(WorkDateTime::FORMAT_ONLY_HOUR),
            (int)$dateTime->format(WorkDateTime::FORMAT_ONLY_MINUTE),
            (int)$dateTime->format(WorkDateTime::FORMAT_ONLY_SECOND)
        );

        // Случай перехода в новый день
        if ($this->endWorkDateTime < $this->startWorkDateTime) {
            $workDateTime->markNormalized();
            return $workDateTime;
        }

        $workTimestamp = $workDateTime->getTimestamp();
        $startWorkTimestamp = $this->startWorkDateTime->getTimestamp();
        $initialEndWorkTimestamp = $this->endWorkDateTime->getTimestamp() - WorkDateTime::SECONDS_IN_DAY;

        if (($workTimestamp >= $startWorkTimestamp && $workTimestamp <= $initialEndWorkTimestamp)
            || ($workTimestamp <= $startWorkTimestamp && $workTimestamp <= $initialEndWorkTimestamp)
        ) {
            $workDateTime->setNextDay();
        }

        $workDateTime->markNormalized();

        return $workDateTime;
    }
}
