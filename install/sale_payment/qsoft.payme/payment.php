<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
$ORDER_ID = isset($arResult['ORDER_ID'])?:$_GET['ORDER_ID'];
$order_info = \CSaleOrder::GetByID(filter_var($ORDER_ID, FILTER_SANITIZE_NUMBER_INT));
if( $order_info['CURRENCY'] == 'USD') $t_currency = 840;
else if( $order_info['CURRENCY'] == 'RUB') $t_currency = 643;
else if( $order_info['CURRENCY'] == 'EUR') $t_currency = 978;
else $t_currency = 860;

$products = array("shipping" => array("title" => ""));
$dbBasketItems = CSaleBasket::GetList(array(),array("ORDER_ID" => $_REQUEST['ORDER_ID']));
while ($arItems = $dbBasketItems->Fetch()){
	$products['items'][] = array(
		"title" => $arItems["NAME"],
		"price" => intval($arItems["PRICE"])*100,
		"count" => intval($arItems["QUANTITY"])
	);
}
$detail = base64_encode(json_encode($products));
$action = CSalePaySystemAction::GetParamValue("MERCHANT_IS_TEST") == 'Y' ? 'https://test.paycom.uz' : 'https://checkout.paycom.uz' ;
?>

<form method="POST" action="<?=$action?>" target="_blank">

    <input type="hidden" name="lang" value="ru" />
    <input type="hidden" name="callback_timeout" value="0" />
    <input type="hidden" name="callback" value="<?=CSalePaySystemAction::GetParamValue("CALLBACK");?>" />
    <input type="hidden" name="merchant" value="<?=CSalePaySystemAction::GetParamValue("MERCHANT");?>" />

    <input type="hidden" name="amount" value="<?=intval(CSalePaySystemAction::GetParamValue("AMOUNT"))*100;?>" />
    <input type="hidden" name="account[<?=CSalePaySystemAction::GetParamValue("ACCOUNT_ID");?>]" value="<?=CSalePaySystemAction::GetParamValue("ORDER_ID");?>" />
    <input type="hidden" name="detail" value="<?=$detail;?>" />
    <input type="hidden" name="currency" value="<?=$t_currency;?>" />

    <button class="payme_button" type="submit"><?=GetMessage('PAYME_BUTTON')?></button>

</form>