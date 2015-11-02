<?php

class Authenticator {

    // used in import requests because import is sent via the editor
    public static function authenticate() {
        $zendClient = new Zend_Http_Client();
        $zendClient->setCookieJar();
        $zendClient -> setUri(BASE_URI_ . '/public/editor/login/authenticate');
        $zendClient->setConfig(array(
            'maxredirects' => 2,
            'timeout' => 30));
        $zendClient->SetHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
                'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept-Language'=>'en-US,en',
            'Accept-Encoding'=>'gzip, deflate',
            'Referer' => BASE_URI_ . '/public/editor/login',
            'Connection'=>'keep-alive')
        );
        
        $zendClient -> setParameterPost('username', USERNAME);
        $zendClient -> setParameterPost('tenant', TENANT);
        $zendClient -> setParameterPost('password', PASSWORD);
        $zendClient -> setParameterPost('rememberme', '0');
        $zendClient -> setParameterPost('login', 'Login');
        $responseAuth = $zendClient -> request(Zend_Http_Client::POST);
        print "\n Authentication response status: " . $responseAuth -> getStatus();
        print "\n Authentication response message: " . $responseAuth -> getMessage() . "\n";
        return $zendClient;
    }
}

?>
    