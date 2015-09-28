<?php

class Authenticator {

    public static function authenticate() {
        $zendClient = new Zend_Http_Client();
        $zendClient->setCookieJar();
        $zendClient -> setUri('http://192.168.99.100/public/editor/login/authenticate');
        $zendClient->setConfig(array(
            'maxredirects' => 10,
            'timeout' => 300));
        $zendClient->SetHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
                'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept-Language'=>'en-US,en',
            'Accept-Encoding'=>'gzip, deflate',
            'Host' => '192.168.99.100',
            'Referer' => 'http://192.168.99.100/public/editor/login',
            'Connection'=>'keep-alive')
        );
        
        $zendClient -> setParameterPost('username', TEST_USERNAME);
        $zendClient -> setParameterPost('tenant', TEST_TENANT);
        $zendClient -> setParameterPost('password', TEST_PASSWORD);
        $zendClient -> setParameterPost('rememberme', '0');
        $zendClient -> setParameterPost('login', 'Login');
        $responseAuth = $zendClient -> request(Zend_Http_Client::POST);
        print "\n Authentication response status: " . $responseAuth -> getStatus();
        print "\n Authentication response message: " . $responseAuth -> getMessage();
        return $zendClient;
    }
}

?>
    