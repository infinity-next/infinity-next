<?php

namespace Tests\Unit;

use App\FileStorage;
use App\Filesystem\Upload;
use App\Filesystem\BannedHashException;
use App\Filesystem\BannedPhashException;
use Tests\TestCase;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\File\File;

class UploadTest extends TestCase
{
    protected $donut;

    protected function setUp(): void
    {
        parent::setUp();

        $storage = FileStorage::where('hash', '5b8a791985ae692e3a410d621c16b092f67491e19ed0f3fa25d723073604d096')->first();

        if ($storage) {
            $storage->banned_at = now();
            $storage->fuzzybanned_at = now();
            $storage->save();
        }
        else {
            $file = new File(dirname(__FILE__) . "/../Dummy/donut.jpg", true);
            $upload = new Upload($file);
            $storage = $upload->process();

            $storage->banned_at = now();
            $storage->fuzzybanned_at = now();
            $storage->save();
        }

        $this->donut = $storage;
    }

    public function testImageUploadAndDestruction()
    {
        $file = new File(dirname(__FILE__) . "/../Dummy/flock.jpg", true);
        $upload = new Upload($file);
        $storage = $upload->process();

        $this->assertInstanceOf(FileStorage::class, $storage);

        $this->assertCount(1, $storage->thumbnails);
        $this->assertInstanceOf(FileStorage::class, $storage->thumbnails[0]);
        $this->assertTrue($storage->thumbnails[0]->exists);

        $hash = $storage->hash;
        $this->assertEquals(strlen($hash), 64);

        $storage->thumbnails()->forceDelete();
        $storage->forceDelete();
        $this->assertEquals(FileStorage::where('hash', $storage->hash)->count(), 0);
    }

    public function testVideoUpload()
    {
        $file = new File(dirname(__FILE__) . "/../Dummy/small.mp4", true);
        $upload = new Upload($file);
        $storage = $upload->process();

        $this->assertInstanceOf(FileStorage::class, $storage);

        $this->assertCount(1, $storage->thumbnails);
        $this->assertInstanceOf(FileStorage::class, $storage->thumbnails[0]);
        $this->assertTrue($storage->thumbnails[0]->exists);

        $hash = $storage->hash;
        $this->assertEquals(strlen($hash), 64);

        $storage->thumbnails()->forceDelete();
        $storage->forceDelete();
        $this->assertEquals(FileStorage::where('hash', $storage->hash)->count(), 0);
    }

    // distortion: literally fuzzy
    public function testFuzzybanFuzzy()
    {
        $this->expectException(BannedPhashException::class);

        $bannedFile = new File(dirname(__FILE__) . "/../Dummy/donut_fuzzy.jpg", true);
        $bannedUpload = new Upload($bannedFile);
        $bannedStorage = $bannedUpload->processThumbnails(true);
    }

    // distortion: bottom-right black border
    public function testFuzzybanBlacked()
    {
        $this->expectException(BannedPhashException::class);
        $bannedFile = new File(dirname(__FILE__) . "/../Dummy/donut_blacked.jpg", true);
        $bannedUpload = new Upload($bannedFile);
        $bannedStorage = $bannedUpload->processThumbnails(true);
    }

    // distortion: uniform white border, mirrored
    public function testFuzzybanOutline()
    {
        $this->expectException(BannedPhashException::class);

        $bannedFile = new File(dirname(__FILE__) . "/../Dummy/donut_outlined.jpg", true);
        $bannedUpload = new Upload($bannedFile);
        $bannedStorage = $bannedUpload->processThumbnails(true);
    }

    public function testPdfUpload()
    {
        $file = new File(dirname(__FILE__) . "/../Dummy/sample.pdf", true);
        $upload = new Upload($file);
        $storage = $upload->process();

        $this->assertInstanceOf(FileStorage::class, $storage);

        $this->assertCount(1, $storage->thumbnails);
        $this->assertInstanceOf(FileStorage::class, $storage->thumbnails[0]);
        $this->assertTrue($storage->thumbnails[0]->exists);

        $hash = $storage->hash;
        $this->assertEquals(strlen($hash), 64);

        $storage->thumbnails()->forceDelete();
        $storage->forceDelete();
        $this->assertEquals(FileStorage::where('hash', $storage->hash)->count(), 0);
    }
}
