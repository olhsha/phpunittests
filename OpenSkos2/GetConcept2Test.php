<?php

require_once dirname(__DIR__) . '/Utils/RequestResponse.php'; 

class GetConcept2Test extends PHPUnit_Framework_TestCase {
  
    private $client;
    private $response0; 
    private $prefLabel;
    private $altLabel;
    private $hiddenLabel;
    private $notation;
    private $uuid;
    private $about;
    
   protected function setUp() {
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
        $this -> uuid = uniqid();
        $this -> about = BASE_URI_ . CONCEPT_collection . "/" .$this -> notation;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#">' .
                '<rdf:Description rdf:about="' . $this -> about . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $this->prefLabel . '</skos:prefLabel>' .
                '<skos:altLabel xml:lang="nl">' . $this->altLabel . '</skos:altLabel>' .
                '<skos:hiddenLabel xml:lang="nl">' . $this->hiddenLabel . '</skos:hiddenLabel>' .
                '<openskos:set rdf:resource="' . BASE_URI_ . CONCEPT_collection . '"/>' .
                '<openskos:uuid>' . $this -> uuid . '</openskos:uuid>' .
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
   
    public function testViaHandleXML() {
        print "\n" . "Test: get concept-rdf via its id. ";
        
        if ($this -> response0->isSuccessful()) {
            // we can now perform the get-test
            $this -> client->setUri(BASE_URI_ . '/public/api/concept/?id=' . $this -> about);
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
    
    public function testViaIdXML() {
        print "\n" . "Test: get concept-rdf via its id. ";
        
        if ($this -> response0->isSuccessful()) {
            // we can now perform the get-test
            $this -> client->setUri(BASE_URI_ . '/public/api/concept/' . $this -> uuid);
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
  
    
    public function testViaIdXMLrdf() {
        print "\n" . "Test: get concept-rdf via its id. ";
        
        if ($this -> response0->isSuccessful()) {
            // we can now perform the get-test
            $this -> client->setUri(BASE_URI_ . '/public/api/concept/' . $this -> uuid . '.rdf');
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
      
     
    
    public function testViaIdHtml() {
        print "\n" . "Test: get concept-html via its id. ";
        
        if ($this -> response0->isSuccessful()) {
            // we can now perform the get-test
            $this -> client->setUri(BASE_URI_ . '/public/api/concept/' . $this -> uuid . '.html');
            $response = $this -> client->request(Zend_Http_Client::GET);
            if ($response->getStatus() != 200) {
                print "\n " . $response->getMessage();
            }
            $this->AssertEquals(200, $response->getStatus());
            $this ->assertionsForHtmlConcept($response, $this -> prefLabel, $this -> altLabel, $this -> hiddenLabel, "nl", "testje (voor def ingevoegd)",  $this -> notation, 1, 1);
            
        } else {
            print "\n Cannot perform the test because something is wrong with creating a test concept: " . $this -> response0->getHeader('X-Error-Msg');
        }
    }

   
    public function testViaIdJson() {
        print "\n" . "Test: get concept-json via its id. ";
        
        if ($this -> response0->isSuccessful()) {
            // we can now perform the get-test
            $this -> client->setUri(BASE_URI_ . '/public/api/concept/' . $this -> uuid . '.json');
            $response = $this -> client->request(Zend_Http_Client::GET);
            if ($response->getStatus() != 200) {
                print "\n " . $response->getMessage();
            }
            $this->AssertEquals(200, $response->getStatus());
            $this ->assertionsForJsonConcept($response, $this -> uuid, $this -> prefLabel, $this -> altLabel, $this -> hiddenLabel, "nl", "testje (voor def ingevoegd)",  $this -> notation, 1, 1);
            
        } else {
            print "\n Cannot perform the test because something is wrong with creating a test concept: " . $this -> response0->getHeader('X-Error-Msg');
        }
    }
    
    
    public function testViaIdJsonP() {
        print "\n" . "Test: get concept-json via its id. ";
        
        if ($this -> response0->isSuccessful()) {
            // we can now perform the get-test
            $this -> client->setUri(BASE_URI_ . '/public/api/concept/' . $this->uuid . '.jsonp&callback=test');
            $response = $this -> client->request(Zend_Http_Client::GET);
            if ($response->getStatus() != 200) {
                print "\n " . $response->getMessage();
            }
            $this->AssertEquals(200, $response->getStatus());
            $this ->assertionsForJsonPConcept($response, $this -> uuid, $this -> prefLabel, $this -> altLabel, $this -> hiddenLabel, "nl", "testje (voor def ingevoegd)",  $this -> notation, 1, 1);
            
        } else {
            print "\n Cannot perform the test because something is wrong with creating a test concept: " . $this -> response0->getHeader('X-Error-Msg');
        }
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
        $dom->setDocumentXML($xml);
        
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
        $dom ->registerXpathNamespaces($namespaces);
        $xml = $response->getBody();
        $dom->setDocumentXML($xml);
        
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
        
        $results9 = $dom->query('dcterms:creator');
        $this -> AssertStringStartsWith(BASE_URI_ , $results9 -> current()-> getAttribute('rdf:resource'));
        
        $results8 = $dom->query('openskos:set');
        $this -> AssertEquals(BASE_URI_ . CONCEPT_collection, $results8 -> current()-> getAttribute('rdf:resource'));
        
    }
    
     
    private function getByIndex($list, $index){
        if ($index < 0 || $index >= count($list)) {
            return null;
        }
        $list -> rewind();
        $i=0;
        while ($i<$index){
            $list -> next();
            $i++;
        }
        return $list -> current();
    }
    
    private function assertionsForHTMLConcept($response, $prefLabel, $altLabel, $hiddenLabel, $lang, $definition, $notation, $topConceptOf, $inScheme) {
        $dom = new Zend_Dom_Query();
        $dom->setDocumentHtml($response->getBody());
        
        //does not work because of . : $results1 = $dom->query('dl > dd  > a[href="http://hdl.handle.net/11148/CCR_C-4046_944cc750-1c29-ccf0-fb68-4d00385d7b42"]');
        $resultsUri1 = $dom->query('dl > dt');
        
        $propertyName = $this -> getByIndex($resultsUri1, 2) -> nodeValue;
        $this -> AssertEquals("SKOS Class:", $propertyName);
        
        $resultsUri2 = $dom->query('dl > dd > a');
        $property = $this -> getByIndex($resultsUri2, 2);
        $this -> AssertEquals("http://www.w3.org/2004/02/skos/core#Concept", $property -> nodeValue);
        $this -> AssertEquals("http://www.w3.org/2004/02/skos/core#Concept", $property -> getAttribute('href'));
        
        $h3s = $dom -> query('h3');
        $inSchemeName =  $this -> getByIndex($h3s, 0) -> nodeValue;
        $this -> AssertEquals("inScheme", $inSchemeName);
        
        $lexLabels =  $this -> getByIndex($h3s, 2) -> nodeValue;
        $this -> AssertEquals("LexicalLabels", $lexLabels);
        
        $h4s = $dom -> query('h4');
        $altLabelName =  $this -> getByIndex($h4s, 2) -> nodeValue;
        $this -> AssertEquals("skos:http://www.w3.org/2004/02/skos/core#altLabel", $altLabelName);
        $prefLabelName =  $this -> getByIndex($h4s, 4) -> nodeValue;
        $this -> AssertEquals("skos:http://www.w3.org/2004/02/skos/core#prefLabel", $prefLabelName);
        $notationName =  $this -> getByIndex($h4s, 5) -> nodeValue;
        $this -> AssertEquals("skos:http://www.w3.org/2004/02/skos/core#notation", $notationName);
        
        $list = $dom -> query('ul > li > a > span');
        $prefLabelVal = $this ->getByIndex($list, 4) -> nodeValue;
        $this -> AssertEquals($prefLabel, $prefLabelVal);
        
    }
    
    private function assertionsForJsonConcept($response,  $uuid, $prefLabel,  $altLabel, $hiddenLabel, $lang, $definition, $notation, $topConceptOf, $inScheme) {
        $json = $response->getBody();
        $arrays = json_decode($json, true);
        $this -> assertEquals($uuid, $arrays["uuid"]);
        $this -> assertEquals($altLabel, $arrays["altLabel@nl"][0]);
        $this -> assertEquals($prefLabel, $arrays["prefLabel@nl"]);
        return $json;
    }
    
    private function assertionsForJsonPConcept($response, $uuid, $altLabel, $hiddenLabel, $lang, $definition, $notation, $topConceptOf, $inScheme, $altLabel, $hiddenLabel, $lang, $definition, $notation, $topConceptOf, $inScheme) {
        $json = $this->asseryionsForJason($response, $uuid, $altLabel, $hiddenLabel, $lang, $definition, $notation, $topConceptOf, $inScheme, $altLabel, $hiddenLabel, $lang, $definition, $notation, $topConceptOf, $inScheme);
    }

}

