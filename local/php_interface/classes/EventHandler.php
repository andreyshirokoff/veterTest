<?
namespace App;

use Bitrix\Main,
    Bitrix\Sale,
    Bitrix\Sale\Compatible\DiscountCompatibility,
    Bitrix\Main\EventManager,
    Bitrix\Sale\Discount\Gift;

class EventHandler
{
    public static function init()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->addEventHandler(
            "sale",
            "onManagerCouponAdd",
            [self::class, 'addGiftToBasket']
        );
    }

    public static function addGiftToBasket(Main\Event $event)
    {
        $basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Main\Context::getCurrent()->getSite());
        $LID = Main\Context::getCurrent()->getSite(); //САЙТ
        $basketItems = $basket->getBasketItems();

        $arBasketProductIDs = [];
        foreach ($basketItems as $basketItem) {
            $arBasketProductIDs[] = $basketItem->getProductId();
        }

        $userId = Main\Engine\CurrentUser::get()->getId();
        $giftManager = Gift\Manager::getInstance()->setUserId($userId);

        DiscountCompatibility::stopUsageCompatible();
        $giftCollections = $giftManager->getCollectionsByBasket($basket, \CSaleDiscount::GetByID(1)); //Айдишник правила
        DiscountCompatibility::revertUsageCompatible();

        if (!$giftManager->existsDiscountsWithGift()) {
            return;
        }

        $giftProductID = 0;
        foreach ($giftCollections as $collection) {
            foreach ($collection as $gift) {
                $giftProductID = $gift->getProductId();
                break 2;
            }
        }

        if (!$giftProductID) {
            return;
        }

        if (\CCatalogSKU::getExistOffers($giftProductID)) {
            $giftProductID = array_shift(\CCatalogSKU::getOffersList($giftProductID)[$giftProductID])["ID"];
        }

        // Gift in basket.
        if (in_array($giftProductID, $arBasketProductIDs)) {
            return;
        }

        $basketGiftItem = $basket->createItem("catalog", $giftProductID);
        $basketGiftItem->setFields(array(
            "QUANTITY" => 1,
            "DISCOUNT_PRICE" => 0,
            "CURRENCY" => \Bitrix\Currency\CurrencyManager::getBaseCurrency(),
            "LID" => $LID,
            "PRODUCT_PROVIDER_CLASS" => "CCatalogProductProvider",
        ));

        $basket->save();
    }
}


