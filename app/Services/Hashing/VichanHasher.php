<?php

namespace App\Services\Hashing;

use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class VichanHasher implements HasherContract
{
    /**
     * The salt stored alongside a password in the Vichan model.
     *
     * @var string
     */
    public $salt = '';

    /**
     * Hash the given value.
     *
     * @param string $value
     * @param array  $options
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function make($value, array $options = array())
    {
        return hash('sha256', $this->salt.sha1($value));
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param string $value
     * @param string $hashedValue
     * @param array  $options
     *
     * @return bool
     */
    public function check($value, $hashedValue, array $options = array())
    {
        return $this->make($value, $options) == $hashedValue;
    }

    /**
     * Check if the given hash has been hashed using the given options.
     *
     * @param string $hashedValue
     * @param array  $options
     *
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = array())
    {
        return false;
    }
}
