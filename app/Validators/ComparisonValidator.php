<?php

namespace App\Validators;

class ComparisonValidator
{
    public function validateGreaterThan($attribute, $value, $parameters)
    {
        $value = (int) $value;

        foreach ($parameters as $parameter) {
            if (isset($this->data[$parameter]) && (int) $this->data[$parameter] > $value) {
                return false;
            }
        }

        return true;
    }

    public function validateLessThan($attribute, $value, $parameters)
    {
        $value = (int) $value;
        foreach ($parameters as $parameter) {
            if (isset($this->data[$parameter]) && (int) $this->data[$parameter] < $value) {
                return false;
            }
        }

        return true;
    }
}
