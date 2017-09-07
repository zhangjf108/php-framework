<?php

/*
 * This file is part of Respect/Validation.
 *
 * (c) Alexandre Gomes Gaigalas <alexandre@gaigalas.net>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace Kerisy\Validator\Rules\SubdivisionCode;

use Kerisy\Validator\Rules\AbstractSearcher;

/**
 * Validator for Sierra Leone subdivision code.
 *
 * ISO 3166-1 alpha-2: SL
 *
 * @link http://www.geonames.org/SL/administrative-division-sierra-leone.html
 */
class SlSubdivisionCode extends AbstractSearcher
{
    public $haystack = [
        'E', // Eastern
        'N', // Northern
        'S', // Southern
        'W', // Western
    ];

    public $compareIdentical = true;
}
