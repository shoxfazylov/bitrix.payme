<?
namespace Qsoft\Payme;

use Bitrix\Main\Entity;
use Bitrix\Main\Type;

class TransactionTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
    {
    	return 'qsoft_payme_transaction';
    }

    public static function getMap()
    {
    	return array(
	        'ID' => array(
		        'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
	        'CREATE_TIME' => array( // Время создания транзакции
	            'data_type' => 'string'
			),
	        'PERFORM_TIME' => array( //Время проведения транзакции
	            'data_type' => 'string'
    	    ),
			'PAYCOM_TIME' => array( //Время проведения транзакции
			    'data_type' => 'string'
			),
			'PAYCOM_DATETIME' => array( //Время проведения транзакции
			    'data_type' => 'string'
			),
	        'CANCEL_TIME' => array( //Время отмены транзакции
	            'data_type' => 'string'
    	    ),
	        'ORDER_ID' => array( //Идентификатор заказа в 1С-Битрикс
				'data_type' => 'integer',
				'required' => true
			),
	        'TID' => array( //Номер или идентификатор транзакции в системе мерчанта
				'data_type' => 'string'
			),
	        'STATE' => array( //Состояние транзакции
    	        'data_type' => 'string'
    	    ),
	        'REASON' => array( //Причина отмены транзакции
    	        'data_type' => 'string'
	        ),
    	);
    }

}
