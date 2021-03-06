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
 * Validator for Northern Mariana Islands subdivision code.
 *
 * ISO 3166-1 alpha-2: MP
 *
 * @link http://www.geonames.org/MP/administrative-division-northern-mariana-islands.html
 */
class MpSubdivisionCode extends AbstractSearcher
{
    public $haystack = [
        'N', // Northern Islands
        'R', // Rota
        'S', // Saipan
        'T', // Tinian
    ];

    public $compareIdentical = true;
}
