<?php

require_once dirname(__DIR__) . '/Utils/RequestResponse.php'; 
require_once dirname(__DIR__) . '/Utils/Logging.php'; 

class DeleteConcept2Test extends PHPUnit_Framework_TestCase {
  
    private static $client;
    private static $about;
    private static $uuid;
    private static $response0;
    private static $aboutC;
    private static $uuidC;
    private static $response0C;
    private static $aboutApproved;
    private static $response0Approved;
    private static $uuidApproved;
    
    public static function setUpBeforeClass() {

        self::$client = new Zend_Http_Client();
        self::$client->SetHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Content-Type' => 'text/xml',
            'Accept-Language' => 'nl,en-US,en',
            'Accept-Encoding' => 'gzip, deflate',
            'Connection' => 'keep-alive')
        );
        // create a test concept
        $randomn = rand(0, 2048);
        $prefLabel = 'testPrefLable_' . $randomn;
        $notation = 'test-xxx-' . $randomn;
        self::$uuid = uniqid();
        self::$about = BASE_URI_ . CONCEPT_collection . "/" . $notation;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#">' .
                '<rdf:Description rdf:about="' . self::$about . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<openskos:set rdf:resource="' . BASE_URI_ . CONCEPT_collection . '"/>' .
                '<openskos:uuid>' . self::$uuid . '</openskos:uuid>' .
                '<openskos:tenant>' . COLLECTION_1_tenant . '</openskos:tenant>' .
                '<skos:notation xml:lang="nl">' . $notation . '</skos:notation>' .
                '<skos:inScheme  rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                '<skos:topConceptOf rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                '<skos:definition xml:lang="nl">testje (voor def ingevoegd)</skos:definition>' .
                '</rdf:Description>' .
                '</rdf:RDF>';


        self::$response0 = RequestResponse::CreateConceptRequest(self::$client, $xml, "false");
        //var_dump(self::$response0 -> getBody());
        print "\n Create status: " . self::$response0 -> getStatus() . "\n";
        
        $randomnC = rand(0, 2048);
        $prefLabelC = 'testPrefLable_' . $randomnC;
        $notationC = 'test-xxx-' . $randomnC;
        self::$uuidC = uniqid();
        self::$aboutC = BASE_URI_ . CONCEPT_collection . "/" . $notationC;
        $xmlC = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#">' .
                '<rdf:Description rdf:about="' . self::$aboutC . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabelC . '</skos:prefLabel>' .
                '<openskos:set rdf:resource="' . BASE_URI_ . CONCEPT_collection . '"/>' .
                '<openskos:uuid>' . self::$uuidC . '</openskos:uuid>' .
                '<openskos:status>candidate</openskos:status>' .
                '<openskos:tenant>' . COLLECTION_1_tenant . '</openskos:tenant>' .
                '<skos:notation xml:lang="nl">' . $notationC . '</skos:notation>' .
                '<skos:inScheme  rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                '<skos:topConceptOf rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                '<skos:definition xml:lang="nl">testje (voor def ingevoegd)</skos:definition>' .
                '</rdf:Description>' .
                '</rdf:RDF>';
        
        self::$response0C = RequestResponse::CreateConceptRequest(self::$client, $xmlC, "false");
        //var_dump(self::$response0C -> getBody());
        print "\n Create status: " . self::$response0C -> getStatus() . "\n";
        
        $randomnApproved = rand(0, 2048);
        $prefLabelApproved = 'testPrefLable_' . $randomnApproved;
        $notationApproved = 'test-xxx-' . $randomnApproved;
        self::$uuidApproved = uniqid();
        self::$aboutApproved = BASE_URI_ . CONCEPT_collection . "/" . $notationApproved;
        $xmlApproved = str_replace($prefLabelC, $prefLabelApproved, $xmlC);
        $xmlApproved = str_replace(self::$aboutC, self::$aboutApproved, $xmlApproved);
        $xmlApproved = str_replace($notationC, $notationApproved, $xmlApproved);
        $xmlApproved = str_replace(self::$uuidC, self::$uuidApproved, $xmlApproved);
        self::$response0Approved = RequestResponse::CreateConceptRequest(self::$client, $xmlApproved, "false");
        //var_dump(self::$response0C -> getBody());
        print "\n Create status: " . self::$response0Approved -> getStatus() . "\n";
        
    }
    
    public function testDelete() {
        if (self::$response0->getStatus() == 201) {
            $response = RequestResponse::DeleteRequest(self::$client, self::$about);
            if ($response->getStatus() != 200) {
                Logging::failureMessaging($response, "delete concept");
            }
            $this->AssertEquals(200, $response->getStatus());
            self::$client->setUri(BASE_URI_ . '/public/api/concept/'. self::$uuid);
            $responseCheck = self::$client->request('GET');
            if ($responseCheck -> getStatus() != 404) {
                Logging::failureMessaging(self::$response0, "delete concept");
            }
            
        } else {
            Logging::failureMessaging(self::$response0, "create test concept");
        }
    }
    
    public function testDeleteCandidate() {
        if (self::$response0C->getStatus() == 201) {
            $response = RequestResponse::DeleteRequest(self::$client, self::$aboutC);
            if ($response->getStatus() != 200) {
                Logging::failureMessaging($response, "delete candidate concept");
            }
            $this->AssertEquals(200, $response->getStatus());
            self::$client->setUri(BASE_URI_ . '/public/api/concept/'. self::$uuidC);
            $responseCheck = self::$client->request('GET');
            if ($responseCheck -> getStatus() != 404) {
                Logging::failureMessaging(self::$response0, "delete candidate concept");
            }
        } else {
            Logging::failureMessaging(self::$response0, "create test candidate concept");
        }
    }
    
    public function testDeleteApproved() {
        if (self::$response0C->getStatus() == 201) {
            $response = RequestResponse::DeleteRequest(self::$client, self::$aboutApproved);
            self::$client->setUri(BASE_URI_ . '/public/api/concept/'. self::$uuidApproved);
            $responseCheck = self::$client->request('GET');
            if ($responseCheck -> getStatus() == 404) {
                print "\n Approved concept is not found after deletion attempt! \n";
            }
            $this->AssertNotEquals(200, $response->getStatus());
            
        } else {
            Logging::failureMessaging(self::$response0, "create test candidate concept");
        }
    }

}