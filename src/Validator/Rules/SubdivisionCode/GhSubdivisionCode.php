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
 * Validator for Ghana subdivision code.
 *
 * ISO 3166-1 alpha-2: GH
 *
 * @link http://www.geonames.org/GH/administrative-division-ghana.html
 */
class GhSubdivisionCode extends AbstractSearcher
{
    public $haystack = [
        'AA', // Greater Accra Region
        'AH', // Ashanti Region
        'BA', // Brong-Ahafo Region
        'CP', // Central Region
        'EP', // Eastern Region
        'NP', // Northern Region
        'TV', // Volta Region
        'UE', // Upper East Region
        'UW', // Upper West Region
        'WP', // Western Region
    ];

    public $compareIdentical = true;
}
