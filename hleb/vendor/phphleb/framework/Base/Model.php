<?php
/**
 * @author  Foma Tuturov <fomiash@yandex.ru>
 */

declare(strict_types=1);

namespace Hleb\Base;

use Hleb\Constructor\Attributes\AvailableAsParent;
use Hleb\Constructor\Models\ModelTrait;

/**
 * The base class of the model, all Models must be inherited from it.
 * To inherit readonly classes, use ReadonlyModel.
 *
 * Базовый класс модели, все Модели должны быть унаследованы от него.
 * Для наследования readonly классов используйте ReadonlyModel.
 */
#[AvailableAsParent]
abstract class Model
{
    use ModelTrait;
}
