<?php

use lab\Operator\MoovBF;

require(dirname(__DIR__, 1).'/vendor/autoload.php');


$oper = new MoovBF('sandbox');


// COLLECTIONS
print_r($oper->payment(['amount' => 10, 'destination' => '22660565103', 'request-id' => 'bash00']));
// print_r($oper->paymentStatus('54797ff9-6a41-4586-b5ae-e861eee818e4'));

// TRANSFERT
// print_r($oper->transfert(['amount' => 10, 'destination' => '22660565103', 'request-id' => 'bash001']));
// print_r($oper->transactionStatus('bash001'));








?>