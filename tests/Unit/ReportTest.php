<?php

namespace Tests\Unit;

use App\Board;
use App\Report;
use App\User;
use Tests\Testcase;

class ReportTest extends TestCase
{
    public function testStateDemoted()
    {
        $report = factory(Report::class)->state('demoted')->make();

        $this->assertTrue($report->isOpen());
        $this->assertTrue($report->isDemoted());
        $this->assertFalse($report->isPromoted());
    }

    public function testStateOpen()
    {
        $report = factory(Report::class)->make();

        $this->assertTrue($report->isOpen());
        $this->assertFalse($report->isDemoted());
        $this->assertFalse($report->isPromoted());
    }

    public function testStatePromoted()
    {
        $report = factory(Report::class)->state('promoted')->make();

        $this->assertTrue($report->isOpen());
        $this->assertFalse($report->isDemoted());
        $this->assertTrue($report->isPromoted());
    }
}
