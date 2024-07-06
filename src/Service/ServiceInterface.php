<?php

namespace lab\Service;

interface ServiceInterface {
    public function payment(array $data);
    public function transfert(array $data);
    public function accountStatus(string $subscriberPhonenumber);
    public function transactionStatus(string $requestId);
    public function xtransfert(array $data);
    public function subscriberRegistration(string $requestId, array $extendedData);
    public function autoDebit(array $data);
}




?>