<?
namespace Qsoft\Payme;

use \Qsoft\Payme\Config;
use \Qsoft\Payme\Transaction;

\CModule::IncludeModule('sale');
class Api {

	private $errorInfo = "";
	private $errorCode = 0;

	private $result;
	private $input;
	private $account;
	private $headers;
	private $paymentSystemParams;
	private $paymentSystemId;

	private $responceType = 0;
	private $lastTransaction;



	public function __construct($params) {
	    $this->getallheaders();
	    $this->setInput(file_get_contents("php://input"));
		$this->paymentSystemId = $params['PAY_SYSTEM_ID_NEW'];
		$this->lastTransaction = [];
		$this->getPaymentParams($this->paymentSystemId);
		$this->Scenarios();
	}

	public function Scenarios() {

	    if ( (!isset($this->input)) || empty($this->input) ) {
	        $this->setError(32700);
	    }else{
        		preg_match('/^Basic\s+(.*)$/i', $this->headers['Authorization'], $user_pass);
        		list($user,$pass) = explode(':',base64_decode($user_pass[1]));
        		$password = $this->paymentSystemParams['KEY']['VALUE'];
        		if($this->paymentSystemParams['MERCHANT_IS_TEST']['VALUE'] == 'Y') $password = $this->paymentSystemParams['KEY_TEST']['VALUE'];
        		if($user != 'Paycom' || $pass != $password){
        		    $this->setError(32504);
        		}

        	if($this->errorCode == 0){
	    	    $methodName = $this->input['method'];
	    	    $this->$methodName();
        	}
	    }
		$this->GenerateResponse();
	}


	public function CheckPerformTransaction() {

	    if(isset($this->input['params']['account'][$this->account])){
	    	$order_id = $this->input['params']['account'][$this->account];

	    	// Поиск заказа по order_id
	    	$order = \CSaleOrder::GetByID(filter_var($order_id, FILTER_SANITIZE_NUMBER_INT));

	    	// Заказ не найден
	    	if (! $order ) {
	    		$this->setError(31050, $this->account);

	    	// Заказ найден
	    	} else {
	    		// Поиск транзакции по order_id
	    		$this->getLastTransactionForOrder($order_id);

	    		// Транзакция нет
	    		if (! $this->lastTransaction ) {

	    			// Проверка состояния заказа
	    			if ($order['PAYED']=='Y' ) {
	    				$this->setError(31052, $this->account);

	    			// Сверка суммы заказа
	    			} else  if (intval($order["PRICE"])*100 != $this->input['params']['amount']) {
	    				$this->setError(31001, $this->account);

	    			// Allow true
	    			} else {
	    				$this->responceType = 1;
	    			}

	    			// Транзакция существует
	    		} else {
	    			$this->setError(31051, $this->account);
	    		}
	    	}
	    }else{
	    	$this->setError(31050, $this->account);
	    }
	}



	public function CreateTransaction() {

		if(isset($this->input['params']['account'][$this->account])){
			$order_id = $this->input['params']['account'][$this->account];

			// Поиск заказа по order_id
			$order = \CSaleOrder::GetByID(filter_var($order_id, FILTER_SANITIZE_NUMBER_INT));

			// Поиск транзакции по id
			$this->getLastTransaction($this->input['params']['id']);

			// Существует транзакция
			if ($this->lastTransaction) {
				$paycom_time_integer = $this->datetime2timestamp($this->lastTransaction['CREATE_TIME']) *1000;
				$paycom_time_integer = $paycom_time_integer+43200000;

				// Проверка состояния заказа
				if ($order['PAYED']=='Y' ) {
					$this->setError(31052, $this->account);

				// Проверка состояния транзакции
				} else if ($this->lastTransaction['STATE'] != 1){
					$this->setError(31008, $this->account);

					// Проверка времени создания транзакции
				} else if ($paycom_time_integer <= $this->timestamp2milliseconds(time())){

					// Отменит reason = 4
					Transaction::setFieldByOrderTid('STATE', -1, $this->lastTransaction['TID']);
					Transaction::setFieldByOrderTid('REASON', 4, $this->lastTransaction['TID']);
					Transaction::setFieldByOrderTid('CANCEL_TIME', $this->timestamp2datetime(strtotime("now")), $this->lastTransaction['TID']);

					\CSaleOrder::PayOrder   ($this->lastTransaction['ORDER_ID'], 'N');
					\CSaleOrder::StatusOrder($this->lastTransaction['ORDER_ID'], "N");
					\CSaleOrder::CancelOrder($this->lastTransaction['ORDER_ID'], "Y", '4');

					$this->getLastTransaction($this->input['params']['id']);
					$this->responceType = 2;

					// Всё OK
				} else {
					$this->responceType = 2;
				}

			// Транзакция нет
			} else {
				// Заказ не найден
				if (! $order ) {
					$this->setError(31050, $this->account);

				// Заказ найден
				} else {
					// Проверка состояния заказа
					if ($order['PAYED']=='Y' ) {
						$this->setError(31052, $this->account);

					// Сверка суммы заказа
					} else  if ( intval($order["PRICE"])*100 != $this->input['params']['amount'] ) {

						$this->setError(31001, $this->account);

					// Запись транзакцию state = 1
					} else {
					    // Поиск транзакции по order_id
					    $this->getLastTransactionForOrder($order_id);

						// Транзакция нет
					    if (empty($this->lastTransaction)) {
							Transaction::add([
								'ORDER_ID' => $order_id,
								'TID' => $this->input['params']['id'],
								'AMOUNT' => intval($order["PRICE"])*100,
								'STATE' => 1,
							    'CREATE_TIME' => $this->timestamp2datetime(strtotime("now")),
							    'PAYCOM_TIME' => $this->timestamp2datetime($this->input['params']['time']),
							    'PAYCOM_DATETIME'=> $this->timestamp2datetime($this->input['params']['time'])
							]);
							$this->getLastTransactionForOrder($order_id);
							$this->responceType = 2;

						// Существует транзакция
						} else {
							$this->setError(31051, $this->account);
						}
					}
				}
			}
		}else{
			$this->setError(31050, $this->account);
		}
	}

	public function CheckTransaction() {

		// Поиск транзакции по id
		$this->getLastTransaction($this->input['params']['id']);

		// Существует транзакция
		if (!empty($this->lastTransaction)) {
			$this->responceType = 2;

		// Транзакция нет
		} else {
			$this->setError(31003);
		}
	}

	public function PerformTransaction() {

		// Поиск транзакции по id
		$this->getLastTransaction($this->input['params']['id']);

		// Существует транзакция
		if ( $this->lastTransaction ) {

			// Проверка состояние транзакцие
			if ($this->lastTransaction['STATE'] == 1) {
				$paycom_time_integer = $this->datetime2timestamp($this->lastTransaction['CREATE_TIME']) *1000;
				$paycom_time_integer = $paycom_time_integer + 43200000;

				// Проверка времени создания транзакции
				if( $paycom_time_integer <= $this->timestamp2milliseconds(time()) ) {
					// Отменит reason = 4
					Transaction::setFieldByOrderTid('STATE', -1, $this->lastTransaction['TID']);
					Transaction::setFieldByOrderTid('REASON', 4, $this->lastTransaction['TID']);
					Transaction::setFieldByOrderTid('CANCEL_TIME', $this->timestamp2datetime(strtotime("now")), $this->lastTransaction['TID']);

					\CSaleOrder::PayOrder   ($this->lastTransaction['ORDER_ID'], 'N');
					\CSaleOrder::StatusOrder($this->lastTransaction['ORDER_ID'], "N");
					\CSaleOrder::CancelOrder($this->lastTransaction['ORDER_ID'], "Y", '4');

					// Всё Ok
				} else {
					// Оплата
					Transaction::setFieldByOrderTid('STATE', 2, $this->lastTransaction['TID']);
					Transaction::setFieldByOrderTid('PERFORM_TIME', $this->timestamp2datetime(strtotime("now")), $this->lastTransaction['TID']);

					\CSaleOrder::PayOrder   ($this->lastTransaction['ORDER_ID'], 'Y');
					\CSaleOrder::StatusOrder($this->lastTransaction['ORDER_ID'], 'P');
				}

				$this->responceType = 2;
				$this->getLastTransaction($this->input['params']['id']);

			// Cостояние не 1
			} else {

				// Проверка состояние транзакцие
				if ($this->lastTransaction['STATE'] == 2) {
					$this->responceType = 2;

				// Cостояние не 2
				} else {
					$this->setError(31008);
				}
			}

		// Транзакция нет
		} else {

			$this->setError(31003);
		}
	}

	public function CancelTransaction() {
		// Поиск транзакции по id
		$this->getLastTransaction($this->input['params']['id']);

		// Существует транзакция
		if ($this->lastTransaction) {
			$reasonCencel = filter_var($this->input['params']['reason'], FILTER_SANITIZE_NUMBER_INT);

			// Проверка состояние транзакцие
			if ($this->lastTransaction['STATE'] == 1) {
				// Отменит state = -1
				Transaction::setFieldByOrderTid('STATE', -1, $this->lastTransaction['TID']);
				Transaction::setFieldByOrderTid('REASON', $reasonCencel, $this->lastTransaction['TID']);
				Transaction::setFieldByOrderTid('CANCEL_TIME', $this->timestamp2datetime(strtotime("now")), $this->lastTransaction['TID']);

				\CSaleOrder::PayOrder   ($this->lastTransaction['ORDER_ID'], 'N');
				\CSaleOrder::StatusOrder($this->lastTransaction['ORDER_ID'], "N");
				\CSaleOrder::CancelOrder($this->lastTransaction['ORDER_ID'], "Y", $reasonCencel);

			// Cостояние 2
			} else if ($this->lastTransaction['STATE'] == 2) {

				// Отменит state = -2
				Transaction::setFieldByOrderTid('STATE', -2, $this->lastTransaction['TID']);
				Transaction::setFieldByOrderTid('REASON', $reasonCencel, $this->lastTransaction['TID']);
				Transaction::setFieldByOrderTid('CANCEL_TIME', $this->timestamp2datetime(strtotime("now")), $this->lastTransaction['TID']);

				\CSaleOrder::PayOrder   ($this->lastTransaction['ORDER_ID'], 'N');
				\CSaleOrder::StatusOrder($this->lastTransaction['ORDER_ID'], "N");
				\CSaleOrder::CancelOrder($this->lastTransaction['ORDER_ID'], "Y", $reasonCencel);

				// Cостояние
			} else {
				// Ничего не надо делать
			}

			$this->responceType = 2;
			$this->getLastTransaction($this->input['params']['id']);

		// Транзакция нет
		} else {
			$this->setError(31003);
		}
	}

	public function ChangePassword() {
		$this->paymentSystemParams['KEY']['VALUE'] = $this->input['params']['password'];
		if($this->paymentSystemParams['MERCHANT_IS_TEST']['VALUE'] == 'Y') $this->paymentSystemParams['KEY_TEST']['VALUE'] = $this->input['params']['password'];

		$updRes = \CSalePaySystemAction::update(
			$this->paymentSystemId,
			array("PARAMS" => \CSalePaySystemAction::SerializeParams($this->paymentSystemParams))
		);

		$this->responceType = 3;
	}

	public function GetStatement() {
		$transactions  = [];
		$dbPaySystem = Transaction::getList([
			'filter'=>[
				'>=PAYCOM_DATETIME' => $this->timestamp2datetime($this->input['params']['from']),
				'<=PAYCOM_DATETIME' => $this->timestamp2datetime($this->input['params']['to'])
			]
		]);
		foreach ($dbPaySystem as $row){
			$transactions[] = [
				"id"		   => $row["TID"],
				"time"		   => $row['PAYCOM_TIME'],
				"amount"	   => $row["AMOUNT"],
				"account"	   => array($this->account => $row["ORDER_ID"]),
				"create_time"  => (is_null($row['CREATE_TIME']) ? null: $this->datetime2timestamp( $row['CREATE_TIME']) ) ,
				"perform_time" => (is_null($row['PERFORM_TIME'])? null: $this->datetime2timestamp( $row['PERFORM_TIME'])) ,
				"cancel_time"  => (is_null($row['CANCEL_TIME']) ? null: $this->datetime2timestamp( $row['CANCEL_TIME']) ) ,
				"transaction"  => $row["ORDER_ID"],
				"state"		   => (int) $row['STATE'],
				"reason"	   => (is_null($row['REASON'])?null:(int) $row['REASON']) ,
				"receivers"	=> null
			];
		}
		$this->result['result'] = ['transactions'=>$transactions];
	}

	protected function getLastTransaction ($transaction_id ) {
	    $this->lastTransaction = Transaction::getByOrderTid($transaction_id);
	}

	protected function getLastTransactionForOrder($order_id ) {
	    $this->lastTransaction = Transaction::getByOrderId($order_id);
	}

	protected function setError ($code, $data = null){
		if(empty($this->errorCode)) $this->errorCode = $code;

		if ($data != null) $this->errorInfo = $data;

		if ($code != 0) {
			$this->result['result'] = null;
		}
	}

	protected function GenerateResponse() {

		if ($this->errorCode == 0) {

			if ($this->responceType == 1) {

				$this->result['result'] = ['allow' => true];

			} else if ($this->responceType == 2) {

				$this->result['result'] = [
				    "create_time"	=> $this->datetime2timestamp($this->lastTransaction['CREATE_TIME']) *1000,
				    "perform_time"  => $this->datetime2timestamp($this->lastTransaction['PERFORM_TIME']) *1000,
				    "cancel_time"   => $this->datetime2timestamp($this->lastTransaction['CANCEL_TIME']) *1000,
					"transaction"	=> $this->lastTransaction['ORDER_ID'],
					"state"			=> (int)$this->lastTransaction['STATE'],
					"reason"		=> (is_null($this->lastTransaction['REASON'])?null:(int)$this->lastTransaction['REASON'])
				];

			} else if ($this->responceType==3) {

				$this->result['result'] = ['success' => true];
			}

		} else {
			$this->result['error'] = Config::getError((int)$this->errorCode, $this->errorInfo);
		}
		$this->result['id'] = $this->input['id'];

		header('Content-type: application/json; charset=utf-8');
		die(json_encode($this->result));
	}

	public function timestamp2datetime($timestamp){
		// if as milliseconds, convert to seconds
		if (strlen((string)$timestamp) == 13) {
			$timestamp = $this->timestamp2seconds($timestamp);
		}

		// convert to datetime string
		return date('Y-m-d H:i:s', $timestamp);
	}

	public function timestamp2seconds($timestamp) {
		// is it already as seconds
		if (strlen((string)$timestamp) == 10) {
			return $timestamp;
		}

		return floor(1 * $timestamp / 1000);
	}

	public function timestamp2milliseconds($timestamp) {
		// is it already as milliseconds
		if (strlen((string)$timestamp) == 13) {
			return $timestamp;
		}

		return $timestamp * 1000;
	}

	public function datetime2timestamp($datetime) {

		if ($datetime) {

			return strtotime($datetime);
		}

		return $datetime;
	}

	public function getinput() {
		return $this->input;
	}

	public function setInput($input) {
		$this->input = json_decode($input, true);
	}

	public function getallheaders()
	{
	    $headers = [];
	    foreach ($_SERVER as $name => $value) {
	        if (substr($name, 0, 5) == 'HTTP_') {
	            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
	        }
	    }
	    if(!isset($headers['Authorization']) && (isset($_SERVER['REDIRECT_REMOTE_USER']) || isset($_SERVER['REMOTE_USER']))){
	       $headers['Authorization'] = $_SERVER['REMOTE_USER']?:$_SERVER['REDIRECT_REMOTE_USER'];
	    }
	    $this->headers = $headers;
	}

	public function getPaymentParams($id)
	{
	    $dbPaySystem = \CSalePaySystemAction::GetList(
	        Array("SORT"=>"ASC"),
	        Array("ACTIVE" => "Y", "ID" => $id),
	        false,
	        false,
	        array("ID", "ACTION_FILE", "PARAMS")
	        );
	    if($ps = $dbPaySystem->Fetch()) {
	        $this->paymentSystemParams = \CSalePaySystemAction::UnSerializeParams($ps["PARAMS"]);
	        $this->account = strtolower($this->paymentSystemParams['ACCOUNT_ID']['VALUE']);
	    }
	}
}