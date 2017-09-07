<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Kerisy\Utils;

/**
 * ArrayHelper provides additional array functionality that you can use in your
 * application.
 *
 * For more details and usage information on ArrayHelper, see the [guide article on array helpers](guide:helper-array).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ArrayUtil extends BaseArrayUtil
{
    /**
     * @param array $arr
     */
    public static function arrayUnique(array &$arr)
    {
        $map = array();
        foreach ($arr as $k => $v) {
            if (is_object($v)) {
                $hash = spl_object_hash($v);
            } elseif (is_resource($v)) {
                $hash = intval($v);
            } else {
                $hash = $v;
            }
            if (isset($map[$hash])) {
                unset($arr[$k]);
            } else {
                $map[$hash] = true;
            }
        }
    }
}
