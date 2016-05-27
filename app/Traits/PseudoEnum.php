<?php

namespace App\Traits;

trait PseudoEnum
{
    /**
     * Which properties of this model are protected, and what values they may be assigned.
     *
     * @var array
     */
    // protected $enum;

    /**
     * Universal mutator that attempts to resolve enum issues.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setAttribute($key, $value)
    {
        parent::setAttribute($key, $value);

        if (isset($this->enum[$key])) {
            if (in_array($value, $this->enum[$key], true)) {
                return $this->attributes[$key] = $value;
            }

            throw new \Exception(get_class($this)." - Setting attribute `{$key}` - \"{$value}\" not in pseudo-enum array [".implode(',', $this->enum[$key]).']');
        }
    }
}
