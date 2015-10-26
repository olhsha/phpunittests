<?php

require_once dirname(__DIR__) . '/Utils/Authenticator.php'; 
require_once dirname(__DIR__) . '/Utils/RequestResponse.php'; 

class GetConcept2Test extends PHPUnit_Framework_TestCase {
  
    protected $client;
    protected $response0; 
    protected $prefLabel;
    protected $notation;
    
    protected function setUp() {
        $this->client = Authenticator::authenticate();

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
        $this->notation = 'test-xxx-' . $randomn;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#">' .
                '<rdf:Description rdf:about="http://192.168.99.100/api/collections/mi:collection/' . $this->notation . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $this->prefLabel . '</skos:prefLabel>' .
                '<openskos:set rdf:resource="' . BASE_URI_ . CONCEPT_collection . '"/>' .
                '<skos:notation xml:lang="nl">' . $this->notation . '</skos:notation>' .
                '<skos:inScheme  rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                '<skos:topConceptOf rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                '<skos:definition xml:lang="nl">testje (voor def ingevoegd)</skos:definition>' .
                '</rdf:Description>' .
                '</rdf:RDF>';


        $this->response0 = RequestResponse::CreateConceptRequest($this->client, $xml, "false");
    }
   
    /*
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
            $this ->assertionsForXMLRDFConcept($response, $this -> prefLabel, "nl", "testje (voor def ingevoegd)",  $this -> notation, 1, 1);
            
        } else {
            print "\n Cannot perform the test because something is wrong with creating a test concept: " . $this -> response0->getHeader('X-Error-Msg');
        }
    }
    
   
    public function testViaPrefLabelImplicit2() {
        print "\n" . "Test: get concept-rdf via its prefLabel without saying that this is a prefLabel ";
        
        
        if ($this -> response0->isSuccessful()) {
            // we can now perform the get-test
            $this -> client->setUri(BASE_URI_ . '/public/api/find-concepts?q=' . $this -> prefLabel);
            $response = $this -> client->request(Zend_Http_Client::GET);
            if ($response->getStatus() != 200) {
                print "\n " . $response->getMessage();
            }
            $this->AssertEquals(200, $response->getStatus());
            $this ->assertionsForXMLRDFConcept($response, $this -> prefLabel, "nl", "testje (voor def ingevoegd)",  $this -> notation, 1, 1);
            
        } else {
            print "\n Cannot perform the test because something is wrong with creating a test concept: " . $this -> response0->getHeader('X-Error-Msg');
        }
    }
   */
     
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
    
    private function assertionsForManyConcepts($response) {
        
        $dom = new Zend_Dom_Query();
        $namespaces = RequestResponse::setNamespaces();
        $dom->registerXpathNamespaces($namespaces);
        $xml = $response->getBody();
        
        //corretions should be removed after bug fixing
        $xmlCorrected = str_replace ('xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#type"', 'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"', $xml);
        $xmlCorrected1 = str_replace ('dc:creator', 'dcterms:creator', $xmlCorrected);
        $dom->setDocumentXML($xmlCorrected1);
        
        $sanityCheck = $dom->queryXpath('/rdf:RDF');
        $this -> AssertEquals(1, count($sanityCheck));
        $results1 = $dom->queryXpath('/rdf:RDF') -> current() -> getAttribute('openskos:numFound');
        $results2 = $dom->queryXpath('/rdf:RDF/rdf:Description');
        $this -> AssertEquals(intval($results1), count($results2));
        
    }
    
    private function assertionsForXMLRDFConcept($response, $prefLabel,  $lang, $definition, $notation, $topConceptOf, $inScheme) {
        
        $dom = new Zend_Dom_Query();
        $namespaces = RequestResponse::setNamespaces();
        $dom->registerXpathNamespaces($namespaces);
        $xml = $response->getBody();
        
        //corretions should be removed after bug fixing
        $xmlCorrected = str_replace ('xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#type"', 'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"', $xml);
        $xmlCorrected1 = str_replace ('dc:creator', 'dcterms:creator', $xmlCorrected);
        var_dump($xmlCorrected1);
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
        
        $results7 = $dom->query('skos:definition');
        $this -> AssertEquals($definition, $results7 -> current()-> nodeValue);
        
        $results9 = $dom->queryXpath('/rdf:RDF/rdf:Description/dcterms:creator');
        $this -> AssertStringStartsWith(BASE_URI_ , $results9 -> current()-> getAttribute('rdf:resource'));
        
        $results8 = $dom->query('openskos:set');
        $this -> AssertEquals(BASE_URI_ . CONCEPT_collection, $results8 -> current()-> getAttribute('rdf:resource'));
        
    }

}

