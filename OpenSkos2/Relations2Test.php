<?php

require_once dirname(__DIR__) . '/Utils/RequestResponse.php';

class GetConcept2Test extends PHPUnit_Framework_TestCase {

    private static $client;
    private static $responseX;
    private static $prefLabelX;
    private static $notationX;
    private static $uuidX;
    private static $aboutX;
    private static $responseY;
    private static $prefLabelY;
    private static $notationY;
    private static $uuidY;
    private static $aboutY;
    
public static function setUpBeforeClass() {
        self::$client = new Zend_Http_Client();
        self::$client->SetHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Content-Type' => 'text/xml',
            'Accept-Language' => 'nl,en-US,en',
            'Accept-Encoding' => 'gzip, deflate',
            'Connection' => 'keep-alive')
        );
        // create a test concept X
        $randomn = rand(0, 2048);
        self::$prefLabelX = 'prefLabelX_' . $randomn;
        self::$notationX = 'test-x-' . $randomn;
        self::$uuidX = uniqid();
        self::$aboutX = BASE_URI_ . CONCEPT_collection . "/" . self::$notationX;
        $xmlX = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#">' .
                '<rdf:Description rdf:about="' . self::$aboutX . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . self::$prefLabelX . '</skos:prefLabel>' .
                '<openskos:set rdf:resource="' . BASE_URI_ . CONCEPT_collection . '"/>' .
                '<openskos:uuid>' . self::$uuidX . '</openskos:uuid>' .
                '<skos:notation>' . self::$notationX . '</skos:notation>' .
                '<skos:inScheme  rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                '<skos:topConceptOf rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                '<skos:definition xml:lang="nl">testje (voor def ingevoegd)</skos:definition>' .
                '</rdf:Description>' .
                '</rdf:RDF>';


        self::$responseX = RequestResponse::CreateConceptRequest(self::$client, $xmlX, "false");
        print "\n Creation status for concept X: " . self::$responseX->getStatus();
        
        // create a test concept X
        self::$prefLabelY = 'prefLabelY_' . $randomn;
        self::$notationY = 'test-y-' . $randomn;
        self::$uuidY = uniqid();
        self::$aboutY = BASE_URI_ . CONCEPT_collection . "/" . self::$notationY;
        $xmlY = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#">' .
                '<rdf:Description rdf:about="' . self::$aboutY . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:prefLabel xml:lang="nl">' . self::$prefLabelY . '</skos:prefLabel>' .
                '<openskos:set rdf:resource="' . BASE_URI_ . CONCEPT_collection . '"/>' .
                '<openskos:uuid>' . self::$uuidY . '</openskos:uuid>' .
                '<skos:notation>' . self::$notationY . '</skos:notation>' .
                '<skos:inScheme  rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                '<skos:topConceptOf rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                '<skos:definition xml:lang="nl">testje (voor def ingevoegd)</skos:definition>' .
                '</rdf:Description>' .
                '</rdf:RDF>';


        self::$responseY = RequestResponse::CreateConceptRequest(self::$client, $xmlY, "false");
        print "\n Creation status for concept X: " . self::$responseY->getStatus();
        
    }

    public static function tearDownAfterClass() {
        if (self::$aboutX != null) {
            RequestResponse::DeleteRequest(self::$client, self::$aboutX);
        } else {
            print "\n The rdf-about of the concept X is null \n";
        }
        if (self::$aboutY != null) {
            RequestResponse::DeleteRequest(self::$client, self::$aboutY);
        } else {
            print "\n The rdf-about of the concept Y is null \n";
        }
    }
    
     public function testCreateRelationViaMultipartForm() {
        print "\n" . "Test: create relation X has narrower Y via multipart-form ";
        $this->AssertEquals(201, self::$responseX->getStatus(), "\n Cannot perform the test because something is wrong with creating a test concept X: " . self::$responseX->getHeader('X-Error-Msg'));
        $this->AssertEquals(201, self::$responseY->getStatus(), "\n Cannot perform the test because something is wrong with creating a test concept Y: " . self::$responseY->getHeader('X-Error-Msg'));
        $boundary = '36374246216810994721943965972';
        $body = '--36374246216810994721943965972
Content-Disposition: form-data; name="concept"
' . self::$aboutX . "\n" .
'--36374246216810994721943965972' . "\n" .
'Content-Disposition: form-data; name="type"
http://www.w3.org/2004/02/skos/core#narrower
--36374246216810994721943965972
Content-Disposition: form-data; name="related"
' . self::$aboutY ."\n" .
'--36374246216810994721943965972
Content-Disposition: form-data; name="tenant"
mi
--36374246216810994721943965972
Content-Disposition: form-data; name="key"
apikey
--36374246216810994721943965972--';
        $response = RequestResponse::CreateRelationMultipartFormRequest(self::$client, $body, $boundary);
        $this->AssertEquals(200, $response->getStatus(), $response->getHeader('X-Error-Msg'));
        // todo: add assertions
    }
    
    public function testCreateRelationURLEncoded() {
        print "\n" . "Test: create relation X has narrower Y via url-encoded parameter string  ";
        $this->AssertEquals(201, self::$responseX->getStatus(), "\n Cannot perform the test because something is wrong with creating a test concept X: " . self::$responseX->getHeader('X-Error-Msg'));
        $this->AssertEquals(201, self::$responseY->getStatus(), "\n Cannot perform the test because something is wrong with creating a test concept Y: " . self::$responseY->getHeader('X-Error-Msg'));
        $parameterString = 'content'. urlencode("=" . self::$aboutX). "&type". urlencode("=http://www.w3.org/2004/02/skos/core#narrower").
         'related'. urlencode("=" . self::$aboutY). "&tenant". urlencode("=". COLLECTION_1_tenant).
                '&key'. urlencode("=". API_KEY);
        $response = RequestResponse::CreateRelationURLEncodedRequest(self::$client, $parameterString);
        $this->AssertEquals(200, $response->getStatus(), $response->getHeader('X-Error-Msg'));
        // todo: add assertions
    }
    
    public function testCreateRelationWithParametersInRequest() {
        print "\n" . "Test: create relation X has narrower Y with parameter requests  ";
        $this->AssertEquals(201, self::$responseX->getStatus(), "\n Cannot perform the test because something is wrong with creating a test concept X: " . self::$responseX->getHeader('X-Error-Msg'));
        $this->AssertEquals(201, self::$responseY->getStatus(), "\n Cannot perform the test because something is wrong with creating a test concept Y: " . self::$responseY->getHeader('X-Error-Msg'));
        $response = RequestResponse::CreateRelationViaParametersRequest(self::$client, self::$aboutX, "http://www.w3.org/2004/02/skos/core#narrower", self::$aboutY);
        $this->AssertEquals(200, $response->getStatus(), $response->getHeader('X-Error-Msg'));
        // todo: add assertions
    }
    
}