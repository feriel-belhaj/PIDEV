<?php

namespace App\Service;

use Twilio\Rest\Client;
use Psr\Log\LoggerInterface;
use Composer\CaBundle\CaBundle;

class TwilioService
{
    private $accountSid;
    private $authToken;
    private $twilioPhoneNumber;
    private $logger;

    public function __construct(string $accountSid, string $authToken, string $twilioPhoneNumber, LoggerInterface $logger)
    {
        $this->accountSid = $accountSid;
        $this->authToken = $authToken;
        $this->twilioPhoneNumber = $twilioPhoneNumber;
        $this->logger = $logger;
    }

    public function getTwilioPhoneNumber(): string
    {
        return $this->twilioPhoneNumber;
    }

    public function sendSMS(string $to, string $message): void
    {
        try {
            // Configuration basique sans SSL pour tester
            $client = new Client($this->accountSid, $this->authToken);
            $httpClient = new \Twilio\Http\CurlClient([
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_PROXY => null,        // Désactiver tout proxy
                CURLOPT_FOLLOWLOCATION => true // Suivre les redirections
            ]);
            
            $client->setHttpClient($httpClient);

            // Formater le numéro au format international si nécessaire
            $formattedNumber = $to;
            if (!str_starts_with($to, '+')) {
                $formattedNumber = '+216' . ltrim($to, '0');
            }

            $result = $client->messages->create(
                $formattedNumber,
                [
                    'from' => $this->twilioPhoneNumber,
                    'body' => $message
                ]
            );
            
            $this->logger->info('SMS sent successfully', [
                'messageId' => $result->sid,
                'to' => $formattedNumber
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Twilio error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'to' => $to
            ]);
            throw $e;
        }
    }
} 