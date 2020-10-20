<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/payment.php"));

$psTitle = GetMessage("PAYME_TITLE");
$psDescription = GetMessage("PAYME_DESCR");
$arPSCorrespondence = array(
		"MERCHANT_IS_TEST" => array(
				"NAME" => GetMessage("PAYME_MERCHANT_IS_TEST"),
				"DESCR" => GetMessage("PAYME_MERCHANT_IS_TEST_DESCR"),
				"SORT" => 1,
				"INPUT" => array(
    		        'TYPE' => 'Y/N'
    		    )
		),
		"MERCHANT" => array(
				"NAME" => GetMessage("PAYME_MERCHANT"),
				"DESCR" => GetMessage("PAYME_MERCHANT_DESCR"),
				"SORT" => 2
		),
		'KEY' => array(
				'NAME'  => GetMessage('PAYME_SECURE_KEY'),
				'DESCR' => GetMessage('PAYME_SECURE_KEY'),
				'SORT'  => 3,
		),
		'KEY_TEST' => array(
				'NAME'  => GetMessage('PAYME_SECURE_KEY_TEST'),
				'DESCR' => GetMessage('PAYME_SECURE_KEY_TEST_DEF'),
				'SORT'  => 4,
		),
		"ACCOUNT_ID" => array(
				"NAME" => GetMessage("PAYME_MERCHANT_ACCOUNT_ID"),
				"DESCR" => GetMessage("PAYME_MERCHANT_ACCOUNT_ID_DESCR"),
				"SORT" => 6
		),
		"CALLBACK" => array(
				"NAME" => GetMessage("PAYME_MERCHANT_CALLBACK"),
				"DESCR" => GetMessage("PAYME_MERCHANT_CALLBACK_DESCR"),
				"SORT" => 7
		),
		'PRINT' => array(
				'NAME'  => GetMessage('PAYME_PRINT_CHECK'),
				'DESCR' => GetMessage('PAYME_PRINT_CHECK_DESCR'),
				"SORT" => 8,
				"INPUT" => array(
					'TYPE' => 'Y/N'
				)
		),
		"AMOUNT" => array(
				"NAME" => GetMessage("PAYME_MERCHANT_AMOUNT"),
				"DESCR" => GetMessage("PAYME_MERCHANT_AMOUNT_DESCR"),
				"SORT" => 9
		),
		"ORDER_ID" => array(
				"NAME" => GetMessage("PAYME_MERCHANT_ORDER_ID"),
				"DESCR" => GetMessage("PAYME_MERCHANT_ORDER_ID_DESCR"),
				"SORT" => 10
		)
	);