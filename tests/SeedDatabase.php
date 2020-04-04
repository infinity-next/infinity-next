<?php

namespace Tests;

trait SeedDatabase
{
    /**
     * Boot the testing helper traits.
     *
     * @return array
     */
    protected function setUpTraits()
    {
        $uses = parent::setUpTraits();
        $uses[static::class] = count($uses);

        $this->seed();

        return $uses;
    }
}
