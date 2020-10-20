<?
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

Class qsoft_payme extends CModule
{
	const MODULE_ID = 'qsoft.payme';
	var $MODULE_ID = 'qsoft.payme';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct()
	{
	    $arModuleVersion = array();
	    include(dirname(__FILE__)."/version.php");
	    $this->MODULE_VERSION = $arModuleVersion["VERSION"];
	    $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
	    $this->MODULE_NAME = Loc::getMessage($this->MODULE_ID."_MODULE_NAME");
	    $this->MODULE_DESCRIPTION = Loc::getMessage($this->MODULE_ID."_MODULE_DESC");

	    $this->PARTNER_NAME = Loc::getMessage($this->MODULE_ID."_PARTNER_NAME");
	    $this->PARTNER_URI = Loc::getMessage($this->MODULE_ID."_PARTNER_URI");
	}

	function InstallDB()
	{
	    global $DB, $APPLICATION;
	    $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/db/'.strtolower($DB->type).'/install.sql');
	    return true;
	}

	function UnInstallDB()
	{
	    global $DB, $APPLICATION;
	    $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/db/'.strtolower($DB->type).'/uninstall.sql');
	    return true;
	}

	function InstallFiles()
	{
	    CopyDirFiles(
	        $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/sale_payment",
	        $_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/include/sale_payment/",
	        true,
	        true
	        );
	    return true;
	}

	function UnInstallFiles()
	{
	    DeleteDirFilesEx("/bitrix/php_interface/include/sale_payment/" . $this->MODULE_ID);
	    return true;
	}

	function DoInstall()
	{
	    global $APPLICATION;
	    $this->InstallFiles();
	    $this->InstallDB();
		RegisterModule($this->MODULE_ID);
	}
	function DoUninstall()
	{
	    global $APPLICATION;
	    $this->UnInstallFiles();
	    $this->UnInstallDB();
		UnRegisterModule($this->MODULE_ID);
	}


}
