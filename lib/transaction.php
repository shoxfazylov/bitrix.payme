<?
namespace Qsoft\Payme;

class Transaction extends TransactionTable
{
	public static function getList($params)
	{
		$t = parent::getList($params)->fetchAll();
		return $t;
	}

    public static function add($fields)
    {
        $t = parent::getList([
            'select' => ['*'],
            'filter' => ['ORDER_ID'=>$fields['ORDER_ID']]
        ])->fetch();
        if(!$t){
        	$result = parent::add($fields);
            if (!$result->isSuccess())
            {
            	$errors = $result->getErrorMessages();
            	@file_put_contents(__DIR__ . '/errors.log', print_r($errors, true));
            }
        }
    }

    public static function getByOrderId($orderId)
    {
    	$s = parent::getList([
    	    'select' => ['*'],
    	    'filter' => ['=ORDER_ID'=>$orderId]
    	])->fetch();
    	if(!empty($s['ID'])) return $s;
    	return [];
    }

    public static function getByOrderTid($tid)
    {
    	$t = parent::getList([
    	    'select' => ['*'],
    		'filter' => ['=TID'=>$tid]
    	])->fetch();
    	if(!empty($t['ID'])) return $t;
    	return [];
    }

    public static function setFieldByOrderId($code = '', $value = '', $orderId)
    {
        $t = parent::getList([
            'select' => ['ID'],
            'filter' => ['ORDER_ID'=>$orderId]
        ])->fetch();
        if($t['ID']){
            parent::update($t['ID'], [$code => $value]);
        }
    }

    public static function setFieldByOrderTid($code = '', $value = '', $tid)
    {
    	$t = parent::getList([
    		'select' => ['ID'],
    		'filter' => ['TID'=>$tid]
    	])->fetch();
    	if($t['ID']){
    		parent::update($t['ID'], [$code => $value]);
    	}
    }


}