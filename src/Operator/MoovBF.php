<?php

namespace lab\Operator;

use lab\Service\ServiceImpl;

class MoovBF extends ServiceImpl {
    
    const CREDENTIALS = [
        "sandbox" => [
            "username" => 'BARKALAB',
            "password" => 'tr!3bB3$Xn56',
            "baseUrl" => 'https://196.28.245.227/tlcfzc_gw/api/gateway/3pp/transaction/process'
            ],
        "production" => [
            "username" => 'BARKALAB',
            "password" => '3qk@9iAGj78^',
            "baseUrl" => 'https://196.28.245.227/tlcfzc_gw_prod/mbs-gateway/gateway/3pp/transaction/process'
        ]
    ];


    public function __construct($platform = "production") {
        parent::__construct(self::CREDENTIALS[$platform]);
    }

}









?>