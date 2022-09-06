<?php

namespace vladgtr\TimeDistributor\tests;

use DateTime;
use DateTimeZone;
use Exception;
use PHPUnit\Framework\TestCase;
use vladgtr\TimeDistributor\DistributorService;
use vladgtr\TimeDistributor\exceptions\DayOfWeekNotFound;
use vladgtr\TimeDistributor\exceptions\ObjectNotNormalized;
use vladgtr\TimeDistributor\exceptions\WeekDaysNotFound;
use vladgtr\TimeDistributor\Schedule;
use vladgtr\TimeDistributor\ScheduleDay;
use vladgtr\TimeDistributor\ScheduleWorkTimeRange;
use vladgtr\TimeDistributor\WorkDateTime;

class DistributorServiceTest extends TestCase
{
    private const BEGIN_DATE = '2020-03-26 10:00:00';
    private const EUROPE_MOSCOW_TIME_ZONE = 'Europe/Moscow';

    /**
     * @var DistributorService
     */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $scheduleWorkTimeRange = new ScheduleWorkTimeRange(
            new WorkDateTime(23, 0),
            new WorkDateTime(7, 0)
        );

        $this->service = new DistributorService($scheduleWorkTimeRange);
    }

    /**
     * @dataProvider calculateEndDateProvider
     *
     * @throws DayOfWeekNotFound
     * @throws ObjectNotNormalized
     */
    public function testCalculateEndDate(
        DateTime $beginDate,
        bool $isNight,
        Schedule $schedule,
        int $timeForDistribution,
        DateTime $expected
    ): void {
        $result = $this->service->calculateEndDateBySchedule(
            $beginDate,
            $schedule,
            $timeForDistribution,
            $isNight
        );

        self::assertInstanceOf(DateTime::class, $result);
        self::assertEquals($expected, $result);
        self::assertEquals($expected->getTimestamp(), $result->getTimestamp());
    }

    /**
     * @throws DayOfWeekNotFound
     * @throws WeekDaysNotFound
     */
    public function calculateEndDateProvider(): array
    {
        $scheduleWithOutWeekend = $this->getScheduleWithOutWeekend();
        $scheduleWithOutWeekend2 = $this->getScheduleWithOutWeekend2();
        $scheduleWithWeekend = $this->getScheduleWithWeekend();
        $scheduleWithWeekend2 = $this->getScheduleWithWeekend2();

        $timezone = new DateTimeZone(self::EUROPE_MOSCOW_TIME_ZONE);
        $beginDate20200326T100000 = new DateTime(self::BEGIN_DATE, $timezone);
        $beginDate20200326T055959 = new DateTime('2020-03-26 5:59:59', $timezone);
        $beginDate20200414T100059 = new DateTime('2020-04-14 10:00:59', $timezone);
        $beginDate20200414T220059 = new DateTime('2020-04-14 22:00:59', $timezone);
        $beginDate20200414T200059 = new DateTime('2020-04-14 20:00:59', $timezone);
        $beginDate20200413T153705 = new DateTime('2020-04-13 15:37:05', $timezone);
        $beginDate20200414T005959 = new DateTime('2020-04-14 0:59:59', $timezone);
        $beginDate20200415T115959 = new DateTime('2020-04-15 11:59:59', $timezone);
        $beginDate20200414T180059 = new DateTime('2020-04-14 18:00:59', $timezone);
        $beginDate20200425T160000 = new DateTime('2020-04-25 16:00:00', $timezone);
        $beginDate20200429T170059 = new DateTime('2020-04-29 17:00:59', $timezone);
        $beginDate20200429T180000 = new DateTime('2020-04-29 18:00:00', $timezone);
        $beginDate20200420T185000 = new DateTime('2020-04-20 18:50:00', $timezone);

        return [
            'withOutWeekend' => [
                'beginDate' => (clone $beginDate20200326T100000),
                'isNight' => false,
                'schedule' => $scheduleWithOutWeekend,
                'timeForDistribution' => 36 * WorkDateTime::SECONDS_IN_HOUR,
                'expected' => (clone $beginDate20200326T100000)->modify('+2 days')->setTime(22, 0, 0),
            ],
            'withOutWeekend2' => [
                'beginDate' => (clone $beginDate20200326T100000)->setTime(11, 59, 59),
                'isNight' => false,
                'schedule' => $scheduleWithOutWeekend,
                'timeForDistribution' => WorkDateTime::SECONDS_IN_HOUR + 30 * WorkDateTime::SECONDS_IN_MINUTE,
                'expected' => (clone $beginDate20200326T100000)->setTime(13, 29, 59),
            ],
            'withOutWeekend3' => [
                'beginDate' => (clone $beginDate20200326T100000)->setTime(22, 00, 00),
                'isNight' => false,
                'schedule' => $scheduleWithOutWeekend,
                'timeForDistribution' => WorkDateTime::SECONDS_IN_HOUR,
                'expected' => (clone $beginDate20200326T100000)->modify('+1 day')->setTime(11, 00, 00),
            ],
            'withOutWeekend4' => [
                'beginDate' => (clone $beginDate20200414T100059),
                'isNight' => false,
                'schedule' => $scheduleWithOutWeekend,
                'timeForDistribution' => WorkDateTime::SECONDS_IN_HOUR + 30 * WorkDateTime::SECONDS_IN_MINUTE,
                'expected' => (clone $beginDate20200414T100059)->setTime(11, 30, 59),
            ],
            'withOutWeekend5' => [
                'beginDate' => (clone $beginDate20200414T220059),
                'isNight' => false,
                'schedule' => $scheduleWithOutWeekend,
                'timeForDistribution' => WorkDateTime::SECONDS_IN_HOUR + 30 * WorkDateTime::SECONDS_IN_MINUTE,
                'expected' => (clone $beginDate20200414T220059)->modify('+1 day')->setTime(11, 30),
            ],
            'withOutWeekend6' => [
                'beginDate' => (clone $beginDate20200414T200059),
                'isNight' => false,
                'schedule' => $scheduleWithOutWeekend,
                'timeForDistribution' => WorkDateTime::SECONDS_IN_HOUR + 30 * WorkDateTime::SECONDS_IN_MINUTE,
                'expected' => (clone $beginDate20200414T200059)->setTime(21, 30, 59),
            ],
            'withOutWeekend7' => [
                'beginDate' => (clone $beginDate20200413T153705),
                'isNight' => false,
                'schedule' => $scheduleWithOutWeekend,
                'timeForDistribution' => WorkDateTime::SECONDS_IN_HOUR + 30 * WorkDateTime::SECONDS_IN_MINUTE,
                'expected' => (clone $beginDate20200413T153705)->setTime(17, 7, 5),
            ],
            'withOutWeekend8' => [
                'beginDate' => (clone $beginDate20200413T153705),
                'isNight' => false,
                'schedule' => $scheduleWithOutWeekend,
                'timeForDistribution' => WorkDateTime::SECONDS_IN_HOUR + 30 * WorkDateTime::SECONDS_IN_MINUTE,
                'expected' => (clone $beginDate20200413T153705)->setTime(17, 7, 5),
            ],
            'withOutWeekend9' => [
                'beginDate' => (clone $beginDate20200429T170059),
                'isNight' => false,
                'schedule' => $scheduleWithOutWeekend2,
                'timeForDistribution' => 8 * WorkDateTime::SECONDS_IN_HOUR,
                'expected' => (clone $beginDate20200429T170059)->modify('+1 day')->setTime(10, 0, 59),
            ],
            'withOutWeekend10' => [
                'beginDate' => (clone $beginDate20200429T180000),
                'isNight' => false,
                'schedule' => $scheduleWithOutWeekend2,
                'timeForDistribution' => 6 * WorkDateTime::SECONDS_IN_HOUR,
                'expected' => (clone $beginDate20200429T180000)->modify('+1 day')->setTime(9, 0, 0),
            ],
            'withOutWeekend11' => [
                'beginDate' => (clone $beginDate20200420T185000),
                'isNight' => false,
                'schedule' => $scheduleWithOutWeekend,
                'timeForDistribution' => 72 * WorkDateTime::SECONDS_IN_HOUR,
                'expected' => (clone $beginDate20200420T185000)->modify('+6 day')->setTime(18, 50, 0),
            ],
            'withOutWeekendNight' => [
                'beginDate' => (clone $beginDate20200326T100000),
                'isNight' => true,
                'schedule' => $scheduleWithOutWeekend,
                'timeForDistribution' => 28 * WorkDateTime::SECONDS_IN_HOUR,
                'expected' => (clone $beginDate20200326T100000)->modify('+4 days')->setTime(3, 0),
            ],
            'withOutWeekendNight2' => [
                'beginDate' => (clone $beginDate20200326T100000)->setTime(11, 59, 59),
                'isNight' => true,
                'schedule' => $scheduleWithOutWeekend,
                'timeForDistribution' => WorkDateTime::SECONDS_IN_HOUR + 30 * WorkDateTime::SECONDS_IN_MINUTE,
                'expected' => (clone $beginDate20200326T100000)->modify('+1 days')->setTime(0, 30),
            ],
            'withOutWeekendNight3' => [
                'beginDate' => (clone $beginDate20200414T005959),
                'isNight' => true,
                'schedule' => $scheduleWithOutWeekend,
                'timeForDistribution' => WorkDateTime::SECONDS_IN_HOUR + 30 * WorkDateTime::SECONDS_IN_MINUTE,
                'expected' => (clone $beginDate20200414T005959)->setTime(2, 29, 59),
            ],
            'withOutWeekendNight4' => [
                'beginDate' => (clone $beginDate20200326T055959),
                'isNight' => true,
                'schedule' => $scheduleWithOutWeekend,
                'timeForDistribution' => WorkDateTime::SECONDS_IN_HOUR + 30 * WorkDateTime::SECONDS_IN_MINUTE,
                'expected' => (clone $beginDate20200326T055959)->setTime(23, 29, 59),
            ],
            'withWeekend' => [
                'beginDate' => (clone $beginDate20200326T100000),
                'isNight' => false,
                'schedule' => $scheduleWithWeekend,
                'timeForDistribution' => 36 * WorkDateTime::SECONDS_IN_HOUR,
                'expected' => (clone $beginDate20200326T100000)->modify('+4 days')->setTime(22, 0, 0),
            ],
            'withWeekend2' => [
                'beginDate' => (clone $beginDate20200415T115959),
                'isNight' => false,
                'schedule' => $scheduleWithWeekend2,
                'timeForDistribution' => 4 * WorkDateTime::SECONDS_IN_HOUR,
                'expected' => (clone $beginDate20200415T115959)->setTime(15, 59, 59),
            ],
            'withWeekend3' => [
                'beginDate' => (clone $beginDate20200414T180059),
                'isNight' => false,
                'schedule' => $scheduleWithWeekend2,
                'timeForDistribution' => 4 * WorkDateTime::SECONDS_IN_HOUR,
                'expected' => (clone $beginDate20200414T180059)->modify('+1 day')->setTime(13, 00, 59),
            ],
            'withWeekend4' => [
                'beginDate' => (clone $beginDate20200425T160000),
                'isNight' => false,
                'schedule' => $scheduleWithWeekend2,
                'timeForDistribution' => 1 * WorkDateTime::SECONDS_IN_HOUR,
                'expected' => (clone $beginDate20200425T160000)->modify('+2 day')->setTime(11, 00, 00),
            ],
        ];
    }

    /**
     * @throws DayOfWeekNotFound
     * @throws WeekDaysNotFound
     * @throws Exception
     */
    private function getScheduleWithOutWeekend(): Schedule
    {
        $startWorkDate = new WorkDateTime(10, 0, 0);
        $endWorkDate = new WorkDateTime(22, 0, 0);
        return $this->mockSchedule($startWorkDate, $endWorkDate, $startWorkDate, $endWorkDate);
    }

    /**
     * @return Schedule
     * @throws DayOfWeekNotFound
     * @throws WeekDaysNotFound
     * @throws Exception
     */
    private function getScheduleWithOutWeekend2(): Schedule
    {
        $startWorkDate = new WorkDateTime(8, 0, 0);
        $endWorkDate = new WorkDateTime(23, 0, 0);
        return $this->mockSchedule($startWorkDate, $endWorkDate, $startWorkDate, $endWorkDate);
    }

    /**
     * @return Schedule
     * @throws DayOfWeekNotFound
     * @throws WeekDaysNotFound
     * @throws Exception
     */
    private function getScheduleWithWeekend(): Schedule
    {
        $startWorkDate = new WorkDateTime(10, 0, 0);
        $endWorkDate = new WorkDateTime(22, 0, 0);
        return $this->mockSchedule($startWorkDate, $endWorkDate);
    }

    /**
     * @return Schedule
     * @throws DayOfWeekNotFound
     * @throws WeekDaysNotFound
     * @throws Exception
     */
    private function getScheduleWithWeekend2(): Schedule
    {
        $startWorkDate = new WorkDateTime(10, 0, 0);
        $endWorkDate = new WorkDateTime(19, 0, 0);

        return $this->mockSchedule($startWorkDate, $endWorkDate);
    }

    /**
     * @param WorkDateTime $startWorkDate
     * @param WorkDateTime $endWorkDate
     * @param WorkDateTime|null $startWeekendWorkDate
     * @param WorkDateTime|null $endWeekendWorkDate
     *
     * @return Schedule
     * @throws DayOfWeekNotFound
     * @throws WeekDaysNotFound
     */
    private function mockSchedule(
        WorkDateTime $startWorkDate,
        WorkDateTime $endWorkDate,
        ?WorkDateTime $startWeekendWorkDate = null,
        ?WorkDateTime $endWeekendWorkDate = null
    ): Schedule {
        $saturdayScheduleDay = $sundayScheduleDay = null;
        if ($startWeekendWorkDate instanceof WorkDateTime && $endWeekendWorkDate instanceof WorkDateTime) {
            $saturdayScheduleDay =
                new ScheduleDay(
                    Schedule::SATURDAY,
                    new ScheduleWorkTimeRange(
                        $startWeekendWorkDate,
                        $endWeekendWorkDate
                    )
                );
            $sundayScheduleDay =
                new ScheduleDay(
                    Schedule::SATURDAY,
                    new ScheduleWorkTimeRange(
                        $startWeekendWorkDate,
                        $endWeekendWorkDate
                    )
                );
        }

        return new Schedule(
            new ScheduleDay(
                Schedule::MONDAY,
                new ScheduleWorkTimeRange(
                    $startWorkDate,
                    $endWorkDate
                )
            ),
            new ScheduleDay(
                Schedule::TUESDAY,
                new ScheduleWorkTimeRange(
                    $startWorkDate,
                    $endWorkDate
                )
            ),
            new ScheduleDay(
                Schedule::WEDNESDAY,
                new ScheduleWorkTimeRange(
                    $startWorkDate,
                    $endWorkDate
                )
            ),
            new ScheduleDay(
                Schedule::THURSDAY,
                new ScheduleWorkTimeRange(
                    $startWorkDate,
                    $endWorkDate
                )
            ),
            new ScheduleDay(
                Schedule::FRIDAY,
                new ScheduleWorkTimeRange(
                    $startWorkDate,
                    $endWorkDate
                )
            ),
            $saturdayScheduleDay,
            $sundayScheduleDay
        );
    }

    /**
     * @dataProvider getWorkTimeProvider
     *
     * @param int $expectedWorkTime
     * @param WorkDateTime $startWorkDateTime
     * @param WorkDateTime $endWorkDateTime
     *
     * @throws ObjectNotNormalized
     */
    public function testGetWorkTime(
        int $expectedWorkTime,
        WorkDateTime $startWorkDateTime,
        WorkDateTime $endWorkDateTime
    ): void {
        $scheduleWorkTimeRange = new ScheduleWorkTimeRange($startWorkDateTime, $endWorkDateTime);

        self::assertEquals(
            $expectedWorkTime,
            $this->service->getWorkTime($scheduleWorkTimeRange)
        );
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getWorkTimeProvider(): array
    {
        return [
            [
                'expectedWorkTime' => 8 * WorkDateTime::SECONDS_IN_HOUR,
                'startWorkDateTime' => new WorkDateTime(0, 0),
                'endWorkDateTime' => new WorkDateTime(8, 0),
            ],
            [
                'expectedWorkTime' => 7 * WorkDateTime::SECONDS_IN_HOUR,
                'startWorkDateTime' => new WorkDateTime(1, 0),
                'endWorkDateTime' => new WorkDateTime(8, 0),
            ],
            [
                'expectedWorkTime' => 9 * WorkDateTime::SECONDS_IN_HOUR,
                'startWorkDateTime' => new WorkDateTime(23, 0),
                'endWorkDateTime' => new WorkDateTime(8, 0),
            ],
            [
                'expectedWorkTime' => 16 * WorkDateTime::SECONDS_IN_HOUR,
                'startWorkDateTime' => new WorkDateTime(16, 0),
                'endWorkDateTime' => new WorkDateTime(8, 0),
            ],
            [
                'expectedWorkTime' => 8 * WorkDateTime::SECONDS_IN_HOUR,
                'startWorkDateTime' => new WorkDateTime(16, 0),
                'endWorkDateTime' => new WorkDateTime(0, 0),
            ],
            [
                'expectedWorkTime' => 23 * WorkDateTime::SECONDS_IN_HOUR,
                'startWorkDateTime' => new WorkDateTime(9, 0),
                'endWorkDateTime' => new WorkDateTime(8, 0),
            ],
            [
                'expectedWorkTime' => 24 * WorkDateTime::SECONDS_IN_HOUR,
                'startWorkDateTime' => new WorkDateTime(9, 0),
                'endWorkDateTime' => new WorkDateTime(9, 0),
            ],
            [
                'expectedWorkTime' => 24 * WorkDateTime::SECONDS_IN_HOUR,
                'startWorkDateTime' => new WorkDateTime(0, 0),
                'endWorkDateTime' => new WorkDateTime(0, 0),
            ],
            [
                'expectedWorkTime' => 10 * WorkDateTime::SECONDS_IN_MINUTE,
                'startWorkDateTime' => new WorkDateTime(0, 20),
                'endWorkDateTime' => new WorkDateTime(0, 30),
            ],
            [
                'expectedWorkTime' => 23 * WorkDateTime::SECONDS_IN_HOUR + 50 * WorkDateTime::SECONDS_IN_MINUTE,
                'startWorkDateTime' => new WorkDateTime(0, 30),
                'endWorkDateTime' => new WorkDateTime(0, 20),
            ],
            [
                'expectedWorkTime' => 1 * WorkDateTime::SECONDS_IN_HOUR,
                'startWorkDateTime' => new WorkDateTime(0, 0),
                'endWorkDateTime' => new WorkDateTime(1, 0),
            ],
            [
                'expectedWorkTime' => 23 * WorkDateTime::SECONDS_IN_HOUR,
                'startWorkDateTime' => new WorkDateTime(1, 0),
                'endWorkDateTime' => new WorkDateTime(0, 0),
            ],
        ];
    }

    /**
     * @dataProvider isWorkTimeProvider
     *
     * @param bool $expected
     * @param DateTime $checkedDateTime
     * @param WorkDateTime $startWorkDateTime
     * @param WorkDateTime $endWorkDateTime
     *
     * @throws ObjectNotNormalized
     */
    public function testIsWorkTime(
        bool $expected,
        DateTime $checkedDateTime,
        WorkDateTime $startWorkDateTime,
        WorkDateTime $endWorkDateTime
    ): void {
        $scheduleWorkTimeRange = new ScheduleWorkTimeRange($startWorkDateTime, $endWorkDateTime);

        self::assertEquals(
            $expected,
            $this->service->isWorkTime($checkedDateTime, $scheduleWorkTimeRange)
        );
    }

    /**
     * @return array
     * @throws Exception
     */
    public function isWorkTimeProvider(): array
    {
        $checkedDateTime = new DateTime(self::BEGIN_DATE);
        return [
            [
                'expected' => true,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(1, 0),
                'startWorkDateTime' => new WorkDateTime(0, 0),
                'endWorkDateTime' => new WorkDateTime(8, 0),
            ],
            [
                'expected' => false,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(23, 0),
                'startWorkDateTime' => new WorkDateTime(0, 0),
                'endWorkDateTime' => new WorkDateTime(8, 0),
            ],
            [
                'expected' => true,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(1, 0),
                'startWorkDateTime' => new WorkDateTime(1, 0),
                'endWorkDateTime' => new WorkDateTime(8, 0),
            ],
            [
                'expected' => false,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(23, 0),
                'startWorkDateTime' => new WorkDateTime(1, 0),
                'endWorkDateTime' => new WorkDateTime(8, 0),
            ],
            [
                'expected' => true,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(1, 0),
                'startWorkDateTime' => new WorkDateTime(23, 0),
                'endWorkDateTime' => new WorkDateTime(8, 0),
            ],
            [
                'expected' => true,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(23, 0),
                'startWorkDateTime' => new WorkDateTime(23, 0),
                'endWorkDateTime' => new WorkDateTime(8, 0),
            ],
            [
                'expected' => true,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(8, 0),
                'startWorkDateTime' => new WorkDateTime(23, 0),
                'endWorkDateTime' => new WorkDateTime(8, 0),
            ],
            [
                'expected' => false,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(10, 0),
                'startWorkDateTime' => new WorkDateTime(23, 0),
                'endWorkDateTime' => new WorkDateTime(8, 0),
            ],
            [
                'expected' => true,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(20, 0),
                'startWorkDateTime' => new WorkDateTime(16, 0),
                'endWorkDateTime' => new WorkDateTime(8, 0),
            ],
            [
                'expected' => false,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(9, 0),
                'startWorkDateTime' => new WorkDateTime(16, 0),
                'endWorkDateTime' => new WorkDateTime(8, 0),
            ],
            [
                'expected' => true,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(0, 0),
                'startWorkDateTime' => new WorkDateTime(16, 0),
                'endWorkDateTime' => new WorkDateTime(0, 0),
            ],
            [
                'expected' => false,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(15, 0),
                'startWorkDateTime' => new WorkDateTime(16, 0),
                'endWorkDateTime' => new WorkDateTime(0, 0),
            ],
            [
                'expected' => true,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(16, 30),
                'startWorkDateTime' => new WorkDateTime(9, 0),
                'endWorkDateTime' => new WorkDateTime(8, 0),
            ],
            [
                'expected' => false,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(8, 30),
                'startWorkDateTime' => new WorkDateTime(9, 0),
                'endWorkDateTime' => new WorkDateTime(8, 0),
            ],
            [
                'expected' => true,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(8, 30),
                'startWorkDateTime' => new WorkDateTime(9, 0),
                'endWorkDateTime' => new WorkDateTime(9, 0),
            ],
            [
                'expected' => true,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(9, 0),
                'startWorkDateTime' => new WorkDateTime(9, 0),
                'endWorkDateTime' => new WorkDateTime(9, 0),
            ],
            [
                'expected' => true,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(0, 0),
                'startWorkDateTime' => new WorkDateTime(0, 0),
                'endWorkDateTime' => new WorkDateTime(0, 0),
            ],
            [
                'expected' => true,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(11, 0),
                'startWorkDateTime' => new WorkDateTime(0, 0),
                'endWorkDateTime' => new WorkDateTime(0, 0),
            ],
            [
                'expected' => true,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(0, 25),
                'startWorkDateTime' => new WorkDateTime(0, 20),
                'endWorkDateTime' => new WorkDateTime(0, 30),
            ],
            [
                'expected' => false,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(0, 35),
                'startWorkDateTime' => new WorkDateTime(0, 20),
                'endWorkDateTime' => new WorkDateTime(0, 30),
            ],
            [
                'expected' => true,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(12, 25),
                'startWorkDateTime' => new WorkDateTime(0, 30),
                'endWorkDateTime' => new WorkDateTime(0, 20),
            ],
            [
                'expected' => false,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(0, 25),
                'startWorkDateTime' => new WorkDateTime(0, 30),
                'endWorkDateTime' => new WorkDateTime(0, 20),
            ],
            [
                'expected' => true,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(0, 25),
                'startWorkDateTime' => new WorkDateTime(0, 0),
                'endWorkDateTime' => new WorkDateTime(1, 0),
            ],
            [
                'expected' => false,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(1, 30),
                'startWorkDateTime' => new WorkDateTime(0, 0),
                'endWorkDateTime' => new WorkDateTime(1, 0),
            ],
            [
                'expected' => true,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(12, 25),
                'startWorkDateTime' => new WorkDateTime(1, 0),
                'endWorkDateTime' => new WorkDateTime(0, 0),
            ],
            [
                'expected' => false,
                'checkedDateTime' => (clone $checkedDateTime)->setTime(0, 25),
                'startWorkDateTime' => new WorkDateTime(1, 0),
                'endWorkDateTime' => new WorkDateTime(0, 0),
            ],
        ];
    }
}
