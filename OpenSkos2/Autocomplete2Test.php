<?php

require_once dirname(__DIR__) . '/Utils/RequestResponse.php'; 
require_once dirname(__DIR__) . '/Utils/Logging.php';
class Autocomplete2Test extends PHPUnit_Framework_TestCase {
  
    private static $client;
    private static $success;
    private static $prefs;
    private static $labelMap;
    private static $abouts;
    
    public static function setUpBeforeClass() {
       
        self::$client = new Zend_Http_Client();
        self::$client->setConfig(array(
            'maxredirects' => 0,
            'timeout' => 30));
        self::$client->SetHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
                'Content-Type' => 'application/json',
            'Accept-Language'=>'nl,en-US,en',
            'Accept-Encoding'=>'gzip, deflate',
            'Connection'=>'keep-alive')
        );
        self::$abouts = array();
        self::$labelMap = array (
           PREF_LABEL => PREF_LABEL . "_",
           ALT_LABEL => ALT_LABEL . "_",
           HID_LABEL => HID_LABEL . "_",
           NOTATION => NOTATION . "_",
        );
   
        // create test concepts
        
        $letters = range('a', 'z'); 
        self::$prefs[0] = uniqid();
        $i = 1;
        
        foreach ($letters as $letter) {
            self::$prefs[$i] = self::$prefs[$i-1] . $letter;
            $randomn = rand(0, 10000);
            $prefLabel = self::$labelMap[PREF_LABEL] . self::$prefs[$i] . $randomn;
            $altLabel = self::$labelMap[ALT_LABEL] . self::$prefs[$i] . $randomn;
            $hiddenLabel = self::$labelMap[HID_LABEL] . self::$prefs[$i] . $randomn;
            $notation = self::$labelMap[NOTATION]  . self::$prefs[$i] . $randomn;
            $uuid = uniqid();
            $about = BASE_URI_ . CONCEPT_collection . "/" . $notation;
            $xml = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:openskos="http://openskos.org/xmlns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmi="http://dublincore.org/documents/dcmi-terms/#">' .
                    '<rdf:Description rdf:about="' . $about . '">' .
                    '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                    '<skos:prefLabel xml:lang="nl">' . $prefLabel . '</skos:prefLabel>' .
                    '<skos:altLabel xml:lang="nl">' . $altLabel . '</skos:altLabel>' .
                    '<skos:hiddenLabel xml:lang="nl">' . $hiddenLabel . '</skos:hiddenLabel>' .
                    '<openskos:set rdf:resource="' . BASE_URI_ . CONCEPT_collection . '"/>' .
                    '<openskos:uuid>' . $uuid . '</openskos:uuid>' .
                    '<openskos:status>approved</openskos:status>' .
                    '<openskos:tenant> ' . COLLECTION_1_tenant . '</openskos:tenant>' .
                    '<skos:notation>' . $notation . '</skos:notation>' .
                    '<skos:inScheme  rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                    '<skos:topConceptOf rdf:resource="http://data.beeldengeluid.nl/gtaa/Onderwerpen"/>' .
                    '<skos:definition xml:lang="nl">testje (voor def ingevoegd)</skos:definition>' .
                    '</rdf:Description>' .
                    '</rdf:RDF>';

            $response0 = RequestResponse::CreateConceptRequest(self::$client, $xml, "false");
            if ($response0 ->getStatus() !=201) {
                array_push(self::$abouts,$about);  
                Logging::failureMessaging($response0, "creating test concept");
                self::$success = false;
                return;
            } 
            $i++;
        }
          
        self::$success = true;
         
    }
    
    // delete all created concepts
    public static function tearDownAfterClass() {
        RequestResponse::deleteConcepts(self::$abouts, self::$client);
    }
    
    
    public function testAutocompleteInLoopNoParams() {
        print "\n testAutocomplete in loop ";
         if (self::$success) {
            $numPrefixes = count(self::$prefs);
            $lim = $numPrefixes - 1; // must be 26
            for ($i = 1; $i <= $lim; $i++) {
                $word = self::$labelMap[PREF_LABEL] . self::$prefs[$i];
                $response = RequestResponse::AutocomleteRequest(self::$client, $word, "");
                if ($response->getStatus() != 200) {
                    Logging::failureMessaging($response);
                }
                $this->AssertEquals(200, $response->getStatus());
                $json = $response->getBody();
                $arrayjson = json_decode($json, true);
                //var_dump($array);
                // todo: for now the spec is unclear. correct after the spec is clarified.
                $this->AssertEquals($numPrefixes - $i, count($arrayjson));
            }
        } else {
            print "\n Cannot perform the test because something is wrong with creating test concepts, see above. \n ";
        }
    }
    
    
    public function testAutocompleteSearchAltLabel() {
        print "\n testAutocomplete search alt Label \n";
        if (self::$success) {
            $word = self::$labelMap[ALT_LABEL] . self::$prefs[1]; // prefLabel<someuuid>a.
            //print "\n $word \n";
            $response = RequestResponse::AutocomleteRequest(self::$client, $word, "?searchLabel=altLabel");
            if ($response->getStatus() != 200) {
                Logging::failureMessaging($response, 'autocomplete on word '. $word);
            }
            $this->AssertEquals(200, $response->getStatus());
            $json = $response->getBody();
            //var_dump($json);
            $arrayjson = json_decode($json, true);
            $this->AssertEquals(26, count($arrayjson));
        } else {
            print "\n Cannot perform the test because something is wrong with creating test concepts, see above. \n ";
        }
    }
    
    public function testAutocompleteSearchAltLabelWithNoOccurences() {
        print "\n testAutocomplete search alt Label";
        if (self::$success) {
            $searchword = self::$labelMap[PREF_LABEL] . self::$prefs[1]; // should not occur in alt labels
            $response = RequestResponse::AutocomleteRequest(self::$client, $searchword, "?searchLabel=altLabel");
            if ($response->getStatus() != 200) {
                Logging::failureMessaging($response , 'autocomplete on word '. $searchword);
            }
            $this->AssertEquals(200, $response->getStatus());
            $json = $response->getBody();
            $arrayjson = json_decode($json, true);
            $this->AssertEquals(0, count($arrayjson));
        } else {
            print "\n Cannot perform the test because something is wrong with creating test concepts, see above. \n ";
        }
    }
    
    
     public function testAutocompleteReturnAltLabel() {
        print "\n testAutocomplete return alt Label";
        if (self::$success) {
            $searchword = self::$labelMap[PREF_LABEL] . self::$prefs[1]; // prefLabel_<someuuid>a.
            $returnword = self::$labelMap[ALT_LABEL] . self::$prefs[1]; // altLabel_<someuuid>a.
            $response = RequestResponse::AutocomleteRequest(self::$client, $searchword, "?returnLabel=altLabel");
            if ($response->getStatus() != 200) {
                Logging::failureMessaging($response , 'autocomplete on word '. $searchword);
            }
            $this->AssertEquals(200, $response->getStatus());
            $json = $response->getBody();
            $arrayjson = json_decode($json, true);
            $this->AssertEquals(26, count($arrayjson));
            for ($i=0; $i<count($arrayjson); $i++){
               $this ->assertStringStartsWith($returnword, $arrayjson[$i]);
           }
        } else {
            print "\n Cannot perform the test because something is wrong with creating test concepts, see above. \n ";
        }
    }
     
    
    public function testAutocompleteLangNL() {
        print "\n testAutocomplete search pref Label";
        if (self::$success) {
            $word = self::$labelMap[PREF_LABEL] . self::$prefs[1]; // prefLabel<someuuid>a.
            $response = RequestResponse::AutocomleteRequest(self::$client, $word, "?lang=nl");
            if ($response->getStatus() != 200) {
                Logging::failureMessaging($response , 'autocomplete on word '. $word . "?lang=nl");
            }
            $this->AssertEquals(200, $response->getStatus());
            $json = $response->getBody();
            $arrayjson = json_decode($json, true);
            $this->AssertEquals(26, count($arrayjson));
        } else {
            print "\n Cannot perform the test because something is wrong with creating test concepts, see above. \n ";
        }
    }
    
    // to do: make more advanced test with "en" non-zero occurences or so
    public function testAutocompleteLangEN() {
        print "\n testAutocomplete search pref Label";
        if (self::$success) {
            $word = self::$labelMap[PREF_LABEL] . self::$prefs[1]; // prefLabel<someuuid>a.
            $response = RequestResponse::AutocomleteRequest(self::$client, $word, "?lang=en");
            if ($response->getStatus() != 200) {
                 Logging::failureMessaging($response , 'autocomplete on word '. $word . "?lang=en (does not exists)");
            }
            $this->AssertEquals(200, $response->getStatus());
            $json = $response->getBody();
            $arrayjson = json_decode($json, true);
            $this->AssertEquals(0, count($arrayjson));
        } else {
            print "\n Cannot perform the test because something is wrong with creating test concepts, see above. \n ";
        }
    }
   
    
    public function testAutocompleteFormatHTML() {
        print "\n testAutocomplete search pref Label";
        if (self::$success) {
            $word = self::$labelMap[PREF_LABEL] . self::$prefs[1]; // prefLabel<someuuid>a.
            $response = RequestResponse::AutocomleteRequest(self::$client, $word, "?format=html");
            if ($response->getStatus() != 200) {
                Logging::failureMessaging($response , 'autocomplete on word '. $word . "?format=html");
            }
            $this->AssertEquals(200, $response->getStatus());
            // todo: add some chek when it becomes clear how the ourput looks like
        } else {
            print "\n Cannot perform the test because something is wrong with creating test concepts, see above. \n ";
        }
    }
    
  
}