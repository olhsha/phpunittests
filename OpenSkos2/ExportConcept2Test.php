<?php

require_once dirname(__DIR__) . '/Utils/RequestResponse.php';
require_once dirname(__DIR__) . '/Utils/Logging.php';
require_once dirname(__DIR__) . '/Utils/Authenticator.php';

class ExportConcept2Test extends PHPUnit_Framework_TestCase {

    private static $client;
    private static $prefLabel;
    private static $altLabel;
    private static $hiddenLabel;
    private static $uuid;
    private static $about;
    private static $notation;
    private static $response0;

    public static function setUpBeforeClass() {
        
        self::$client = Authenticator::authenticate();
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
                '<skos:notation xml:lang="nl">' . self::$notation . '</skos:notation>' .
                '<skos:inScheme  rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                '<skos:topConceptOf rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                '<skos:definition xml:lang="nl">testje (voor def ingevoegd)</skos:definition>' .
                '</rdf:Description>' .
                '</rdf:RDF>';


        self::$response0 = RequestResponse::CreateConceptRequest(self::$client, $xml, "false");
        var_dump(self::$response0->getBody());
        print "\n Creation status: " . self::$response0->getStatus() . "\n";
    }

    public function testExport() {
        if (self::$response0->getStatus() == 201) {
            $response = RequestResponse::DeleteRequest(self::$client, self::$about);
            if ($response->getStatus() != 200) {
                Logging::failureMessaging($response, "export concept");
                $this -> assertions($response);
            }
            $this->AssertEquals(200, $response->getStatus());
        } else {
            Logging::failureMessaging(self::$response0, "create test concept");
        }
    }
    
    private function assertions($response){
        $dom = new Zend_Dom_Query();
        $dom->setDocumentXML($response -> GetBody());
        
       // comparing test xml with teh data from the xml from the file
       // Asserting
        // Assert - 1 
        $results1 = $dom->query('skos:notation');
        $this -> AssertEquals(1, count($results1));
        $this -> AssertEquals(self::$notation, $results1 -> current()-> nodeValue);
        
        // Assert - 2 
        $results2 = $dom->query('skos:inScheme');
        $this -> AssertEquals(1, count($results2));
        $this->assertStringStartsWith("http://data.beeldengeluid.nl/gtaa/Onderwerpen", $results2 -> getAttribute('rdf:resource'));
        
    }

   
}
