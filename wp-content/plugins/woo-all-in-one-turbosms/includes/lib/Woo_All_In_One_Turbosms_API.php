<?php 
define ('TURBOSMS_ENV', 'dev');

class Woo_All_In_One_Turbosms_API {
    private $url = 'http://turbosms.in.ua/api/wsdl.html';
    private $client;
    private $auth_status = false;

    public function __construct() {
        $this->client = new SoapClient($this->url);

        try {
            $this->setAuth();
        } catch ( Exception $e ) {
            $this->client = false;
        }
    }

    private function setAuth() {
        $settings = Woo_All_In_One_Turbosms_Helpers::get_settings();

        $login = $settings['login'];
        $password = $settings['password'];

        $this->auth_status = $this->client->Auth(array(
            'login' => $login,
            'password' => $password,
        ));
    }

    public function getBalance() {
        if ( !$this->client ) {
            return false;
        }
        return $this->client->GetCreditBalance();
    }

    public function getAuthStatus() {
        return $this->auth_status;
    }

    public function sendSMS($sender, $destination, $text) {
        if ( !$this->client ) {
            return false;
        }

        $sms = array(
            'sender' => $sender,
            'destination' => $destination,
            'text' => $text
        );

        return $this->client->SendSMS($sms);
    }
}