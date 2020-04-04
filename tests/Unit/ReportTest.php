<?php

namespace Tests\Routes\Panel;

use App\Report;
use Tests\Testcase;

class ReportTest extends TestCase
{
    public function testDemoted()
    {
        $report = factory(Report::class)->state('demoted')->make();

        $this->assertTrue($report->isOpen());
        $this->assertTrue($report->isDemoted());
        $this->assertFalse($report->isPromoted());
    }

    public function testOpen()
    {
        $report = factory(Report::class)->make();

        $this->assertTrue($report->isOpen());
        $this->assertFalse($report->isDemoted());
        $this->assertFalse($report->isPromoted());
    }

    public function testPromoted()
    {
        $report = factory(Report::class)->state('promoted')->make();

        $this->assertTrue($report->isOpen());
        $this->assertFalse($report->isDemoted());
        $this->assertTrue($report->isPromoted());
    }
}
