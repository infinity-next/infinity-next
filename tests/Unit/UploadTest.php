<?php

namespace Tests\Unit;

use App\FileStorage;
use App\Filesystem\Upload;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\File\File;

class BoardTest extends TestCase
{
    use RefreshDatabase;

    public function testImageUpload()
    {
        $file = new File(dirname(__FILE__) . "/../Dummy/donut.jpg", true);
        $upload = new Upload($file);
        $storage = $upload->process();

        $this->assertInstanceOf(FileStorage::class, $storage);

        $this->assertCount(1, $storage->thumbnails);
        $this->assertInstanceOf(FileStorage::class, $storage->thumbnails[0]);
        $this->assertTrue($storage->thumbnails[0]->exists);
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
