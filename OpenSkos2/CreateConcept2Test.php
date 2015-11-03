<?php

require_once dirname(__DIR__) . '/Utils/RequestResponse.php'; 
require_once dirname(__DIR__) . '/Utils/Logging.php';

class CreateConcept2Test extends PHPUnit_Framework_TestCase {
    
    private $client;
    
    // used to collect the id of a created concept so that it can be removed by tearDown after a test method finishes its work
    private $abouts;
    
    protected function setUp() {
        $this->client = new Zend_Http_Client();
        $this->client->setConfig(array(
            'maxredirects' => 0,
            'timeout' => 30));

        $this->client->SetHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Content-Type' => 'text/xml',
            'Accept-Language' => 'nl,en-US,en',
            'Accept-Encoding' => 'gzip, deflate',
            'Connection' => 'keep-alive')
        );
        
        $this -> abouts = array();
    }
    
    protected function tearDown() {
        RequestResponse::deleteConcepts($this->abouts, $this ->client);
        unset($this->abouts);
    }

    public function test01CreateConceptWithoutURIWithDateAccepted2() {
        //CreateConceptTest::test01CreateConceptWithoutURIWithDateAccepted();
        // Create new concept with dateAccepted filled (implicit status APPROVED). This should not be possible. 
        print "\n\n test01 ... \n";
        $randomn = rand(0, 2048);
        $prefLabel = 'testPrefLable_' . $randomn;
        $dateSubmitted = date(DateTime::ISO8601); //'2015-10-01T15:06:58Z';//
        $dateAccepted = '2015-10-02T10:31:35Z'; // date(DateTime::ISO8601);'2015-10-01T15:06:58Z';
        $set = BASE_URI_ . CONCEPT_collection;

        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:ns0="http://dublincore.org/documents/dcmi-terms/#">' .
                '<rdf:Description>' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<ns0:terms-dateSubmitted>' . $dateSubmitted . '</ns0:terms-dateSubmitted>' .
                '<ns0:terms-dateAccepted>' . $dateAccepted . '</ns0:terms-dateAccepted>' .
                '<skos:inScheme  rdf:resource="http://meertens/scheme/example1"/>' .
                '<openskos:set rdf:resource="' . $set .'"/>' .
                '<openskos:tenant> ' . COLLECTION_1_tenant . '</openskos:tenant>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($this -> client, $xml, "true");
        if ($response -> getStatus() == 201) {
            array_push($this -> abouts, $this ->getAbout($response));
        } 
        $this -> resultMessaging($response);
        $this->AssertEquals(409, $response->getStatus());
    }

    
    public function test02CreateConceptWithoutUriWithoutDateAccepted() {
        // Create a concept without Uri and without dateAccepted , but with UniquePrefLabel. Check XML response.
        print "\n\n test02 ... \n";
        $randomn = rand(0, 2048);
        $prefLabel = 'testPrefLable_' . $randomn;
        $set = BASE_URI_ . CONCEPT_collection;

        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#">' .
                '<rdf:Description>' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<openskos:set rdf:resource="' . $set . '"/>' .
                '<skos:inScheme  rdf:resource="http://meertens/scheme/example1"/>' .
                '<openskos:tenant> ' . COLLECTION_1_tenant . '</openskos:tenant>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($this-> client, $xml, "true");
        $this -> resultMessaging($response);
        $this->AssertEquals(201, $response->getStatus());
        $this->CheckCreatedConcept($response);
    }

   
   public function test03CreateConceptWithURIAlreadyExists() {
        // test if creating a new concept with an URI that already exists, fails
        print "\n\n test03 ... \n";
        $randomn = rand(0, 2048);
        $prefLabel = 'testPrefLable_' . $randomn;
        $notation = 'notation_' . $randomn;
        $set = BASE_URI_ . CONCEPT_collection;
        $conceptId = $set . "/" . $notation;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:openskos="http://openskos.org/xmlns/openskos.xsd" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dcterms="http://purl.org/dc/terms/" > ' .
                '<rdf:Description rdf:about="' . $conceptId . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<skos:notation>' . $notation . '</skos:notation>' .
                '<skos:inScheme  rdf:resource="http://meertens/scheme/example1"/>' .
                '<openskos:set rdf:resource="' . $set . '"/>' .
                '<openskos:tenant> ' . COLLECTION_1_tenant . '</openskos:tenant>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        // create the first concept with which we will compare
        $response = RequestResponse::CreateConceptRequest($this->client, $xml, "false");

        if ($response->getStatus() == 201) {
           print "\n First concept is created \n";
           array_push($this -> abouts, $this ->getAbout($response));
           $xml2= str_replace ('testPrefLable_', '_another_testPrefLable_', $xml);
           $response2 = RequestResponse::CreateConceptRequest($this->client, $xml2, "false");
           if ($response2 -> getStatus() == 201) {
            array_push($this -> abouts, $this ->getAbout($response2));
           } 
           $this -> resultMessaging($response2);
           $this->AssertEquals(400, $response2->getStatus());
        } else {
           Logging::failureMessaging($response, 'creating the first concept');
        }

       
    }

    
    public function test04CreateConceptWithoutURIUniquePrefLabelNoApiKey() {
        // create concept without URI. but with unique prefLabel. Api Key is missng.
        // todo: veoeken met verkeerde parameters moeten foutcode opleveren (collection, tenant)
        print "\n\n test04 ... \n";
        $randomn = rand(0, 2048);
        $prefLabel = 'testPrefLable_' . $randomn;
        $set = BASE_URI_ . CONCEPT_collection;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#">' .
                '<rdf:Description>' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<skos:inScheme  rdf:resource="http://meertens/scheme/example1"/>' .
                '<openskos:set rdf:resource="' . $set . '"/>' .
                '<openskos:tenant> ' . COLLECTION_1_tenant . '</openskos:tenant>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptNoApikeyRequest($this -> client, $xml, "true");
        if ($response->getStatus() == 201) {
            array_push($this->abouts, $this->getAbout($response));
        }
        $this -> resultMessaging($response);
        $this->AssertEquals(412, $response->getStatus());
    }

    
    public function test05CreateConceptWithURIUniquePrefLabel() {
        // Create concept with URI and with unique prefLabel, including skos:notation
        print "\n\n test05 ... \n";
        $randomn = rand(0, 4096);
        $prefLabel = 'testPrefLable_' . $randomn;
        $notation = 'testNotation_' . $randomn;
        $set = BASE_URI_ . CONCEPT_collection;
        $about = $set . '/' . $notation;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description rdf:about="' . $about . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<skos:notation>' . $notation . '</skos:notation>' .
                '<skos:inScheme  rdf:resource="http://meertens/scheme/example1"/>' .
                '<openskos:set rdf:resource="' . $set . '"/>' .
                '<openskos:tenant> ' . COLLECTION_1_tenant . '</openskos:tenant>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($this -> client, $xml, "false");
        $this -> resultMessaging($response);
        $this->AssertEquals(201, $response->getStatus());
        $this->CheckCreatedConcept($response);
    }

    
    public function test05BCreateConceptWithURIUniquePrefLabel() {
        // Create concept with URI and with unique prefLabel, without skos:notation
        print "\n\n test05B ... \n";
        $randomn = rand(0, 4096);
        $prefLabel = 'testPrefLable_' . $randomn;
        $notation = 'testNotation_' . $randomn;
        $set = BASE_URI_ . CONCEPT_collection;
        $about = $set . '/' . $notation;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description rdf:about="' . $about . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<skos:inScheme  rdf:resource="http://meertens/scheme/example1"/>' .
                '<openskos:set rdf:resource="'. $set . '"/>' .
                '<openskos:tenant> ' . COLLECTION_1_tenant . '</openskos:tenant>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($this -> client, $xml, "false");
        if ($response->getStatus() == 201) {
            array_push($this->abouts, $about);
        }
        $this -> resultMessaging($response);
        $this->AssertEquals(400, $response->getStatus());
    }

   
   public function test05CCreateConceptWithURIUniquePrefLabel() {
        // Create concept with URI and with unique prefLabel, with duplicate skos:notation
        print "\n\n test05C ... \n";
        $randomn = rand(0, 4096);
        $prefLabel = 'testPrefLable_' . $randomn;
        $notation = 'testNotation_' . $randomn;
        $set = BASE_URI_ . CONCEPT_collection;
        $about = $set . '/' . $notation;
        $xml0 = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description rdf:about="' . $about . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<skos:notation>' . $notation . '</skos:notation>' .
                '<skos:inScheme  rdf:resource="http://meertens/scheme/example1"/>' .
                '<openskos:set rdf:resource="' . $set . '"/>' .
                '<openskos:tenant> ' . COLLECTION_1_tenant . '</openskos:tenant>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response0 = RequestResponse::CreateConceptRequest($this -> client, $xml0, "false");
        if ($response0->getStatus()==201) {
            print "\n First concept is created \n";
            array_push($this->abouts, $about);
            $xml1 = str_replace('testPrefLable_', '_another_testPrefLable_', $xml0);
            $response1 = RequestResponse::CreateConceptRequest($this -> client, $xml1, "false");
            if ($response1->getStatus() == 201) {
                array_push($this->abouts, $about);
            }
            $this -> resultMessaging($response1);
            $this->AssertEquals(400, $response1->getStatus());
        } else {
            Logging::failureMessaging($response0, 'create first test concept');
        }
    }

    
    public function test06CreateConceptWithURIUniquePrefLabel() {
        // Create concept without URI about, the xml is wrong
        print "\n\n test06 ... \n";
        $randomn = rand(0, 4096);
        $prefLabel = 'testPrefLable_' . $randomn;
        $set = BASE_URI_ . CONCEPT_collection;
        $wrongXml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description>' .
                //'<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>'. 
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<skos:inScheme  rdf:resource="http://meertens/scheme/example1"/>' .
                '<openskos:set rdf:resource="' . $set . '"/>' .
                '<openskos:tenant> ' . COLLECTION_1_tenant . '</openskos:tenant>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($this -> client, $wrongXml, "true");
        if ($response->getStatus() == 201) {
            array_push($this->abouts, $this->getAbout($response));
        }
        $this -> resultMessaging($response);
        $this->AssertEquals(400, $response->getStatus());
    }

    
    public function test07CreateConceptWithoutUri() {
        // Create a concept without Uri and with unique PrefLabel. 
        print "\n\n test07 ... \n";
        $randomn = rand(0, 4092);
        $prefLabel = 'testPrefLable_' . $randomn;
        $set = BASE_URI_ . CONCEPT_collection;
        
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description>' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<dcterms:creator>Test</dcterms:creator>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<skos:inScheme  rdf:resource="http://meertens/scheme/example1"/>' .
                '<openskos:set rdf:resource="'. $set . '"/>' .
                '<openskos:tenant> ' . COLLECTION_1_tenant . '</openskos:tenant>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($this -> client, $xml, "true");
        $this -> resultMessaging($response);
        $this->AssertEquals(201, $response->getStatus());
        $this->CheckCreatedConcept($response);
    }

    
    public function test08CreateConceptWithoutUriAutogenerateFalse() {
        // Create a concept without Uri and with unique PrefLabel.  Autogenerate parameter is false
        print "\n\n test08 ... \n";
        $randomn = rand(0, 4092);
        $prefLabel = 'testPrefLable_' . $randomn;
        $set = BASE_URI_ . CONCEPT_collection;
        
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description>' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<skos:inScheme  rdf:resource="http://meertens/scheme/example1"/>' .
                '<openskos:set rdf:resource="'. $set . '"/>' .
                '<openskos:tenant> ' . COLLECTION_1_tenant . '</openskos:tenant>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($this -> client, $xml, "false");
        if ($response->getStatus() == 201) {
            array_push($this->abouts, $this->getAbout($response));
        }
        $this -> resultMessaging($response);
        $this->AssertEquals(400, $response->getStatus());
    }
    
    public function test09CreateConceptWithoutUriPrefLabelExists() {
        // Create a concept without Uri and prefLabel is not unique within a scheme. 
        print "\n\n test09 ... \n";
        $randomn = rand(0, 4092);
        $prefLabel = 'testPrefLable_' . $randomn;
        $altLabel = 'testAltPrefLable_' . $randomn;
        $set = BASE_URI_ . CONCEPT_collection;
        // create the first instance of the concept
        $xml0 = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description>' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<skos:altLabel xml:lang="nl">' . $altLabel . '</skos:altLabel>' .
                '<skos:inScheme  rdf:resource="http://meertens/scheme/example1"/>' .
                '<openskos:set rdf:resource="'. $set . '"/>' .
                '<openskos:tenant> ' . COLLECTION_1_tenant . '</openskos:tenant>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response0 = RequestResponse::CreateConceptRequest($this -> client, $xml0, "true");
        print "\n Test setting:  the first concept is created. \n";
        
        if ($response0->getStatus() == 201) {
            // we can proceed with the test
            array_push($this->abouts, $this->getAbout($response0));
            $xml = str_replace('testAltLable_', '_another_testAltLable_', $xml0);
            $response = RequestResponse::CreateConceptRequest($this -> client, $xml, "true");
            if ($response->getStatus() == 201) {
                array_push($this->abouts, $this->getAbout($response));
            }
            $this -> resultMessaging($response);
            $this->AssertEquals(400, $response->getStatus());
        } else {
            print "ERROR while creating the first test concept, cannot proceed woth the test! ";
            Logging::failureMessaging($response0, 'create the first test concept');
        }
    }

   
   public function test10CreateConceptWithoutUriButWithNotationUniquePrefLabel() {
        // Create a concept without Uri (no rdf:about), but with notation. prefLabel is unique. 
        print "\n\n test10 ... \n";
        $randomn = rand(0, 4092);
        $prefLabel = 'testPrefLable_' . $randomn;
        $set = BASE_URI_ . CONCEPT_collection;
        $notation = 'testNotation_' . $randomn;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description>' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                 '<skos:inScheme  rdf:resource="http://meertens/scheme/example1"/>' .
                '<openskos:set rdf:resource="'. $set . '"/>' . 
                '<skos:notation>' . $notation .  '</skos:notation>' .
                '<openskos:tenant> ' . COLLECTION_1_tenant . '</openskos:tenant>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($this -> client, $xml, "true");
        if ($response->getStatus() == 201) {
            array_push($this->abouts, $this->getAbout($response));
        }
        $this -> resultMessaging($response);
        $this->AssertEquals(400, $response->getStatus());
    }

   
    public function test10BCreateConceptWithoutUriButWithoutNotationUniquePrefLabel() {
        // Create a concept without Uri (no rdf:about), and no notation. prefLabel is unique. 
        print "\n\n test10 ... \n";
        $randomn = rand(0, 4092);
        $set = BASE_URI_ . CONCEPT_collection;
        $prefLabel = 'testPrefLable_' . $randomn;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#" > ' .
                '<rdf:Description>' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                 '<skos:inScheme  rdf:resource="http://meertens/scheme/example1"/>' .
                '<openskos:set rdf:resource="'. $set . '"/>'  .
                '<openskos:tenant> ' . COLLECTION_1_tenant . '</openskos:tenant>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        $response = RequestResponse::CreateConceptRequest($this -> client, $xml, "false");
        if ($response->getStatus() == 201) {
            array_push($this->abouts, $this->getAbout($response));
        }
        $this -> resultMessaging($response);
        $this->AssertEquals(400, $response->getStatus());
    }

    // this method also retrieves rdf:about element which is used in tearDown method to remove created concept after the method is done.
    private function CheckCreatedConcept($response) {
        $dom = new Zend_Dom_Query();
        $dom->setDocumentXML($response->getBody());
        $namespaces = RequestResponse::setNamespaces();
        $dom->registerXpathNamespaces($namespaces);

        $elem = $dom->queryXpath('/rdf:RDF');
        $this->assertEquals($elem->current()->nodeType, XML_ELEMENT_NODE, 'The root node of the response is not an element');

        $description = $dom->queryXpath('/rdf:RDF/rdf:Description'); 
        $this->assertEquals(1, $description->count(), "rdf:Description element is not declared");
        
        $resURI = $description->current()->getAttribute("rdf:about");
        $this->assertNotEquals(null, $resURI, "No valid uri for SKOS concept (null value)");
        $this->assertNotEquals("", $resURI, "No valid uri for SKOS concept (empty-string value)");
        array_push($this -> abouts, $resURI);
        
        $status = $dom->queryXpath('/rdf:RDF/skos:Concept/openskos:status');
        $this->assertEquals(1, $status->count(), "No openkos:status element. ");
        $this->assertEquals("Candidate", $status->current()->nodeValue, "Satus is not Candidate, as it must be by just created concept.");
        print "\n New concept is created with URI $resURI  and status" . $status->current()->nodeValue;
    }
     
    private function getAbout($response){
        $dom = new Zend_Dom_Query();
        $namespaces = RequestResponse::setNamespaces();
        $dom->setDocumentXML($response->getBody());
        $dom->registerXpathNamespaces($namespaces);

        $description = $dom->queryXpath('/rdf:RDF/rdf:Description'); 
        $this->assertEquals(1, $description->count(), "rdf:Description element is not declared");
        
        $resURI = $description->current()->getAttribute("rdf:about");
        $this->assertNotEquals("", $resURI, "No valid uri for SKOS concept");
        return $resURI;
    }
            
    private function resultMessaging($response) {
        if ($response -> getStatus() == 201) {
            print "\n Hosanna: a concept is created! \n";
            print "\n HTTPResponseHeader-Location: " . $response->getHeader('Location') . "\n";
        } else {
           Logging::failureMessaging($response, 'create concept');
        }
    }

}

