<?php
CModule::AddAutoloadClasses(
    'qsoft.payme',
    array(
        'Qsoft\Payme\Config'       				=> 'lib/config.php',
    	'Qsoft\Payme\TransactionTable'			=> 'lib/entity/transaction.php',
    	'Qsoft\Payme\Transaction'   			=> 'lib/transaction.php',
    	'Qsoft\Payme\Api'       				=> 'lib/api.php',

    )
);