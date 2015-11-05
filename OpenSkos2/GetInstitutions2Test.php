<?php

require_once dirname(__DIR__) . '/Utils/RequestResponse.php'; 

class GetInstitutions2Test extends PHPUnit_Framework_TestCase {
    
    private static $client;
     
    public static function setUpBeforeClass() {
        self::$client = new Zend_Http_Client();
        self::$client->setConfig(array(
            'maxredirects' => 0,
            'timeout' => 30));

        self::$client->SetHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Content-Type' => 'text/xml',
            'Accept-Language' => 'nl,en-US,en',
            'Accept-Encoding' => 'gzip, deflate',
            'Connection' => 'keep-alive')
        );
        
    }
    
    public function testAllInstitutions() {
        print "\n Test: get all institutions in default format ... ";
        $response = RequestResponse::GetCollectionOrInstitution(self::$client, BASE_URI_ . '/public/api/institutions', 'text/xml');
        $this->AssertEquals(200, $response->getStatus(), $response->getMessage());
        $this->assertionsXMLRDFInstitutions($response);
    }
    
    public function testAllInstitutionsJson() {
        print "\n Test: get all institutions in json ... ";
        $response = RequestResponse::GetCollectionOrInstitution(self::$client, BASE_URI_ . '/public/api/institutions?format=json', 'application/json');
        $this->AssertEquals(200, $response->getStatus(), $response->getMessage());
        $this->assertionsJsonInstitutions($response);
    }
    
   
    
    public function testAllInstitutionsJsonP() {
        print "\n Test: get all institutions in jsonp ... ";
        $response = RequestResponse::GetCollectionOrInstitution(self::$client, BASE_URI_ . '/public/api/institutions?format=jsonp$callback'. CALLBACK_NAME , 'application/json');
        $this->AssertEquals(200, $response->getStatus(), $response->getMessage());
        $this->assertionsJsonPInstitutions($response);
    }

    
   
    public function testAllInstitutionsRDFXML() {
        print "\n Test: get all institutions rdf/xml explicit... ";
        $response = RequestResponse::GetCollectionOrInstitution(self::$client, BASE_URI_ . '/public/api/institutions/' . INSTITUTION_CODE . '.rdf', 'text/xml');
        $this->AssertEquals(200, $response->getStatus(), $response->getMessage());
        $this->assertionsXMLRDFInstitutions($response);
    }
    
    public function testAllInstitutionsHTML() {
        print "\n Test: get all institutions html... ";
        $response = RequestResponse::GetCollectionOrInstitution(self::$client, BASE_URI_ . '/public/api/institutions?format=html', 'text/html');
        $this->AssertEquals(200, $response->getStatus(), $response->getMessage());
        $this->assertionsHTMLAllInstitutions($response);
    }
    
    public function testInstitution() {
        print "\n Test: get a institution in default format ... ";
        $response = RequestResponse::GetCollectionOrInstitution(self::$client, BASE_URI_ . '/public/api/institutions/' . INSTITUTION_CODE, 'text/xml');
        $this->AssertEquals(200, $response->getStatus(), $response->getMessage());
        $dom = new Zend_Dom_Query();
        $dom->setDocumentXML($response->getBody());
        $this->assertionsXMLRDFInstitution($dom, 0);
    }
    
    
    public function testInstitutionJson() {
        print "\n Test: get a institution in json ... ";
        $response = RequestResponse::GetCollectionOrInstitution(self::$client, BASE_URI_ . '/public/api/institutions/' . INSTITUTION_CODE. '.json', 'application/json');
        $this->AssertEquals(200, $response->getStatus(), $response->getMessage());
        $json = $response->getBody();
        $institution = json_decode($json, true);
        $this->assertionsJsonInstitution($institution, 0);
    }
    
    
    public function testInstitutionJsonP() {
        print "\n Test: get a institution in jsonp ... ";
        $response = RequestResponse::GetCollectionOrInstitution(self::$client, BASE_URI_ . '/public/api/institutions/' . INSTITUTION_CODE. '.jsonp?callback=' . CALLBACK_NAME, 'application/json');
        $this->AssertEquals(200, $response->getStatus(), $response->getMessage());
        $json = $response->getBody();
        $institution = RequestResponse::jsonP_decode_parameters($json, CALLBACK_NAME);
        $this->assertionsJsonInstitution($institution, 0);
    }
    
    public function testInstitutionHTML() {
        print "\n Test: get a institution in html ... ";
        $response = RequestResponse::GetCollectionOrInstitution(self::$client, BASE_URI_ . '/public/api/institutions/' . INSTITUTION_CODE .".html", 'text/html');
        $this->AssertEquals(200, $response->getStatus(), $response->getMessage());
        $dom = new Zend_Dom_Query();
        $dom->setDocumentHTML($response->getBody());
        $this->assertionsHTMLInstitution($dom, 0);
    }

    ////////////////////////////////////
    private function assertionsJsonInstitution($institution, $i) {
        if ($i == 0) {
            $this->assertEquals(INSTITUTION_CODE, $institution["code"]);
            $this->assertEquals(INSTITUTION_NAME, $institution["name"]);
            $this->assertEquals(INSTITUTION_MAIL, $institution["email"]);
            $this->assertEquals(NUMBER_COLLECTIONS, count(["collections"]));
        } else {
            $this->AssertEquals(1, 0);
        }
    }
    
    private function assertionsJsonInstitutions($response) {
        $json = $response->getBody();
        $institutions = json_decode($json, true);
        $this -> assertEquals(NUMBER_COLLECTIONS, count($institutions["institutions"]));
        $i =0;
        foreach ($institutions["institutions"] as $institution){
           $this -> assertionsJsonInstitution($institution, $i);
           $i++;
        }
    }
    
    
    
    private function assertionsJsonPInstitutions($response) {
        $this -> assertionsJsonInstitutions($response);
    }
    
    private function assertionsXMLRDFInstitution(Zend_Dom_Query $dom, $i) {
        $results1 = $dom->query('v:Vcard');
        $results2 = $dom->query('v:fn');
        $results3 = $dom->query('v:organisation-name');
        $results4 = $dom->query('v:email');
        if ($i == 0) {
            $this->AssertEquals(INSTITUTION_NAME, $results2 -> current()->nodeValue);
            $this->AssertEquals(INSTITUTION_NAME, $results3 -> current()->nodeValue);
            $this->AssertEquals("mailto:" . INSTITUTION_MAIL, $results4 -> current()->getAttribute('rdf:about'));
            $uri = BASE_URI_. INSTITUTION_URI . "/" . INSTITUTION_CODE;
            $this->AssertEquals($uri, $results1 -> current()->getAttribute('rdf:about'));
            
        } else {
            $this->AssertEquals(1, 0);
        }
    }
   
    private function assertionsXMLRDFInstitutions($response) {
        $dom = new Zend_Dom_Query();
        $dom->setDocumentXML($response->getBody());
        $results1 = $dom->query('v:Vcard');
        $this -> assertEquals(NUMBER_INSTITUTIONS, count($results1));
        for($i=0; $i<NUMBER_INSTITUTIONS; $i++){
            $this -> assertionsXMLRDFInstitution($dom, $i);
        }
    }
    
    private function assertionsHTMLInstitution(Zend_Dom_Query $dom, $i) {
        $header2 = $dom->query('h2');
        $codeQuery = $dom->query('dl > dt');
        $codeValueQuery = $dom->query('dl > dd');
        $collectionsQuery = $dom->query('ul > li > p');
        $formats = $dom->query('ul > li > a');
        
        $title = RequestResponse::getByIndex($header2, $i)->nodeValue;
        $this->AssertEquals(INSTITUTION_NAME, $title);
        
        $codeItem = RequestResponse::getByIndex($codeQuery, $i)->nodeValue;
        $this->AssertEquals("code:", $codeItem);
        
        $codeValue = RequestResponse::getByIndex($codeValueQuery, $i)->nodeValue;
        $this->AssertEquals(INSTITUTION_CODE, $codeValue);
        
        $this->AssertEquals(NUMBER_COLLECTIONS, count($collectionsQuery));
        $this->AssertEquals(3, count($formats));
        
    }
    
    private function assertionsHTMLAllInstitutions($response) {
        $dom = new Zend_Dom_Query();
        $dom->setDocumentHTML($response->getBody());
        $institutions = $dom->query('ul > li > a > strong'); // fetches institutions and formats together
        $this -> AssertEquals(NUMBER_INSTITUTIONS, count($institutions));
        for($i=0 ; $i<NUMBER_INSTITUTIONS; $i++){
           $title = RequestResponse::getByIndex($institutions, $i)->nodeValue;
           if ($i===0) {
               $this -> AssertEquals(INSTITUTION_NAME, $title);
           } else {
              $this -> AssertEquals(1, 0);
           }
        }
        
        $list = $dom->query('ul > li > a'); // fetches institutions and formats together
        $this -> AssertEquals(3, count($list)-NUMBER_INSTITUTIONS);
    }

}
