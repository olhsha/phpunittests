<?php

//require_once dirname(__DIR__) . '/Utils/Authenticator.php'; 
require_once dirname(__DIR__) . '/Utils/RequestResponse.php'; 

class GetConcept2Test extends PHPUnit_Framework_TestCase {
  
    protected $client;
    protected $response0; 
    protected $prefLabel;
    protected $altLabel;
    protected $hiddenLabel;
    protected $notation;
    
    protected function setUp() {
        //$this->client = Authenticator::authenticate();
        $this->client = new Zend_Http_Client();
        $this->client->SetHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Content-Type' => 'text/xml',
            'Accept-Language' => 'nl,en-US,en',
            'Accept-Encoding' => 'gzip, deflate',
            'Connection' => 'keep-alive')
        );
        // create a test concept
        $randomn = rand(0, 2048);
        $this->prefLabel = 'testPrefLable_' . $randomn;
        $this->altLabel = 'testAltLable_' . $randomn;
        $this->hiddenLabel = 'testHiddenLable_' . $randomn;
        $this->notation = 'test-xxx-' . $randomn;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#">' .
                '<rdf:Description rdf:about="http://192.168.99.100/api/collections/mi:collection/' . $this->notation . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $this->prefLabel . '</skos:prefLabel>' .
                '<skos:altLabel xml:lang="nl">' . $this->altLabel . '</skos:altLabel>' .
                '<skos:hiddenLabel xml:lang="nl">' . $this->hiddenLabel . '</skos:hiddenLabel>' .
                '<openskos:set rdf:resource="' . BASE_URI_ . CONCEPT_collection . '"/>' .
                '<skos:notation xml:lang="nl">' . $this->notation . '</skos:notation>' .
                '<skos:inScheme  rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                '<skos:topConceptOf rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                '<skos:definition xml:lang="nl">testje (voor def ingevoegd)</skos:definition>' .
                '</rdf:Description>' .
                '</rdf:RDF>';


        $this->response0 = RequestResponse::CreateConceptRequest($this->client, $xml, "false");
        print "\n Creation status: " . $this->response0 -> getStatus();
        
    }
   
    
    public function testViaPrefLabel2() {
        print "\n" . "Test: get concept-rdf via its prefLabel. ";
        
        if ($this -> response0->isSuccessful()) {
            // we can now perform the get-test
            $this -> client->setUri(BASE_URI_ . '/public/api/find-concepts?q=prefLabel:' . $this -> prefLabel);
            $response = $this -> client->request(Zend_Http_Client::GET);
            if ($response->getStatus() != 200) {
                print "\n " . $response->getMessage();
            }
            $this->AssertEquals(200, $response->getStatus());
            $this ->assertionsForXMLRDFConcept($response, $this -> prefLabel, $this -> altLabel, $this -> hiddenLabel, "nl", "testje (voor def ingevoegd)",  $this -> notation, 1, 1);
            
        } else {
            print "\n Cannot perform the test because something is wrong with creating a test concept: " . $this -> response0->getHeader('X-Error-Msg');
        }
    }
    
   
    public function testViaPrefLabelImplicit2() {
        print "\n" . "Test: get concept-rdf via its prefLabel, without syaing that this is a pref label";
        
        
        if ($this -> response0->isSuccessful()) {
            // we can now perform the get-test
            $this -> client->setUri(BASE_URI_ . '/public/api/find-concepts?q=' . $this -> prefLabel);
            $response = $this -> client->request(Zend_Http_Client::GET);
            if ($response->getStatus() != 200) {
                print "\n " . $response->getMessage();
            }
            $this->AssertEquals(200, $response->getStatus());
            $this ->assertionsForXMLRDFConcept($response, $this -> prefLabel, $this -> altLabel, $this -> hiddenLabel, "nl", "testje (voor def ingevoegd)",  $this -> notation, 1, 1);
            
        } else {
            print "\n Cannot perform the test because something is wrong with creating a test concept: " . $this -> response0->getHeader('X-Error-Msg');
        }
    }
    
    
    
    public function testViaAltLabelImplicit2() {
        print "\n" . "Test: get concept-rdf via its altLabel";
        
        
        if ($this -> response0->isSuccessful()) {
            // we can now perform the get-test
            $this -> client->setUri(BASE_URI_ . '/public/api/find-concepts?q=' . $this -> altLabel);
            $response = $this -> client->request(Zend_Http_Client::GET);
            if ($response->getStatus() != 200) {
                print "\n " . $response->getMessage();
            }
            $this->AssertEquals(200, $response->getStatus());
            $this ->assertionsForXMLRDFConcept($response, $this -> prefLabel, $this -> altLabel, $this -> hiddenLabel,"nl", "testje (voor def ingevoegd)",  $this -> notation, 1, 1);
            
        } else {
            print "\n Cannot perform the test because something is wrong with creating a test concept: " . $this -> response0->getHeader('X-Error-Msg');
        }
    }
    
    
    public function testViaHiddenLabelImplicit2() {
        print "\n" . "Test: get concept-rdf via its hiddenLabel";
        
        
        if ($this -> response0->isSuccessful()) {
            // we can now perform the get-test
            $this -> client->setUri(BASE_URI_ . '/public/api/find-concepts?q=' . $this -> hiddenLabel);
            $response = $this -> client->request(Zend_Http_Client::GET);
            if ($response->getStatus() != 200) {
                print "\n " . $response->getMessage();
            }
            $this->AssertEquals(200, $response->getStatus());
            $this ->assertionsForXMLRDFConcept($response, $this -> prefLabel, $this -> altLabel, $this -> hiddenLabel,"nl", "testje (voor def ingevoegd)",  $this -> notation, 1, 1);
            
        } else {
            print "\n Cannot perform the test because something is wrong with creating a test concept: " . $this -> response0->getHeader('X-Error-Msg');
        }
    }
    
    
    public function testViaPrefLabelIncomplete() {
        print "\n" . "Test: get concept-rdf via its prefLabel's prefix ";
        
        
        if ($this -> response0->isSuccessful()) {
            // we can now perform the get-test
            $this -> client->setUri(BASE_URI_ . '/public/api/find-concepts?q=prefLabel:testPrefLable*');
            $response = $this -> client->request(Zend_Http_Client::GET);
            if ($response->getStatus() != 200) {
                print "\n " . $response->getMessage();
            }
            $this->AssertEquals(200, $response->getStatus());
            $this -> assertionsForManyConcepts($response);
            
        } else {
            print "\n Cannot perform the test because something is wrong with creating a test concept: " . $this -> response0->getHeader('X-Error-Msg');
        }
    }
    
    
    public function testViaPrefLabelIncompleteAndOneRow() {
        print "\n" . "Test: get concept-rdf via its prefLabel's prefix, but asking for 1 row ";
        
        
        if ($this -> response0->isSuccessful()) {
            // we can now perform the get-test
            $this -> client->setUri(BASE_URI_ . '/public/api/find-concepts?q=prefLabel:testPrefLable*&rows=1');
            $response = $this -> client->request(Zend_Http_Client::GET);
            if ($response->getStatus() != 200) {
                print "\n " . $response->getMessage();
            }
            $this->AssertEquals(200, $response->getStatus());
            $this -> assertionsForManyConceptsRows($response, 1);
            
        } else {
            print "\n Cannot perform the test because something is wrong with creating a test concept: " . $this -> response0->getHeader('X-Error-Msg');
        }
    }
    
     public function testViaPrefLabelIncompleteAndTwoRows() {
        print "\n" . "Test: get concept-rdf via its prefLabel's prefix, but asking for 2 rows ";
        
        
        if ($this -> response0->isSuccessful()) {
            // we can now perform the get-test
            $this -> client->setUri(BASE_URI_ . '/public/api/find-concepts?q=prefLabel:testPrefLable*&rows=2');
            $response = $this -> client->request(Zend_Http_Client::GET);
            if ($response->getStatus() != 200) {
                print "\n " . $response->getMessage();
            }
            $this->AssertEquals(200, $response->getStatus());
            $this -> assertionsForManyConceptsRows($response, 2);
            
        } else {
            print "\n Cannot perform the test because something is wrong with creating a test concept: " . $this -> response0->getHeader('X-Error-Msg');
        }
    }
    
    
    
    public function testViaPrefLabelAndLangExist2() {
        print "\n" . "Test: get concept-rdf via its prefLabel and language. ";
        
        if ($this -> response0->isSuccessful()) {
            // we can now perform the get-test
            $this -> client->setUri(BASE_URI_ . '/public/api/find-concepts?q=prefLabel@nl:' . $this -> prefLabel);
            $response = $this -> client->request(Zend_Http_Client::GET);
            if ($response->getStatus() != 200) {
                print "\n " . $response->getMessage();
            }
            $this->AssertEquals(200, $response->getStatus());
            $this ->assertionsForXMLRDFConcept($response, $this -> prefLabel, $this -> altLabel, $this -> hiddenLabel, "nl", "testje (voor def ingevoegd)",  $this -> notation, 1, 1);
            
        } else {
            print "\n Cannot perform the test because something is wrong with creating a test concept: " . $this -> response0->getHeader('X-Error-Msg');
        }
    }
    
    
    public function testViaPrefLabelAndLangDoesNotExist2() {
        print "\n" . "Test: get concept-rdf via its prefLabel and laguage. Empty result. ";
        
        if ($this -> response0->isSuccessful()) {
            // we can now perform the get-test
            $this -> client->setUri(BASE_URI_ . '/public/api/find-concepts?q=prefLabel@en:' . $this -> prefLabel);
            $response = $this -> client->request(Zend_Http_Client::GET);
            if ($response->getStatus() != 200) {
                print "\n " . $response->getMessage();
            }
            $this->AssertEquals(200, $response->getStatus());
            $this ->assertionsForManyConceptsZeroResults($response);
            
        } else {
            print "\n Cannot perform the test because something is wrong with creating a test concept: " . $this -> response0->getHeader('X-Error-Msg');
        }
    }
    
    
    public function testViaPrefLabelPrefixAndLangExist2() {
        print "\n" . "Test: get concept-rdf via its prefLabel and language. ";
        
        if ($this -> response0 -> getStatus() == 201) {
            // we can now perform the get-test
            $this -> client->setUri(BASE_URI_ . '/public/api/find-concepts?q=prefLabel@nl:testPref*');
            $response = $this -> client->request(Zend_Http_Client::GET);
            if ($response->getStatus() != 200) {
                print "\n " . $response->getMessage();
            }
            $this->AssertEquals(200, $response->getStatus());
            $this ->assertionsForManyConcepts($response);
            
        } else {
            print "\n Cannot perform the test because something is wrong with creating a test concept: " . $this -> response0->getBody();
        }
    }
   
    
    public function testViaIdXML() {
        print "\n" . "Test: get concept-rdf via its id. ";
        
        if ($this -> response0->isSuccessful()) {
            $uuid = $this -> getUuidFromResponse($this -> response0);
            // we can now perform the get-test
            // since create generates no uuid, this test still fails.
            $this -> client->setUri(BASE_URI_ . '/public/api/concept/' . $uuid);
            $response = $this -> client->request(Zend_Http_Client::GET);
            if ($response->getStatus() != 200) {
                print "\n " . $response->getMessage();
            }
            $this->AssertEquals(200, $response->getStatus());
            $this ->assertionsForXMLRDFConcept($response, $this -> prefLabel, $this -> altLabel, $this -> hiddenLabel, "nl", "testje (voor def ingevoegd)",  $this -> notation, 1, 1);
            
        } else {
            print "\n Cannot perform the test because something is wrong with creating a test concept: " . $this -> response0->getHeader('X-Error-Msg');
        }
    }
   
    private function getUuidFromResponse($response){
        
        $dom = new Zend_Dom_Query();
        $namespaces = RequestResponse::setNamespaces();
        $dom->registerXpathNamespaces($namespaces);
        $xml = $response->getBody();
        $dom->setDocumentXML($xml);
        
        $sanityCheck = $dom->queryXpath('/rdf:RDF');
        $this -> AssertEquals(1, count($sanityCheck));
        $results1 = $dom->queryXpath('/rdf:RDF/rdf:Description');
        $this -> AssertEquals(1, count($results1));
        $results2 = $dom->queryXpath('/rdf:RDF/rdf:Description/openskos:uuid');
        $this -> AssertEquals(1, count($results2));
        return $results2 -> current() -> nodeValue;
        
    }
    
    private function assertionsForManyConceptsRows($response, $rows) {
        
        $dom = new Zend_Dom_Query();
        $namespaces = RequestResponse::setNamespaces();
        $dom->registerXpathNamespaces($namespaces);
        $xml = $response->getBody();
       $dom->setDocumentXML($xml);
        
        $sanityCheck = $dom->queryXpath('/rdf:RDF');
        $this -> AssertEquals(1, count($sanityCheck));
        $results2 = $dom->queryXpath('/rdf:RDF/rdf:Description');
        $this -> AssertEquals($rows, count($results2));
    }
    
    private function assertionsForManyConceptsZeroResults($response) {
        
        $dom = new Zend_Dom_Query();
        $namespaces = RequestResponse::setNamespaces();
        $dom->registerXpathNamespaces($namespaces);
        $xml = $response->getBody();
        $dom->setDocumentXML($xml);
        
        $sanityCheck = $dom->queryXpath('/rdf:RDF');
        $this -> AssertEquals(1, count($sanityCheck));
        $results1 = $dom->queryXpath('/rdf:RDF') -> current() -> getAttribute('openskos:numFound');
        $results2 = $dom->queryXpath('/rdf:RDF/rdf:Description');
        $this -> AssertEquals(0, count($results2));
        $this -> AssertEquals(0, intval($results1));
    }
    
    private function assertionsForManyConcepts($response) {
        
        $dom = new Zend_Dom_Query();
        $namespaces = RequestResponse::setNamespaces();
        $dom->registerXpathNamespaces($namespaces);
        $xml = $response->getBody();
          //corretions should be removed after bug fixing
        $xmlCorrected1 = str_replace ('dc:creator', 'dcterms:creator', $xml);
        $dom->setDocumentXML($xmlCorrected1);
        
        $sanityCheck = $dom->queryXpath('/rdf:RDF');
        $this -> AssertEquals(1, count($sanityCheck));
        $results1 = $dom->queryXpath('/rdf:RDF') -> current() -> getAttribute('openskos:numFound');
        $results2 = $dom->queryXpath('/rdf:RDF/rdf:Description');
        print "\n numFound =" . intval($results1) . "\n";
        $this -> AssertEquals(intval($results1), count($results2));
        
    }
    
    private function assertionsForXMLRDFConcept($response, $prefLabel,  $altLabel, $hiddenLabel, $lang, $definition, $notation, $topConceptOf, $inScheme) {
        
        $dom = new Zend_Dom_Query();
        $namespaces = RequestResponse::setNamespaces();
        $dom->registerXpathNamespaces($namespaces);
        $xml = $response->getBody();
        
        //corretions should be removed after bug fixing
        $xmlCorrected1 = str_replace ('dc:creator', 'dcterms:creator', $xml);
        $dom->setDocumentXML($xmlCorrected1);
        
        $results1 = $dom->queryXpath('/rdf:RDF/rdf:Description');
        $this -> AssertEquals(1, count($results1));
        $this -> AssertStringStartsWith(BASE_URI_ . CONCEPT_collection, $results1 -> current()-> getAttribute('rdf:about'));
        
        $results2 = $dom->query('rdf:type');
        $this -> AssertEquals(CONCEPT_type_resource, $results2 -> current()-> getAttribute('rdf:resource'));
        
        $results3 = $dom->query('skos:notation');
        $this -> AssertEquals($notation, $results3 -> current()-> nodeValue);
        
        $results4 = $dom->query('skos:inScheme');
        $this -> AssertEquals($inScheme, count($results4));
        
        $results5 = $dom->query('skos:topConceptOf');
        $this -> AssertEquals($topConceptOf, count($results5));
        
        $results6 = $dom->query('skos:prefLabel');
        $this -> AssertEquals($lang, $results6 -> current()-> getAttribute('xml:lang'));
        $this -> AssertEquals($prefLabel, $results6 -> current()-> nodeValue);
        
        $results6a = $dom->query('skos:altLabel');
        $this -> AssertEquals($lang, $results6a -> current()-> getAttribute('xml:lang'));
        $this -> AssertEquals($altLabel, $results6a -> current()-> nodeValue);
        
        $results6b = $dom->query('skos:hiddenLabel');
        $this -> AssertEquals($lang, $results6b -> current()-> getAttribute('xml:lang'));
        $this -> AssertEquals($hiddenLabel, $results6b -> current()-> nodeValue);
        
        $results7 = $dom->query('skos:definition');
        $this -> AssertEquals($definition, $results7 -> current()-> nodeValue);
        
        $results9 = $dom->queryXpath('/rdf:RDF/rdf:Description/dcterms:creator');
        $this -> AssertStringStartsWith(BASE_URI_ , $results9 -> current()-> getAttribute('rdf:resource'));
        
        $results8 = $dom->query('openskos:set');
        $this -> AssertEquals(BASE_URI_ . CONCEPT_collection, $results8 -> current()-> getAttribute('rdf:resource'));
        
    }

}

