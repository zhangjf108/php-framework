<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Kerisy Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Kerisy\DB\Validators;

use Kerisy\web\AssetBundle;

/**
 * This asset bundle provides the javascript files needed for the [[EmailValidator]]s client validation.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PunycodeAsset extends AssetBundle
{
    public $sourcePath = '@bower/punycode';
    public $js = [
        'punycode.js',
    ];
}
