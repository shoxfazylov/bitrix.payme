<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$GLOBALS["APPLICATION"]->RestartBuffer();
\CModule::IncludeModule('qsoft.payme');
use \Qsoft\Payme\Api;

$api = new Api($arParams);