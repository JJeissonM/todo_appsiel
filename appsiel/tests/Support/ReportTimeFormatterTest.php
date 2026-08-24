<?php

use App\Support\ReportTimeFormatter;

class ReportTimeFormatterTest extends PHPUnit_Framework_TestCase
{
    public function test_it_formats_morning_and_afternoon_times_with_seconds()
    {
        $this->assertSame('02:26:54 a. m.', ReportTimeFormatter::time('02:26:54'));
        $this->assertSame('02:26:54 p. m.', ReportTimeFormatter::time('14:26:54'));
    }

    public function test_it_formats_midnight_and_noon_correctly()
    {
        $this->assertSame('12:00:00 a. m.', ReportTimeFormatter::time('00:00:00'));
        $this->assertSame('12:00:00 p. m.', ReportTimeFormatter::time('12:00:00'));
    }

    public function test_it_preserves_the_date_when_formatting_a_date_time()
    {
        $this->assertSame(
            '2026-08-24 02:26:54 p. m.',
            ReportTimeFormatter::dateTime('2026-08-24 14:26:54')
        );
    }

    public function test_it_uses_the_requested_empty_value_for_missing_dates()
    {
        $this->assertSame('', ReportTimeFormatter::time(null));
        $this->assertSame('No registrado', ReportTimeFormatter::dateTime('0000-00-00 00:00:00', 'No registrado'));
    }
}
