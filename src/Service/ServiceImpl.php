<?php

namespace lab\Service;

use lab\Logger\LoggerManager;
use GuzzleHttp\Client;
use Exception;
use InvalidArgumentException;

class ServiceImpl implements ServiceInterface {

    private $logger;
    private $errorLogger;
    protected $credentials = [];
    private $baseUrl;
    
    public function __construct($credentials = []) {
        $this->baseUrl  = $credentials['baseUrl'];
        $this->credentials = $credentials;
        $this->logger = LoggerManager::getLogger('app');
        $this->errorLogger = LoggerManager::getErrorLogger();
    }
    
    private function requestHandler($method = 'GET', $body = [], $endpoint = "", $headers = [], $decode = true){
        try{
            $client = new Client(['base_uri' => $this->baseUrl, 'headers' => $headers]);
            $body = ($body) ? ["json" => $body] : [];
            $response = $client->request($method, $endpoint, $body);
            $contents = $response->getBody()->getContents();
            // Décoder le contenu si nécessaire
            $decodedContents = ($decode) ? json_decode($contents, true) : $contents;
            // Récupérer le statut HTTP de la réponse
            $statusCode = $response->getStatusCode();
            // Retourner à la fois le contenu décoder et le statut HTTP
            return (Object)['statusCode' => $statusCode, 'content' => $decodedContents];
        }catch (\GuzzleHttp\Exception\RequestException $e) {
            // Capturer les exceptions de Guzzle et les relancer avec un message précis
            if ($e->hasResponse()) {
                // Récupérer le statut HTTP et le message d'erreur de la réponse
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $errorMessage = $response->getReasonPhrase();
                $contents = $response->getBody()->getContents();
                // Traiter en fonction du statut HTTP
                if ($statusCode >= 400 && $statusCode < 500) {
                    // Erreur client : traiter le contenu ou ignorer selon les besoins
                    return (Object)['statusCode' => $statusCode, 'content' => $contents];
                } elseif ($statusCode >= 500 && $statusCode < 600) {
                    // Erreur serveur : gérer comme une exception
                    throw new Exception("Server Error: $contents");
                } else {
                    // Autres cas non gérés : gérer comme une exception ou ignorer selon les besoins
                    throw new Exception("Unexpected HTTP status code: $statusCode");
                }
            } else {
                // Erreur de connexion réseau ou autre erreur Guzzle
                throw new Exception("Request failed: " . $e->getMessage());
            }
            throw new Exception("Error in request: " . $e->getMessage());
        }catch(Exception $e){
            throw new Exception($e);
        }
    }

    private function getBasicAuth(){
        $username = $this->credentials["username"];
        $password = $this->credentials["password"];
        return 'Basic '.base64_encode("$username:$password");
    }


    public function payment(array $data = []) {
        try {  
            // Vérifier si toutes les clés nécessaires sont présentes dans $data
            $requiredKeys = ['amount', 'request-id', 'destination'];
            // Vérifier les clés manquantes
            $missingKeys = array_diff($requiredKeys, array_keys($data));
            // Si des clés manquantes sont trouvées, générer une exception
            if (!empty($missingKeys)) {
                $missingKeysStr = implode(', ', $missingKeys);
                throw new InvalidArgumentException("Missing required key(s): $missingKeysStr in \$data array.");
            }
            $this->logger->info('Payment method called');
            // Implémentation de la méthode payment
            $commandId = 'mror-transaction-ussd';
            $headers    = [
                'Content-Type'      =>  'application/json',
                'Authorization'     =>  self::getBasicAuth(),
                'command-id'        =>  $commandId,
            ];
            $body = array (
                "request-id"    => $data['request-id'],
                "destination"   => $data['destination'], 
                "amount"        => $data['amount'],
                "remarks"       => $data['destination'].'|'.time().'|'.$data['amount'],
                "message"       => "PAYMENT OF ".$data['amount']." TO ABC PLEASE CONFIRM WITH PIN",
                "extended-data" => [
                    "ext2"          => "CUSTOM STRING",
                    "custommessge"  => "Payment for XXXX"
                ]
            );
            $response = self::requestHandler('POST', $body, '', $headers);
            return (Array)$response;
        } catch (Exception $e) {
            $this->errorLogger->error('Error in payment method: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function transfert(array $data = []) {
        try {  
            // Vérifier si toutes les clés nécessaires sont présentes dans $data
            $requiredKeys = ['amount', 'request-id', 'destination'];
            // Vérifier les clés manquantes
            $missingKeys = array_diff($requiredKeys, array_keys($data));
            // Si des clés manquantes sont trouvées, générer une exception
            if (!empty($missingKeys)) {
                $missingKeysStr = implode(', ', $missingKeys);
                throw new InvalidArgumentException("Missing required key(s): $missingKeysStr in \$data array.");
            }
            $this->logger->info('Transfert method called');
            // Implémentation de la méthode payment
            $commandId = 'transfer-api-transaction';
            $headers    = [
                'Content-Type'      =>  'application/json',
                'Authorization'     =>  self::getBasicAuth(),
                'command-id'        =>  $commandId,
            ];
            $body = array (
                "request-id"    => $data['request-id'],
                "destination"   => $data['destination'], 
                "amount"        => $data['amount'],
                "remarks"       => $data['destination'].'|'.time().'|'.$data['amount'],
                "message"       => "TRANSFERT OF ".$data['amount']." TO ABC PLEASE CONFIRM WITH PIN",
                "extended-data" => [
                    "ext2"          => "CUSTOM STRING",
                    "custommessge"  => "Transfert for XXXX"
                ]
            );
            $response = self::requestHandler('POST', $body, '', $headers);
            return (Array)$response;
        } catch (Exception $e) {
            $this->errorLogger->error('Error in Transfert method: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function accountStatus(string $subscriberPhonenumber = null){
        try{
            if (!($subscriberPhonenumber)) {
                throw new InvalidArgumentException("Missing required key(s): subscriberPhonenumber in function args.");
            }
            $this->logger->info('AccountStatus method called');
            // Implémentation de la méthode payment
            $commandId = 'process-check-subscriber';
            $headers    = [
                'Content-Type'      =>  'application/json',
                'Authorization'     =>  self::getBasicAuth(),
                'command-id'        =>  $commandId,
            ];
            $body = array (
                "destination" => $subscriberPhonenumber, 
                "request-id" => "check_$subscriberPhonenumber".'|'.time()
            );
            $response = self::requestHandler('POST', $body, '', $headers);
            return (Array)$response;
        } catch (Exception $e) {
            $this->errorLogger->error('Error in AccountStatus method: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function transactionStatus(string $requestId = null){
        try{
            if (!($requestId)) {
                throw new InvalidArgumentException("Missing required key(s): requestId in function args.");
            }
            $this->logger->info('TransactionStatus method called');
            // Implémentation de la méthode payment
            $commandId = 'process-check-transaction';
            $headers    = [
                'Content-Type'      =>  'application/json',
                'Authorization'     =>  self::getBasicAuth(),
                'command-id'        =>  $commandId,
            ];
            $body = array (
                "request-id" => $requestId
            );
            $response = self::requestHandler('POST', $body, '', $headers);
            return (Array)$response;
        }catch (Exception $e) {
            $this->errorLogger->error('Error in transactionStatus method: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function xtransfert(array $data = []){
        try {  
            // Vérifier si toutes les clés nécessaires sont présentes dans $data
            $requiredKeys = ['amount', 'request-id', 'destination'];
            // Vérifier les clés manquantes
            $missingKeys = array_diff($requiredKeys, array_keys($data));
            // Si des clés manquantes sont trouvées, générer une exception
            if (!empty($missingKeys)) {
                $missingKeysStr = implode(', ', $missingKeys);
                throw new InvalidArgumentException("Missing required key(s): $missingKeysStr in \$data array.");
            }
            $this->logger->info('Xtransfert method called');
            // Implémentation de la méthode payment
            $commandId = 'xcash-api-transaction';
            $headers    = [
                'Content-Type'      =>  'application/json',
                'Authorization'     =>  self::getBasicAuth(),
                'command-id'        =>  $commandId,
            ];
            $body = array (
                "request-id"    => $data['request-id'],
                "destination"   => $data['destination'], 
                "amount"        => $data['amount'],
                "remarks"       => $data['destination'].'|'.time().'|'.$data['amount'],
                "extended-data" => [
                    "ext2"          => "CUSTOM STRING",
                    "custommessge"  => "Cross Transfert for XXXX"
                ]
            );
            $response = self::requestHandler('POST', $body, '', $headers);
            return (Array)$response;
        } catch (Exception $e) {
            $this->errorLogger->error('Error in Xtransfert method: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function subscriberRegistration(string $requestId= null, array $extendedData = []){
        try {  
            // Vérifier si toutes les clés nécessaires sont présentes dans $data
            if (!($requestId)) {
                throw new InvalidArgumentException("Missing required key(s): requestId in function args.");
            }
            $this->logger->info('SubscriberRegistration method called');
            // Implémentation de la méthode payment
            $commandId = 'subscriber-registration';
            $headers    = [
                'Content-Type'      =>  'application/json',
                'Authorization'     =>  self::getBasicAuth(),
                'command-id'        =>  $commandId,
            ];
            $body = array (
                "request-id"    => $requestId,
                "extended-data" => [
                    "nickname" => $extendedData['nickname'] ?? "string",
                    "msisdn" => $extendedData['msisdn'] ?? "string",
                    "lastname" => $extendedData['lastname'] ?? "string",
                    "firstname" => $extendedData['firstname'] ?? "string",
                    "secondname" => $extendedData['secondname'] ?? "string",
                    "idnumber" => $extendedData['idnumber'] ?? "string",
                    "iddescription" => $extendedData['iddescription'] ?? "string",
                    "idexpirydate" => $extendedData['idexpirydate'] ?? "string",
                    "tinnumber" => $extendedData['tinnumber'] ?? "string",
                    "gender" => $extendedData['gender'] ?? "string",
                    "nationality" => $extendedData['nationality'] ?? "string",
                    "dateofbirth" => $extendedData['dateofbirth'] ?? "string",
                    "placeofbirth" => $extendedData['placeofbirth'] ?? "string",
                    "company" => $extendedData['company'] ?? "string",
                    "profession" => $extendedData['profession'] ?? "string",
                    "businessname" => $extendedData['businessname'] ?? "string",
                    "city" => $extendedData['city'] ?? "string",
                    "streetname" => $extendedData['streetname'] ?? "string",
                    "region" => $extendedData['region'] ?? "string",
                    "country" => $extendedData['country'] ?? "string",
                    "emailaddress" => $extendedData['emailaddress'] ?? "string",
                    "alternatenumber" => $extendedData['alternatenumber'] ?? "string",
                    "accounttype" => $extendedData['accounttype'] ?? "string"
                ]
            );
            $response = self::requestHandler('POST', $body, '', $headers);
            return (Array)$response;
        } catch (Exception $e) {
            $this->errorLogger->error('Error in SubscriberRegistration method: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function autoDebit(array $data = []){
        try {  
            // Vérifier si toutes les clés nécessaires sont présentes dans $data
            $requiredKeys = ['amount', 'request-id', 'destination'];
            // Vérifier les clés manquantes
            $missingKeys = array_diff($requiredKeys, array_keys($data));
            // Si des clés manquantes sont trouvées, générer une exception
            if (!empty($missingKeys)) {
                $missingKeysStr = implode(', ', $missingKeys);
                throw new InvalidArgumentException("Missing required key(s): $missingKeysStr in \$data array.");
            }
            $this->logger->info('AutoDebit method called');
            // Implémentation de la méthode payment
            $commandId = 'auto-debit-async';
            $headers    = [
                'Content-Type'      =>  'application/json',
                'Authorization'     =>  self::getBasicAuth(),
                'command-id'        =>  $commandId,
            ];
            $body = array (
                "request-id"    => $data['request-id'],
                "destination"   => $data['destination'], 
                "amount"        => $data['amount'],
                "remarks"       => $data['destination'].'|'.time().'|'.$data['amount'],
                "extended-data" => [
                    "trans-id"  => $data['request_id'],
                    "priority"  => 1
                ]
            );
            $response = self::requestHandler('POST', $body, '', $headers);
            return (Array)$response;
        } catch (Exception $e) {
            $this->errorLogger->error('Error in AutoDebit method: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


}




?>