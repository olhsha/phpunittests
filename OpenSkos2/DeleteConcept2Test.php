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
        $randomn = rand(0, 20480);
        $prefLabel = 'testPrefLable_' . $randomn;
        $notation = 'test-xxx-' . $randomn;
        self::$uuid = uniqid();
        $uuid = uniqid();
        self::$about = BASE_URI_ . "/" . OPENSKOS_SET_code . "/" . $notation;
        $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#">' .
                '<rdf:Description rdf:about="' . self::$about . '">' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                '<openskos:uuid>' . $uuid . '</openskos:uuid>' .
                '<skos:inScheme  rdf:resource="' . SCHEMA_URI_1 . '"/>' .
                '<openskos:set>' . OPENSKOS_SET_code . '</openskos:set>' .
                '<openskos:tenant>' . TENANT . '</openskos:tenant>' .
                '<skos:notation>' . $notation . '</skos:notation>' .
                '<skos:topConceptOf rdf:resource="' . SCHEMA_URI_1 . '"/>' .
                '<skos:definition xml:lang="nl">testje (voor def ingevoegd)</skos:definition>' .
                '</rdf:Description>' .
                '</rdf:RDF>';

        self::$response0 = RequestResponse::CreateConceptRequest(self::$client, $xml, "false");
        if (self::$response0->getStatus() !== 201) {
                Logging::failureMessaging(self::$response0, "creating test concept");
                return;
            } else { // things went well, but when submitting a concept is status is automatically reset to "candidate";
                // now update to change the status for "approved", otherwise autocomplete would not react
                self::$response0 = RequestResponse::UpdateConceptRequest(self::$client, $xml);
                if (self::$response0->getStatus() !== 201) {
                    Logging::failureMessaging(self::$response0, "updating test concept to set status to 'apprved'");
                    return;
                }
            }
        
        $randomnC = rand(0, 20480);
        $prefLabelC = 'testPrefLable_' . $randomnC;
        $notationC = 'test-xxx-' . $randomnC;
        self::$uuidC = uniqid();
        self::$aboutC = BASE_URI_ . "/" . OPENSKOS_SET_code . "/" . $notationC;
        $xmlC = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#">' .
                '<rdf:Description rdf:about="' . self::$aboutC . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . $prefLabelC . '</skos:prefLabel>' .
                '<openskos:set>' . OPENSKOS_SET_code . '</openskos:set>' .
                '<openskos:uuid>' . self::$uuidC . '</openskos:uuid>' .
                '<openskos:tenant>' . TENANT . '</openskos:tenant>' .
                '<skos:notation>' . $notationC . '</skos:notation>' .
                '<skos:inScheme  rdf:resource="' . SCHEMA_URI_1 . '"/>' .
                '<skos:topConceptOf rdf:resource="' . SCHEMA_URI_1 . '"/>' .
                '<skos:definition xml:lang="nl">testje (voor def ingevoegd)</skos:definition>' .
                '</rdf:Description>' .
                '</rdf:RDF>';
        
        self::$response0C = RequestResponse::CreateConceptRequest(self::$client, $xmlC, "false");
    }
    
    
    public function testDeleteCandidate() {
        print "\n deleting concept with candidate status ...";
        if (self::$response0C->getStatus() === 201) {
            $response = RequestResponse::DeleteRequest(self::$client, self::$aboutC);
            if ($response->getStatus() !== 202) {
                Logging::failureMessaging($response, "delete candidate concept");
            }
            $this->AssertEquals(202, $response->getStatus());
            self::$client->setUri(BASE_URI_ . '/public/api/concept?id='. self::$uuidC);
            $responseCheck = self::$client->request('GET');
            if ($responseCheck -> getStatus() !== 410) {
                print("\n getting deleted concept has status " . $responseCheck->getStatus());
                print("\n with the message " . $responseCheck->getMessage());
            }
            $this->AssertEquals(410, $responseCheck->getStatus());
        } 
    }
    
    public function testDeleteApproved() {
        print "\n deleting concept with approved status ...";
        if (self::$response0->getStatus() === 201) {
            $response = RequestResponse::DeleteRequest(self::$client, self::$about);
            self::$client->setUri(BASE_URI_ . '/public/api/concept?id='. self::$uuid);
            $responseCheck = self::$client->request('GET');
            if ($responseCheck -> getStatus() === 410) {
                print "\n Approved concept has been marked as deleted! \n";
            }
            $this->AssertEquals(410, $responseCheck->getStatus());
            if($response->getStatus() !== 202) {
                print("\n delete return status " . $response->getStatus());
                print("\n with the message " . $response->getMessage());
            }
            $this->AssertEquals(202, $response->getStatus());
        } 
    }
    
     

}