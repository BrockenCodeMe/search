<?php

/**
 * This file is part of RollerworksSearch Component package.
 *
 * (c) 2012-2014 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Search\Tests\Doctrine\Dbal;

use Rollerworks\Component\Search\Doctrine\Dbal\ConversionStrategyInterface;
use Rollerworks\Component\Search\Doctrine\Dbal\SqlValueConversionInterface;

interface SqlValueConversionStrategyInterface extends ConversionStrategyInterface, SqlValueConversionInterface
{
}