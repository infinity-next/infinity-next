<?php

class ExampleTest extends TestCase
{
    public function testIndex()
    {
        $this->visit('/')
            ->see(config('app.name'))
            ->assertResponseStatus(200);
    }

    public function testNotFound()
    {
        $this->call('GET', '.impossibleRoute');
        $this->assertResponseStatus(404);
    }

    public function testNotFound()
    {
        $this->call('GET', '.impossibleRoute');
        $this->assertResponseStatus(404);
    }
}
