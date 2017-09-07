<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Kerisy Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Kerisy\Db;

/**
 * IndexConstraint represents the metadata of a table `INDEX` constraint.
 *
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0.13
 */
class IndexConstraint extends Constraint
{
    /**
     * @var bool whether the index is unique.
     */
    public $isUnique;
    /**
     * @var bool whether the index was created for a primary key.
     */
    public $isPrimary;
}
