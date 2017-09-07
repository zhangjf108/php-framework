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
 * Validator for São Tomé and Príncipe subdivision code.
 *
 * ISO 3166-1 alpha-2: ST
 *
 * @link http://www.geonames.org/ST/administrative-division-sao-tome-and-principe.html
 */
class StSubdivisionCode extends AbstractSearcher
{
    public $haystack = [
        'P', // Principe
        'S', // Sao Tome
    ];

    public $compareIdentical = true;
}