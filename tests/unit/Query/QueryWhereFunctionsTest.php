<?php 

namespace Tests\Unit;

use Phluent\Query;
// validate input valuies ? date ? day hour etc?

class QueryWhereFunctionsTest extends \PHPUnit\Framework\TestCase
{
    // TIME()
    public function testWhereTime()
    {
        $expected = "SELECT * FROM `movies` WHERE TIME(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereTime('release_date', '12:45');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['12:45'], $q->getParams());
    }
    public function testWhereTimeOrWhereTime()
    {
        $expected = "SELECT * FROM `movies` WHERE TIME(`movies`.`release_date`)=? OR TIME(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereTime('release_date', '12:45')->orWhereTime('release_date', '14:30');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['12:45','14:30'], $q->getParams());
    }
    public function testWhereTimeOrWhereTimeWithOperators()
    {
        $expected = "SELECT * FROM `movies` WHERE TIME(`movies`.`release_date`)<>? OR TIME(`movies`.`release_date`)<>?";

        $q = new Query();
        $q->table('movies')->whereTime('release_date', '<>', '12:45')->orWhereTime('release_date', '<>', '14:30');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['12:45','14:30'], $q->getParams());
    }
    // HOUR()
    public function testWhereHour()
    {
        $expected = "SELECT * FROM `movies` WHERE HOUR(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereHour('release_date', '12');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['12'], $q->getParams());
    }
    public function testWhereHourOrWhereHour()
    {
        $expected = "SELECT * FROM `movies` WHERE HOUR(`movies`.`release_date`)=? OR HOUR(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereHour('release_date', '12')->orWhereHour('release_date', '14');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['12','14'], $q->getParams());
    }
    public function testWhereHourOrWhereHourWithOperators()
    {
        $expected = "SELECT * FROM `movies` WHERE HOUR(`movies`.`release_date`)<>? OR HOUR(`movies`.`release_date`)<>?";

        $q = new Query();
        $q->table('movies')->whereHour('release_date', '<>', '12')->orWhereHour('release_date', '<>', '14');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['12','14'], $q->getParams());
    }
    // MINUTE()
    public function testWhereMinute()
    {
        $expected = "SELECT * FROM `movies` WHERE MINUTE(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereMinute('release_date', '12');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['12'], $q->getParams());
    }
    public function testWhereMinuteOrWhereMinute()
    {
        $expected = "SELECT * FROM `movies` WHERE MINUTE(`movies`.`release_date`)=? OR MINUTE(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereMinute('release_date', '12')->orWhereMinute('release_date', '14');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['12','14'], $q->getParams());
    }
    public function testWhereMinuteOrWhereMinuteWithOperators()
    {
        $expected = "SELECT * FROM `movies` WHERE MINUTE(`movies`.`release_date`)<>? OR MINUTE(`movies`.`release_date`)<>?";

        $q = new Query();
        $q->table('movies')->whereMinute('release_date', '<>', '12')->orWhereMinute('release_date', '<>', '14');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['12','14'], $q->getParams());
    }
    // DATE()
    public function testWhereDate()
    {
        $expected = "SELECT * FROM `movies` WHERE DATE(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereDate('release_date', '2020-12-01');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2020-12-01'], $q->getParams());
    }
    public function testWhereDateOrWhereDate()
    {
        $expected = "SELECT * FROM `movies` WHERE DATE(`movies`.`release_date`)=? OR DATE(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereDate('release_date', '2020-12-01')->orWhereDate('release_date', '2020-12-14');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2020-12-01','2020-12-14'], $q->getParams());
    }
    public function testWhereDateOrWhereDateWithOperators()
    {
        $expected = "SELECT * FROM `movies` WHERE DATE(`movies`.`release_date`)<>? OR DATE(`movies`.`release_date`)<>?";

        $q = new Query();
        $q->table('movies')->whereDate('release_date', '<>', '2020-12-01')->orWhereDate('release_date', '<>', '2020-12-14');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2020-12-01','2020-12-14'], $q->getParams());
    }
    // DAY()
    public function testWhereDay()
    {
        $expected = "SELECT * FROM `movies` WHERE DAY(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereDay('release_date', '2');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2'], $q->getParams());
    }
    public function testWhereDayOrWhereDay()
    {
        $expected = "SELECT * FROM `movies` WHERE DAY(`movies`.`release_date`)=? OR DAY(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereDay('release_date', '2')->orWhereDay('release_date', '4');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2','4'], $q->getParams());
    }
    public function testWhereDayOrWhereDayWithOperators()
    {
        $expected = "SELECT * FROM `movies` WHERE DAY(`movies`.`release_date`)<>? OR DAY(`movies`.`release_date`)<>?";

        $q = new Query();
        $q->table('movies')->whereDay('release_date', '<>', '2')->orWhereDay('release_date', '<>', '4');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2','4'], $q->getParams());
    }
    // MONTH()
    public function testWhereMonth()
    {
        $expected = "SELECT * FROM `movies` WHERE MONTH(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereMonth('release_date', '2');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2'], $q->getParams());
    }
    public function testWhereMonthOrWhereMonth()
    {
        $expected = "SELECT * FROM `movies` WHERE MONTH(`movies`.`release_date`)=? OR MONTH(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereMonth('release_date', '2')->orWhereMonth('release_date', '4');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2','4'], $q->getParams());
    }
    public function testWhereMonthOrWhereMonthWithOperators()
    {
        $expected = "SELECT * FROM `movies` WHERE MONTH(`movies`.`release_date`)<>? OR MONTH(`movies`.`release_date`)<>?";

        $q = new Query();
        $q->table('movies')->whereMonth('release_date', '<>', '2')->orWhereMonth('release_date', '<>', '4');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2','4'], $q->getParams());
    }
    // YEAR()
    public function testWhereYear()
    {
        $expected = "SELECT * FROM `movies` WHERE YEAR(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereYear('release_date', '2019');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2019'], $q->getParams());
    }
    public function testWhereYearOrWhereYear()
    {
        $expected = "SELECT * FROM `movies` WHERE YEAR(`movies`.`release_date`)=? OR YEAR(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereYear('release_date', '2012')->orWhereYear('release_date', '2014');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2012','2014'], $q->getParams());
    }
    public function testWhereYearOrWhereYearWithOperators()
    {
        $expected = "SELECT * FROM `movies` WHERE YEAR(`movies`.`release_date`)<>? OR YEAR(`movies`.`release_date`)<>?";

        $q = new Query();
        $q->table('movies')->whereYear('release_date', '<>', '2012')->orWhereYear('release_date', '<>', '2014');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2012','2014'], $q->getParams());
    }
    // WEEK()
    public function testWhereWeek()
    {
        $expected = "SELECT * FROM `movies` WHERE WEEK(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereWeek('release_date', '52');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['52'], $q->getParams());
    }
    public function testWhereWeekOrWhereWeek()
    {
        $expected = "SELECT * FROM `movies` WHERE WEEK(`movies`.`release_date`)=? OR WEEK(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereWeek('release_date', '12')->orWhereWeek('release_date', '34');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['12','34'], $q->getParams());
    }
    public function testWhereWeekOrWhereWeekWithOperators()
    {
        $expected = "SELECT * FROM `movies` WHERE WEEK(`movies`.`release_date`)<>? OR WEEK(`movies`.`release_date`)<>?";

        $q = new Query();
        $q->table('movies')->whereWeek('release_date', '<>', '2')->orWhereWeek('release_date', '<>', '34');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2','34'], $q->getParams());
    }
    // WEEKDAY()
    public function testWhereWeekday()
    {
        $expected = "SELECT * FROM `movies` WHERE WEEKDAY(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereWeekday('release_date', '2');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2'], $q->getParams());
    }
    public function testWhereWeekdayOrWhereWeekday()
    {
        $expected = "SELECT * FROM `movies` WHERE WEEKDAY(`movies`.`release_date`)=? OR WEEKDAY(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereWeekday('release_date', '2')->orWhereWeekday('release_date', '4');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2','4'], $q->getParams());
    }
    public function testWhereWeekdayOrWhereWeekdayWithOperators()
    {
        $expected = "SELECT * FROM `movies` WHERE WEEKDAY(`movies`.`release_date`)<>? OR WEEKDAY(`movies`.`release_date`)<>?";

        $q = new Query();
        $q->table('movies')->whereWeekday('release_date', '<>', '2')->orWhereWeekday('release_date', '<>', '4');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2','4'], $q->getParams());
    }
    // QUARTER()
    public function testWhereQuarter()
    {
        $expected = "SELECT * FROM `movies` WHERE QUARTER(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereQuarter('release_date', '2');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2'], $q->getParams());
    }
    public function testWhereQuarterOrWhereQuarter()
    {
        $expected = "SELECT * FROM `movies` WHERE QUARTER(`movies`.`release_date`)=? OR QUARTER(`movies`.`release_date`)=?";

        $q = new Query();
        $q->table('movies')->whereQuarter('release_date', '2')->orWhereQuarter('release_date', '4');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2','4'], $q->getParams());
    }
    public function testWhereQuarterOrWhereQuarterWithOperators()
    {
        $expected = "SELECT * FROM `movies` WHERE QUARTER(`movies`.`release_date`)<>? OR QUARTER(`movies`.`release_date`)<>?";

        $q = new Query();
        $q->table('movies')->whereQuarter('release_date', '<>', '2')->orWhereQuarter('release_date', '<>', '4');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2','4'], $q->getParams());
    }
}


