<?php

/*
 * This file is part of Respect/Validation.
 *
 * (c) Alexandre Gomes Gaigalas <alexandre@gaigalas.net>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace Kerisy\Validator\Exceptions\Locale;

use Kerisy\Validator\Exceptions\BicException;

class GermanBicException extends BicException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} must be a german BIC',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} must not be a german BIC',
        ],
    ];
}
