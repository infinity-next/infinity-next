<?php

namespace Tests\Unit;

use App\Support\Tripcode\InsecureTripcode;
use App\Support\Tripcode\SecureTripcode;
use App\Support\Tripcode\PrettyGoodTripcode;
use App\Support\Tripcode\InvalidPgpTripcode;
use Tests\TestCase;

class TripcodeTest extends TestCase
{
    public function testInsecureTripcode()
    {
        $tripcode = new InsecureTripcode("test");
        $this->assertSame((string)$tripcode, ".CzKQna1OU");
        $this->assertNotSame((string)$tripcode, "ppWv3KSryQ");

        $tripcode = new InsecureTripcode("test2");
        $this->assertSame((string)$tripcode, "ppWv3KSryQ");
        $this->assertNotSame((string)$tripcode, ".CzKQna1OU");
    }

    public function testSecureTripcode()
    {
        $insecure1 = new InsecureTripcode("test");
        $insecure2 = new InsecureTripcode("test2");
        $tripcode1 = new SecureTripcode("test");
        $tripcode2 = new SecureTripcode("test2");

        $this->assertNotSame((string)$tripcode1, (string)$tripcode2);
        $this->assertNotSame((string)$insecure1, (string)$tripcode1);
        $this->assertNotSame((string)$insecure2, (string)$tripcode2);

        $same1 = new SecureTripcode("1234567890");
        $same2 = new SecureTripcode("1234567890");
        $this->assertSame((string)$same1, (string)$same2);
    }

    public function testPrettyGoodTripcode()
    {
        $signed = file_get_contents(__DIR__ . "/../Dummy/pgt_sig.txt");
        $tripcode = new PrettyGoodTripcode($signed);

        $this->assertSame($tripcode->getTripcode(), "8E570D9F7F70256595769E49F9BC5BCAD7E18635");
        $this->assertSame($tripcode->getTimestamp(), 1587472827);
    }

    public function testForgedPrettyGoodTripcode()
    {
        $this->expectException(InvalidPgpTripcode::class);

        $forged = file_get_contents(__DIR__ . "/../Dummy/pgt_bad_sig.txt");
        $tripcode = new PrettyGoodTripcode($forged);
    }

    public function testSpoofedPrettyGoodTripcode()
    {
        $this->expectException(InvalidPgpTripcode::class);

        $forged = file_get_contents(__DIR__ . "/../Dummy/pgt_spoof_sig.txt");
        $tripcode = new PrettyGoodTripcode($forged);
    }
}
