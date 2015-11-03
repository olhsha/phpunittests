<?php

require_once dirname(__DIR__) . '/Utils/Authenticator.php';
require_once dirname(__DIR__) . '/Utils/RequestResponse.php'; 

class GetCollections2Test extends PHPUnit_Framework_TestCase {
    
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
    
    public function testAllCollections() {
        print "\n Test: get all collections in default format ... ";
        $response = RequestResponse::GetCollection(self::$client, BASE_URI_ . '/public/api/collections', 'text/xml');
        $this->AssertEquals(200, $response->getStatus(), $response->getMessage());
        $this->assertionsXMLRDFCollections($response);
    }
    
    public function testAllCollectionsJson() {
        print "\n Test: get all collections in json ... ";
        $response = RequestResponse::GetCollection(self::$client, BASE_URI_ . '/public/api/collections?format=json', 'application/json');
        $this->AssertEquals(200, $response->getStatus(), $response->getMessage());
        $this->assertionsJsonCollections($response);
    }
    
   
    
    public function testAllCollectionsJsonP() {
        print "\n Test: get all collections in jsonp ... ";
        $response = RequestResponse::GetCollection(self::$client, BASE_URI_ . '/public/api/collections?format=jsonp$callback=my_callback1234', 'application/json');
        $this->AssertEquals(200, $response->getStatus(), $response->getMessage());
        $this->assertionsJsonPCollections($response);
    }

    public function testAllCollectionsOAIYesJson() {
        print "\n Test: get all collections in json, allow oai ... ";
        $response = RequestResponse::GetCollection(self::$client, BASE_URI_ . '/public/api/collections?allow_oai=y&format=json', 'application/json'); 
        $this->AssertEquals(200, $response->getStatus(), $response->getMessage());
       
       // empty result
        $json = $response->getBody();
        $arrays = json_decode($json, true);
        $this -> assertEquals(1, count($arrays));
        $this -> assertEquals(0, count($arrays["collections"]));
    } 
    
    public function testCollectionsRDFXML() {
        print "\n Test: get all collections rdf/xml explicit... ";
        $response = RequestResponse::GetCollection(self::$client, BASE_URI_ . '/public/api/collections/' . COLLECTION_1_tenant . ":" . COLLECTION_1_code . '.rdf', 'text/xml');
        $this->AssertEquals(200, $response->getStatus(), $response->getMessage());
        $this->assertionsXMLRDFCollections($response);
    }
    
    public function testCollection() {
        print "\n Test: get a collection in default format ... ";
        $response = RequestResponse::GetCollection(self::$client, BASE_URI_ . '/public/api/collections/' . COLLECTION_1_tenant . ":" . COLLECTION_1_code, 'text/xml');
        $this->AssertEquals(200, $response->getStatus(), $response->getMessage());
        $dom = new Zend_Dom_Query();
        $dom->setDocumentXML($response->getBody());
        $this->assertionsXMLRDFCollection($dom, 0);
    }
    
    
    public function testCollectionJson() {
        print "\n Test: get a collection in json ... ";
        $response = RequestResponse::GetCollection(self::$client, BASE_URI_ . '/public/api/collections/' . COLLECTION_1_tenant . ":" . COLLECTION_1_code. '.json', 'application/json');
        $this->AssertEquals(200, $response->getStatus(), $response->getMessage());
        $json = $response->getBody();
        $collection = json_decode($json, true);
        $this->assertionsJsonCollection($collection, 0);
    }
    
    
    public function testCollectionJsonP() {
        print "\n Test: get a collection in jsonp ... ";
        $response = RequestResponse::GetCollection(self::$client, BASE_URI_ . '/public/api/collections/' . COLLECTION_1_tenant . ":" . COLLECTION_1_code. '.jsonp?callback=my_callback1234', 'application/json');
        $this->AssertEquals(200, $response->getStatus(), $response->getMessage());
        $json = $response->getBody();
        var_dump($json);
        $collections = json_decode($json, true);
        $collection = json_decode($json, true);
        var_dump($collection);
        $this->assertionsJsonPCollection($collection, 0);
    }

    ////////////////////////////////////
    private function assertionsJsonCollection($collection, $i) {
        if ($i == 0) {
            $this->assertEquals(COLLECTION_1_code, $collection["code"]);
            $this->assertEquals(COLLECTION_1_tenant, $collection["tenant"]);
            $this->assertEquals(COLLECTION_1_title, $collection["dc_title"]);
            $this->assertEquals(COLLECTION_1_description, $collection["dc_description"]);
            $this->assertEquals(COLLECTION_1_website, $collection["website"]);
            $this->assertEquals(COLLECTION_1_licensename, $collection["license_name"]);
            $this->assertEquals(COLLECTION_1_licenseurl, $collection["license_url"]);
            $this->assertEquals(COLLECTION_1_oaibaseurl, $collection["OAI_baseURL"]);
            $this->assertEquals(COLLECTION_1_allowoai, $collection["allow_oai"]);
            $this->assertEquals(COLLECTION_1_conceptsbaseurl, $collection["conceptsBaseUrl"]);
        }
    }
    
    private function assertionsJsonCollections($response) {
        $json = $response->getBody();
        $collections = json_decode($json, true);
        $this -> assertEquals(NUMBER_COLLECTIONS, count($collections["collections"]));
        $i =0;
        foreach ($collections["collections"] as $collection){
           $this -> assertionsJsonCollection($collection, $i);
           $i++;
        }
    }
    
     private function assertionsJsonPCollection($response, $i) {
        
    }
    
    private function assertionsJsonPCollections($response) {
        $this -> assertionsJsonCollections($response);
    }
    
    private function assertionsXMLRDFCollection(Zend_Dom_Query $dom, $i) {
        $results1 = $dom->query('rdf:Description');
        $results2 = $dom->query('rdf:type');
        $results3 = $dom->query('dcterms:title');
        $results4 = $dom->query('dcterms:creator');
        if ($i == 0) {
            $collection_1_description_about = BASE_URI_ . '/api/collections/' . COLLECTION_1_tenant . ":" . COLLECTION_1_code;
            $this->AssertEquals($collection_1_description_about, $results1 ->current()->getAttribute('rdf:about'));
            $this->AssertEquals(COLLECTION_1_type_resource, $results2 -> current()->getAttribute('rdf:resource'));
            $this->AssertEquals(COLLECTION_1_title, $results3 -> current()->nodeValue);
            $this->AssertEquals(COLLECTION_1_creator, $results4 -> current()->nodeValue);
            $this->AssertEquals(BASE_URI_ . COLLECTION_1_creator_about_suffix, $results4->current()->getAttribute('rdf:about'));
        } else {
            // collection 2, next()
        }
    }
   
    private function assertionsXMLRDFCollections($response) {
        $dom = new Zend_Dom_Query();
        $dom->setDocumentXML($response->getBody());
        $results1 = $dom->query('rdf:Description');
        $this -> assertEquals(NUMBER_COLLECTIONS, count($results1));
        for($i=0; $i<NUMBER_COLLECTIONS; $i++){
            $this -> assertionsXMLRDFCollection($dom, $i);
        }
    }
    
}