<?php

require_once dirname(__DIR__) . '/Utils/Authenticator.php';
require_once dirname(__DIR__) . '/Utils/RequestResponse.php'; 

class GetCollections2Test extends PHPUnit_Framework_TestCase {
    
    
    public function testAllCollectionsJson() {
        print "\n" . "Test: get all collections in json. ";
       $client = Authenticator::authenticate();
       $response = RequestResponse::GetCollection($client, BASE_URI_ . '/public/api/collections?format=json', 'application/json'); 
       
       if ($response->getStatus() != 200) {
          print "\n " . $response->getMessage();
       }
      
       $this -> AssertEquals(200, $response->getStatus());
       $this -> assertionsForJsonCollections($response);
    } 
    
    public function testAllCollectionsOAIYesJson() {
        print "\n" . "Test: get all collections in json, allow oai. ";
        $client = Authenticator::authenticate();
        $response = RequestResponse::GetCollection($client, BASE_URI_ . '/public/api/collections?allow_oai=y&format=json', 'application/json'); 
       
       if ($response->getStatus() != 200) {
          print "\n " . $response->getMessage();
       }
      
       $this -> AssertEquals(200, $response->getStatus());
       
       // empty result
        $json = $response->getBody();
        $arrays = json_decode($json, true);
        $this -> assertEquals(1, count($arrays));
        $this -> assertEquals(0, count($arrays["collections"]));
    } 
    
    public function testCollectionsRDFXML() {
        print "\n" . "Test: get collection. ";
         $client = Authenticator::authenticate();
        $response = RequestResponse::GetCollection($client, BASE_URI_ . '/public/api/collections/' . COLLECTION_1_tenant . ":" . COLLECTION_1_code . '.rdf', 'text/xml'); 
       
       // analyse respond
       if ($response->getStatus() != 200) {
          print "\n " . $response->getMessage();
       }
      
       $this -> AssertEquals(200, $response->getStatus());
       $this -> assertionsForXMLRDFCollection($response);
    }  
    
    private function assertionsForJsonCollections($response) {
        $json = $response->getBody();
        $arrays = json_decode($json, true);
        //var_dump($arrays);
        $this -> assertEquals(NUMBER_COLLECTIONS, count($arrays["collections"]));
        $this -> assertEquals(COLLECTION_1_code, $arrays["collections"][0]["code"]);
        $this -> assertEquals(COLLECTION_1_tenant, $arrays["collections"][0]["tenant"]);
        $this -> assertEquals(COLLECTION_1_title, $arrays["collections"][0]["dc_title"]);
        $this -> assertEquals(COLLECTION_1_description, $arrays["collections"][0]["dc_description"]);
        $this -> assertEquals(COLLECTION_1_website, $arrays["collections"][0]["website"]);
        $this -> assertEquals(COLLECTION_1_licensename, $arrays["collections"][0]["license_name"]);
        $this -> assertEquals(COLLECTION_1_licenseurl, $arrays["collections"][0]["license_url"]);
        $this -> assertEquals(COLLECTION_1_oaibaseurl, $arrays["collections"][0]["OAI_baseURL"]);
        $this -> assertEquals(COLLECTION_1_allowoai, $arrays["collections"][0]["allow_oai"]);
        $this -> assertEquals(COLLECTION_1_conceptsbaseurl, $arrays["collections"][0]["conceptsBaseUrl"]);
    }
    
    private function assertionsForXMLRDFCollection($response) {
        $dom = new Zend_Dom_Query();
        $dom->setDocumentXML($response->getBody());
        
        $results1 = $dom->query('rdf:Description');
        $collection_1_description_about = BASE_URI_ .  '/api/collections/' . COLLECTION_1_tenant . ":" . COLLECTION_1_code;
        $this -> AssertEquals($collection_1_description_about, $results1 -> current()-> getAttribute('rdf:about'));
        
        $results2 = $dom->query('rdf:type');
        $this -> AssertEquals(COLLECTION_1_type_resource, $results2 -> current()-> getAttribute('rdf:resource'));
        
        $results3 = $dom->query('dcterms:title');
        $this -> AssertEquals(COLLECTION_1_title, $results3 -> current()-> nodeValue);
        
        $results4 = $dom->query('dcterms:creator');
        $this -> AssertEquals(COLLECTION_1_creator, $results4 -> current()-> nodeValue);
        $this -> AssertEquals(BASE_URI_ . COLLECTION_1_creator_about_suffix, $results4 -> current()-> getAttribute('rdf:about'));
        
    }
    
}