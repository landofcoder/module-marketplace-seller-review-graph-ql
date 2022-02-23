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

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Search\Model\Query;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplier\Filter;
use Lof\MarketPlace\Api\SellerRatingsRepositoryInterface;
use Lof\MarketPlace\Model\SellerRatingsRepository;


/**
 * Class SellerById
 *
 * @package Lof\SellerReviewGraphQl\Model\Resolver
 */
class SellerById implements ResolverInterface
{
 

    /**
     * @var SellerRatingsRepositoryInterface
     */
    private $repository;
 /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param SellerRatingsRepositoryInterface $repository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */

    public function __construct(
        SellerRatingsRepositoryInterface $repository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->repository = $repository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;

    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field, 
        $context, 
        ResolveInfo $info, 
        array $value = null, 
        array $args = null
        ){

            $this->vaildateArgs($args);

            $searchCriteria = $this->searchCriteriaBuilder->build('lof_marketplace_seller', $args);
            $searchCriteria->setCurrentPage($args['currentPage']);
            $searchCriteria->setPageSize($args['pageSize']);
            $searchResult = $this->repository->getSellerRatings($seller_id);
    
            return [
                'total_count' => $searchResult->getTotalCount(),
                'items' => $searchResult->getItems(),
            ];
        }
    
        /**
         * @param array $args
         * @throws GraphQlInputException
         */
        private function vaildateArgs(array $args): void
        {
            if (isset($args['seller_id']) && $args['seller_id'] < 1) {
                throw new GraphQlInputException(__('seller_id value must be greater than 0.'));
            }
        }
    
}
