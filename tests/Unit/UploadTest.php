<?php

namespace Tests\Unit;

use App\FileStorage;
use App\Filesystem\Upload;
use Tests\TestCase;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\File\File;

class UploadTest extends TestCase
{
    public function testImageUploadAndDestruction()
    {
        $file = new File(dirname(__FILE__) . "/../Dummy/donut.jpg", true);
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

    public function testFuzzyban()
    {
        $this->expectException(\Exception::class);

        $file = new File(dirname(__FILE__) . "/../Dummy/donut.jpg", true);
        $upload = new Upload($file);
        $storage = $upload->process();

        $storage->banned_at = now()->timestamp;
        $storage->save();

        $bannedFile = new File(dirname(__FILE__) . "/../Dummy/donut_fuzzy.jpg", true);
        $bannedUpload = new Upload($bannedFile);
        $bannedStorage = $bannedUpload->process();
    }
}
