# 1C Bitrix и Яндекс Кассы, как сгенерировать/получить ссылку на оплату заказа?


Формирование происходит исходя из настроек платежной системы и сформированного заказа. Если открыть инспектор кода на последнем шаге оформления заказа, то можно увидеть, что там не ссылка на оплату, а форма, которая отправляет post-запрос в yandex, и он уже формирует страницу оплаты из переданных данных https://yadi.sk/i/NvXjQ74D6e_bag

Возможно, что и GET-параметры подойдут для запроса в https://demomoney.yandex.ru/eshop.xml

Тогда ссылка будет иметь вид https://demomoney.yandex.ru/eshop.xml?ShopID=сайт&scid=секретный код&customerNumber=номер клиента&orderNumber=номер заказа&Sum=сумма&paymentType=PC&cms_name=1C-Bitrix&BX_HANDLER=YANDEX&BX_PAYSYSTEM_CODE=3

Или можно попробовать в письмо прислать форму с оплатой. Генерируется она таким образом (код взят из стандартного компонента bitrix:sale.order.ajax):

```shell
<?
use Bitrix\Main,
   Bitrix\Main\Loader,
   Bitrix\Sale,
   Bitrix\Sale\Order,
   Bitrix\Sale\PaySystem,
   Bitrix\Sale\Payment;
   Loader::includeModule("sale");
   $registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
   $orderClassName = $registry->getOrderClassName();
   $order = $orderClassName::loadByAccountNumber(10);// id заказа
   if ($order->isAllowPay()) {
     $paymentCollection = $order->getPaymentCollection();
     foreach ($paymentCollection as $payment) {
        $arResult["PAYMENT"][$payment->getId()] = $payment->getFieldValues();
        if (intval($payment->getPaymentSystemId()) > 0 && !$payment->isPaid()) {
            $paySystemService = PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
            if (!empty($paySystemService)) {
                $arPaySysAction = $paySystemService->getFieldsValues();
                if ($paySystemService->getField('NEW_WINDOW') === 'N' || $paySystemService->getField('ID') == PaySystem\Manager::getInnerPaySystemId()){
                    $initResult = $paySystemService->initiatePay($payment, null, PaySystem\BaseServiceHandler::STRING);
                    if ($initResult->isSuccess())
                        $arPaySysAction['BUFFERED_OUTPUT'] = $initResult->getTemplate(); // получаем форму оплаты из обработчика
                    else
                        $arPaySysAction["ERROR"] = $initResult->getErrorMessages();
                }
            }
        }
    }
}
?>
```
Проще всего отправить клиенту на почту ссылку на страницу с заказом ?ORDER_ID=id заказа откуда он уже сможет в один клик перейти на страницу оплаты.
