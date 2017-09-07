<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Kerisy Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Kerisy\Db;

use Kerisy\Core\Component;

/**
 * Constraint represents the metadata of a table constraint.
 *
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0.13
 */
class Constraint extends Component
{
    /**
     * @var string[]|null list of column names the constraint belongs to.
     */
    public $columnNames;
    /**
     * @var string|null the constraint name.
     */
    public $name;
}
