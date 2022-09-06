<?php

namespace vladgtr\TimeDistributor;

use DateTime;
use vladgtr\TimeDistributor\exceptions\DayOfWeekNotFound;
use vladgtr\TimeDistributor\exceptions\ObjectNotNormalized;

class DistributorService
{
    /**
     * @var ScheduleWorkTimeRange
     */
    private $defaultScheduleNightWorkTimeRange;

    public function __construct(
        ?ScheduleWorkTimeRange $defaultScheduleNightWorkTimeRange = null
    ) {
        if ($defaultScheduleNightWorkTimeRange !== null) {
            $this->defaultScheduleNightWorkTimeRange = $defaultScheduleNightWorkTimeRange;
        }
    }

    /**
     * @throws DayOfWeekNotFound
     * @throws ObjectNotNormalized
     */
    public function calculateEndDateBySchedule(
        DateTime $beginDate,
        Schedule $schedule,
        int $timeForDistribution,
        bool $isNight
    ): DateTime {
        $correctedBeginDate = $this->correctBeginDateTimeBySchedule($beginDate, $schedule, $isNight);
        return $this->distributeTimeBySchedule($correctedBeginDate, $schedule, $timeForDistribution, $isNight);
    }

    /**
     * @throws DayOfWeekNotFound
     */
    public function setDateToNearestWorkDay(DateTime $dateTime, Schedule $schedule): DateTime
    {
        $resultDateTime = clone $dateTime;
        do {
            $scheduleOnDay = $this->getScheduleOnDay($resultDateTime, $schedule);
            if ($scheduleOnDay === null) {
                $resultDateTime->modify('+1 day');
            }
        } while ($scheduleOnDay === null);

        return $resultDateTime;
    }

    /**
     * @throws DayOfWeekNotFound
     * @throws ObjectNotNormalized
     */
    public function distributeTimeBySchedule(
        DateTime $beginDate,
        Schedule $schedule,
        int $timeForDistribution,
        bool $isNight
    ): DateTime {
        $endDate = clone $beginDate;
        do {
            $endDate = $this->setDateToNearestWorkDay($endDate, $schedule);

            $scheduleWorkTimeRange = $this->getScheduleWorkTimeRange($endDate, $schedule, $isNight);

            $startWorkDateTime = $scheduleWorkTimeRange->getStartWorkDateTime();
            $workDateTime = $scheduleWorkTimeRange->getWorkDateTimeFromDateTime($endDate);
            $workTimestamp = $workDateTime->getTimestamp();

            // Высчитывает разницу от начала проведения работ до текущего времени, и добавляет в timeForDistribution
            $timeForDistribution += $workTimestamp - $startWorkDateTime->getTimestamp();

            // Тк вычли разницу устанавливаем дату в начало работ
            $endDate->setTime(
                $startWorkDateTime->format(WorkDateTime::FORMAT_ONLY_HOUR),
                $startWorkDateTime->format(WorkDateTime::FORMAT_ONLY_MINUTE),
                $startWorkDateTime->format(WorkDateTime::FORMAT_ONLY_SECOND)
            );

            // Если работы ночные и был переход с предыдущего на новый день,
            // например время начала 23:00, а время в расчёте 2:00 следующего дня,
            // из за чего сбрасывая время на 23:00 нужно так же перевести день на предыдущий
            if ($workTimestamp > WorkDateTime::SECONDS_IN_DAY) {
                $endDate->modify('-1 day');
            }

            $workTime = $this->getWorkTime($scheduleWorkTimeRange);

            $remainingTimeForDistribution = $timeForDistribution - $workTime;
            if ($remainingTimeForDistribution <= 0) {
                $endDate->modify("+{$timeForDistribution} seconds");
            } elseif ($remainingTimeForDistribution > 0) {
                $endDate->modify('+1 day');
                $timeForDistribution = $remainingTimeForDistribution;
            }
        } while ($remainingTimeForDistribution > 0);

        return $endDate;
    }

    /**
     * @throws DayOfWeekNotFound
     * @throws ObjectNotNormalized
     */
    public function correctBeginDateTimeBySchedule(
        DateTime $beginDate,
        Schedule $schedule,
        bool $isNight
    ): DateTime {
        $resultBeginDate = $this->setDateToNearestWorkDay($beginDate, $schedule);

        $scheduleWorkTimeRange = $this->getScheduleWorkTimeRange($resultBeginDate, $schedule, $isNight);
        $workDateTime = $scheduleWorkTimeRange->getWorkDateTimeFromDateTime($resultBeginDate);
        $startWorkDateTime = $scheduleWorkTimeRange->getStartWorkDateTime();

        // Если beginDate был в выходные, и после корректировки на ближайший рабочей день, дата передвинулась, то соответственно устанавливаем время начала работ по магазину
        if ($resultBeginDate > $beginDate) {
            $resultBeginDate->setTime(
                (int)$startWorkDateTime->format(WorkDateTime::FORMAT_ONLY_HOUR),
                (int)$startWorkDateTime->format(WorkDateTime::FORMAT_ONLY_MINUTE)
            );

            return $resultBeginDate;
        }


        if ($this->isWorkTime($resultBeginDate, $scheduleWorkTimeRange)) {
            return $resultBeginDate;
        }

        // Тк время не рабочее значит, рабочее время не попадает в интервал графика,
        // соответственно рабочее время либо больще либо меньше времени начала работ,
        // если меньше то прибавлять день не требуется просто добавить время до необходимого,
        // если больше нужно прибавить один день тк в рабочее время мы не попадаем и установить начальное рабочее время
        if ($workDateTime > $startWorkDateTime) {
            $resultBeginDate->modify('+1 day');
            $resultBeginDate = $this->setDateToNearestWorkDay($resultBeginDate, $schedule);

            // Берем время нового дня
            $scheduleWorkTimeRange = $this->getScheduleWorkTimeRange($resultBeginDate, $schedule, $isNight);
            $startWorkDateTime = $scheduleWorkTimeRange->getStartWorkDateTime();
        }

        $resultBeginDate->setTime(
            (int)$startWorkDateTime->format(WorkDateTime::FORMAT_ONLY_HOUR),
            (int)$startWorkDateTime->format(WorkDateTime::FORMAT_ONLY_MINUTE)
        );

        return $resultBeginDate;
    }

    /**
     * @throws DayOfWeekNotFound
     */
    public function getScheduleOnDay(DateTime $dateTime, Schedule $schedule): ?ScheduleDay
    {
        $dayOfWeek = strtolower($dateTime->format(WorkDateTime::FORMAT_ONLY_DAY_OF_WEEK_FULL_NAME));
        return $schedule->getDayOfWeekByName($dayOfWeek);
    }

    /**
     * @throws ObjectNotNormalized
     */
    public function isWorkTime(
        DateTime $checkedDateTime,
        ScheduleWorkTimeRange $scheduleWorkTimeRange
    ): bool {
        $startWorkDateTime = $scheduleWorkTimeRange->getStartWorkDateTime();
        $endWorkDateTime = $scheduleWorkTimeRange->getEndWorkDateTime();

        // 24 hours work
        if ($startWorkDateTime == $endWorkDateTime) {
            return true;
        }

        $checkedWorkDateTime = $scheduleWorkTimeRange->getWorkDateTimeFromDateTime($checkedDateTime);

        // Checking time in working time
        if ($checkedWorkDateTime >= $startWorkDateTime && $checkedWorkDateTime <= $endWorkDateTime) {
            return true;
        }

        return false;
    }

    /**
     * @throws ObjectNotNormalized
     */
    public function getWorkTime(ScheduleWorkTimeRange $scheduleWorkTimeRange): int
    {
        $startWorkDateTime = $scheduleWorkTimeRange->getStartWorkDateTime();
        $endWorkDateTime = $scheduleWorkTimeRange->getEndWorkDateTime();

        $workTime = $endWorkDateTime->getTimestamp() - $startWorkDateTime->getTimestamp();

        if ($workTime === 0) {
            $workTime = WorkDateTime::SECONDS_IN_DAY;
        }

        return $workTime;
    }

    /**
     * @throws DayOfWeekNotFound
     */
    private function getScheduleWorkTimeRange(
        DateTime $dateTime,
        Schedule $schedule,
        bool $isNight
    ): ScheduleWorkTimeRange {
        if ($isNight && $this->defaultScheduleNightWorkTimeRange instanceof ScheduleWorkTimeRange) {
            $scheduleWorkTimeRange = $this->defaultScheduleNightWorkTimeRange;
        } else {
            $scheduleWorkTimeRange = $this->getScheduleOnDay($dateTime, $schedule)->getScheduleWorkTimeRange();
        }

        if ($isNight) {
            return new ScheduleWorkTimeRange(
                $scheduleWorkTimeRange->getStartWorkDateTime(),
                $scheduleWorkTimeRange->getEndWorkDateTime()
            );
        }

        return $scheduleWorkTimeRange;
    }
}
