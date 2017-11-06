<?php declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\CartBridge\Rule;

use Shopware\Cart\Cart\CalculatedCart;
use Shopware\Cart\Rule\Match;
use Shopware\Context\Struct\ShopContext;
use Shopware\Framework\Struct\IndexedCollection;

class BillingAreaRule extends \Shopware\Cart\Rule\Rule
{
    /**
     * @var int[]
     */
    protected $areaIds;

    /**
     * @param int[] $areaIds
     */
    public function __construct(array $areaIds)
    {
        $this->areaIds = $areaIds;
    }

    public function match(
        CalculatedCart $calculatedCart,
        ShopContext $context,
        IndexedCollection $collection
    ): Match {
        if (!$customer = $context->getCustomer()) {
            return new Match(false, ['Not logged in customer']);
        }

        $id = $customer->getActiveBillingAddress()->getCountry()->getAreaUuid();

        return new Match(
            in_array($id, $this->areaIds, true),
            ['Billing area does not match']
        );
    }
}
