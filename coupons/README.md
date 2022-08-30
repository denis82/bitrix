# чтобы передать промокод, применный к заказу, на сторону 1С



```shell
<?
use Bitrix\Main;
Main\EventManager::getInstance()->addEventHandler(
    'sale',
    'OnSaleOrderBeforeSaved',
    'saleOrderBeforeSaved'
);

function saleOrderBeforeSaved(Main\Event $event)
{  
    $order = $event->getParameter("ENTITY");
    $coupons = $order->getDiscount()->getApplyResult()["COUPON_LIST"];
    $propertyCollection = $order->getPropertyCollection();

    $propsData = [];

    foreach ($propertyCollection as $propertyItem) {
        if (!empty($propertyItem->getField("CODE"))) {
            $propsData[$propertyItem->getField("CODE")] = trim($propertyItem->getValue());
        }
    }
 
    foreach ($propertyCollection as $propertyItem) {
  
        switch ($propertyItem->getField("CODE")) {

            case 'PROMOKOD': 

   foreach($coupons as $coupon_key=>$coupon_val)
   {
    $propertyItem->setField("VALUE", $coupon_val['COUPON']);
   }
         
            break;

    }
  }
}
?>
```
Создаем ключ на странице: 
https://console.cloud.google.com/apis...​ 
и включаем Google Sheets & Google Drive, если еще не включены.

Весь тестовый проект: https://github.com/ivansamofal/google-sheets
