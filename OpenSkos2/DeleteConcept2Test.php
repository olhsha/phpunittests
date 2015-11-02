<?php

require_once dirname(__DIR__) . '/Utils/RequestResponse.php'; 
require_once dirname(__DIR__) . '/Utils/Logging.php'; 

class DeleteConcept2Test extends PHPUnit_Framework_TestCase {
  
    private static $client;
    private static $prefLabel;
    private static $altLabel;
    private static $hiddenLabel;
    private static $uuid;
    private static $about;
    private static $notation;
    private static $response0;
    private static $aboutC;
    private static $response0C;
    
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
        self::$prefLabel = 'testPrefLable_' . $randomn;
        self::$altLabel = 'testAltLable_' . $randomn;
        self::$hiddenLabel = 'testHiddenLable_' . $randomn;
        self::$notation = 'test-xxx-' . $randomn;
        self::$uuid = uniqid();
        self::$about = BASE_URI_ . CONCEPT_collection . "/" . self::$notation;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#">' .
                '<rdf:Description rdf:about="' . self::$about . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . self::$prefLabel . '</skos:prefLabel>' .
                '<skos:altLabel xml:lang="nl">' . self::$altLabel . '</skos:altLabel>' .
                '<skos:hiddenLabel xml:lang="nl">' . self::$hiddenLabel . '</skos:hiddenLabel>' .
                '<openskos:set rdf:resource="' . BASE_URI_ . CONCEPT_collection . '"/>' .
                '<openskos:uuid>' . self::$uuid . '</openskos:uuid>' .
                '<openskos:tenant>' . COLLECTION_1_tenant . '</openskos:tenant>' .
                '<skos:notation xml:lang="nl">' . self::$notation . '</skos:notation>' .
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
        $altLabelC = 'testAltLable_' . $randomnc;
        $hiddenLabelC = 'testHiddenLable_' . $randomn;
        $notationC = 'test-xxx-' . $randomnC;
        $uuidC = uniqid();
        self::$aboutC = BASE_URI_ . CONCEPT_collection . "/" . $notationC;
        $xmlC = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#">' .
                '<rdf:Description rdf:about="' . self::$aboutC . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabelC . '</skos:prefLabel>' .
                '<skos:altLabel xml:lang="nl">' . $altLabelC . '</skos:altLabel>' .
                '<skos:hiddenLabel xml:lang="nl">' . $hiddenLabelC . '</skos:hiddenLabel>' .
                '<openskos:set rdf:resource="' . BASE_URI_ . CONCEPT_collection . '"/>' .
                '<openskos:uuid>' . $uuidC . '</openskos:uuid>' .
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
    }
    
    public function testDelete() {
        if (self::$response0->getStatus() == 201) {
            $response = RequestResponse::DeleteRequest(self::$client, self::$about);
            if ($response->getStatus() != 200) {
                Logging::failureMessaging($response, "delete concept");
            }
            $this->AssertEquals(200, $response->getStatus());
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
        } else {
            Logging::failureMessaging(self::$response0, "create test candidate concept");
        }
    }

    private function failureMessaging($response, $action) {
        print "\n Failed to " . $action. ", response header: " . $response->getHeader('X-Error-Msg');
        print "\n Failed to " . $action. ", response message: " . $response->getMessage();
        print "\n Failed to " . $action. ", responce message: " . $response->getBody();
    }
}