<?php

namespace App\Validators;

use App\Services\ContentFormatter;

/**
 * Content formatting validator..
 *
 * @category   Validator
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2020 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class FormattingValidator
{
    public function validateUgcHeight($attribute, $value, $parameters)
    {
        $height = $parameters[0] ?? 0;

        if ($height <= 0) {
            return true;
        }

        $formatter = new ContentFormatter();
        $formatted = $formatter->formatPost($value);

        return $formatter->getLineCount() <= $height;
    }
}
