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
 * @package    Lof_MarketplaceGraphQl
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

declare(strict_types=1);

namespace Lof\SellerReviewGraphQl\Model\Resolver\Customer;

use Lof\MarketPlace\Api\Data\SellerInterface;
use Lof\MarketPlace\Api\SellersFrontendRepositoryInterface;
use Lof\MarketPlace\Api\SellersRepositoryInterface;
use Lof\MarketPlace\Helper\Data;
use Lof\MarketPlace\Model\RatingFactory;
use Lof\MarketPlace\Model\Sender;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lof\MarketPlace\Model\ResourceModel\Seller\CollectionFactory;

/**
 * Class ReviewSeller
 * @package Lof\MarketplaceGraphQl\Model\Resolver
 */
class ReviewSeller implements ResolverInterface
{

    /**
     * @var SellerInterface
     */
    private $sellerInterface;
    /**
     * @var RatingFactory
     */
    private $_rateSeller;
    /**
     * @var SellersRepositoryInterface
     */
    private $sellerRepository;
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var Sender
     */
    private $sender;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;


    /**
     * ReviewSeller constructor.
     * @param RatingFactory $rateSeller
     * @param SellerInterface $sellerInterface
     * @param SellersFrontendRepositoryInterface $sellersRepository
     * @param Data $helper
     * @param Sender $sender
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        RatingFactory $rateSeller,
        SellerInterface $sellerInterface,
        SellersFrontendRepositoryInterface $sellersRepository,
        Data $helper,
        Sender $sender,
        CollectionFactory $collectionFactory
    ) {
        $this->_rateSeller = $rateSeller;
        $this->sellerInterface = $sellerInterface;
        $this->sellerRepository = $sellersRepository;
        $this->helper = $helper;
        $this->sender = $sender;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        if (!($args['input']) || !isset($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        if (!isset($args['sellerUrl']) || !($args['sellerUrl'])) {
            throw new GraphQlInputException(__('sellerUrl value is required for query.'));
        }

        /** @var ContextInterface $context */
        if (true === $context->getExtensionAttributes()->getIsCustomer()) {

            $customerId = $context->getUserId();

            $seller = $this->collectionFactory->create()
                ->addFieldToFilter("url_key", $args['sellerUrl']['0'])
                ->getFirstItem();
            $sellerId = $seller->getSellerId();

            if (!isset($sellerId) || !$sellerId) {
                return [
                    "code" => 1,
                    "message" => "Seller does not exits!"
                ];
            }

            $args['input']['seller_email'] = $seller->getEmail();
            $args['input']['seller_name'] = $seller->getName();
            $args['input']['rating'] = ($args['input']['rate1'] + $args['input']['rate2'] + $args['input']['rate3']) / 3;
            if ($this->helper->getConfig('general_settings/rating_approval')) {
                $args['status'] = 'pending';
            } else {
                $args['status'] = 'accept';
            }
            $ratingModel = $this->_rateSeller->create();
            $ratingModel->setData($args['input']);
            $ratingModel->save();
            $args['namestore'] = $this->helper->getStoreName();
            $args['urllogin'] = $this->helper->getStoreUrl('customer/account/login');

            if ($this->helper->getConfig('email_settings/enable_send_email')) {
                $this->sender->newRating($args);
            }
            return [
                "code" => 0,
                "message" => "Submit rating success!"
            ];
        }

        $seller = $this->collectionFactory->create()
            ->addFieldToFilter("url_key", $args['sellerUrl']['0'])
            ->getFirstItem();
        $sellerId = $seller->getSellerId();

        if (!isset($sellerId) || !$sellerId) {
            return [
                "code" => 1,
                "message" => "Seller does not exits!"
            ];
        }
        $args['input']['seller_email'] = $seller->getEmail();
        $args['input']['seller_name'] = $seller->getName();

        $args['input']['rating'] = ($args['input']['rate1'] + $args['input']['rate2'] + $args['input']['rate3']) / 3;
        if ($this->helper->getConfig('general_settings/rating_approval')) {
            $args['status'] = 'pending';
        } else {
            $args['status'] = 'accept';
        }
        $ratingModel = $this->_rateSeller->create();
        $ratingModel->setData($args['input']);
        $ratingModel->save();
        $args['namestore'] = $this->helper->getStoreName();
        $args['urllogin'] = $this->helper->getStoreUrl('customer/account/login');

        if ($this->helper->getConfig('email_settings/enable_send_email')) {
            $this->sender->newRating($args);
        }
        return [
            "code" => 0,
            "message" => "Submit rating success!"
        ];
    }
}
