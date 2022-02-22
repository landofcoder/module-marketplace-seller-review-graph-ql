<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_SellerReviewGraphQl
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\SellerReviewGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class SellerById
 *
 * @package Lof\SellerReviewGraphQl\Model\Resolver
 */
class SellerById extends AbstractSellerQuery implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->_labelFlag = 1;
        $this->validateArgs($args);

        $sellerData = $this->_sellerRepository->get($args['seller_id']);
        if($sellerData){
            $products = $sellerData->getProducts();
            if($items = $products->getItems()){
                $productArray = [];
                /** @var \Magento\Catalog\Model\Product $product */
                foreach ($items as $product) {
                    $productArray[$product->getId()] = $product->load($product->getId())->getData();
                    $productArray[$product->getId()]['model'] = $product;
                }

                $newProducts =[
                                'total_count' => $products->getTotalCount(),
                                'items' => $productArray
                                ];
                $sellerData->setProducts($newProducts);
            }
        }
        return $sellerData?$sellerData->__toArray():[];
    }
}
