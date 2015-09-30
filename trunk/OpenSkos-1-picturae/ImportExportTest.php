<?php

require_once dirname(__DIR__) . '/Utils/Authenticator.php';
require_once dirname(__DIR__) . '/Utils/Logging.php';

class ImportExportTest extends PHPUnit_Framework_TestCase {

    protected $notationg = "";
    
    public function testImportDefault() {
        print "\n" . "Testing import (default status) ... ";
        $client = Authenticator::authenticate();

        //create import job
        $response = $this->importFromRawData($client, 'default');

        // send import job 
        if ($response->getStatus() == 200) {
            $output = array(
                '0' => "The ouput of sending jobs: "
            );
            $retvar = 0;

            $sendjob = exec(PHP_JOBS_PROCESS, $output, $retvar);
        }

        // retrieve imported concept 
        $dom = $this->retrieveConceptDomViaNotation($client, $this->notationg, 'application/xml+rdf');
        // Asserting
        
        // Assert0
        $results0 = $dom->query('rdf:RDF');
        $checkCount = $results0->current()->getAttribute('openskos:numFound');
        $this -> AssertEquals(1, $checkCount, "\n found " .  $checkCount . " concepts with notation " . $this->notationg . ".\n");
        
        // Assert - 1 
        $results1 = $dom->query('skos:notation');
        $this->AssertEquals(1, count($results1));
        $this->AssertEquals($this->notationg, $results1->current()->nodeValue);

        // Assert - 2 
        $results2 = $dom->query('skos:inScheme');
        $this->AssertEquals(8, count($results2));
        $i = 0;
        foreach ($results2 as $result) {
            $i++;
            $this->assertStringStartsWith(HANDLE_CCR_PREFIX, $result->getAttribute('rdf:resource'), "The $i-th attribute does not start with " . HANDLE_CCR_PREFIX);
        }
    }
    
    public function testImportApproved() {
        print "\n" . "Testing import (approved concept) ... ";
        $client = Authenticator::authenticate();

        //create import job
        $response = $this->importFromRawData($client, 'approved');

        // send import job 
        if ($response->getStatus() == 200) {
            $output = array(
                '0' => "The ouput of sending jobs: "
            );
            $retvar = 0;

            $sendjob = exec(PHP_JOBS_PROCESS, $output, $retvar);
        }

        // retrieve imported concept 
        $dom = $this->retrieveConceptDomViaNotation($client, $this->notationg, 'application/xml+rdf');
        // Asserting
        
        // Assert0
        $results0 = $dom->query('rdf:RDF');
        $checkCount = $results0->current()->getAttribute('openskos:numFound');
        $this -> AssertEquals(1, $checkCount, "\n found " .  $checkCount . " concepts with notation " . $this->notationg . ".\n");
        
        // Assert - 1 
        $results1 = $dom->query('skos:notation');
        $this->AssertEquals(1, count($results1));
        $this->AssertEquals($this->notationg, $results1->current()->nodeValue);

        // Assert - 2 
        $results2 = $dom->query('skos:inScheme');
        $this->AssertEquals(8, count($results2));
        $i = 0;
        foreach ($results2 as $result) {
            $i++;
            $this->assertStringStartsWith(HANDLE_CCR_PREFIX, $result->getAttribute('rdf:resource'), "The $i-th attribute does not start with " . HANDLE_CCR_PREFIX);
        }
    }
    
    public function testImportCandidate() {
        print "\n" . "Testing import candidate ... ";
        $client = Authenticator::authenticate();

        //create import job
        $response = $this->importFromRawData($client, 'candidate');

        // send import job 
        if ($response->getStatus() == 200) {
            $output = array(
                '0' => "The ouput of sending jobs: "
            );
            $retvar = 0;

            $sendjob = exec(PHP_JOBS_PROCESS, $output, $retvar);
        }

        // retrieve imported concept 
        $dom = $this->retrieveConceptDomViaNotation($client, $this->notationg, 'application/xml+rdf');
        // Asserting
        
        // Assert0
        $results0 = $dom->query('rdf:RDF');
        $checkCount = $results0->current()->getAttribute('openskos:numFound');
        $this -> AssertEquals(0, $checkCount, "\n found " .  $checkCount . " concepts with notation " . $this->notationg . ".\n");
        
    }

    public function testExport() {

        $clientI = Authenticator::authenticate();

        //importing
        $responseImport = $this->importFromRawData($clientI, 'default');
        print "\n Preparing test, step 1: make import job response: " . $responseImport->getStatus();
        $sendjob = exec(PHP_JOBS_PROCESS);
        print "\n Preparing test, step 2: sent import job. \n";

        print "\n" . "Testing export ... ";
        $client = Authenticator::authenticate();
        $conceptIdJson = $this->retrieveConceptIdFromNotation($client, $this->notationg, 'application/json');
        $conceptId = json_decode($conceptIdJson);
        $docs = $conceptId->response->docs;
        $uuid = $docs[0]->uuid;
        print "\n Fresh concept id: ";
        var_dump($uuid);
        print "\n ";
        // exporting test concept
        $fileName = 'export_' . $uuid;
        print "\n If the data are big, they should be written to " . $fileName;
        print "\n Otherwise they are exported as stream in the response's body. \n";

        $response = $this->exportConcept($client, $uuid, $fileName);

        print "\n Export response status: " . $response->GetStatus();
        print "\n Export response message: " . $response->GetMessage();

        // retrieving the data from the exported file
        $dom = new Zend_Dom_Query();
        $dom->setDocumentXML($response->GetBody());

        // comparing test xml with the data from the xml from the file
        // Asserting
        // Assert - 1 
        $results1 = $dom->query('skos:notation');
        $this->AssertEquals(1, count($results1));
        $this->AssertEquals($this->notationg, $results1->current()->nodeValue);

        // Assert - 2 
        $results2 = $dom->query('skos:inScheme');
        $this->AssertEquals(8, count($results2));
        $i = 0;
        foreach ($results2 as $result) {
            $i++;
            $this->assertStringStartsWith(HANDLE_CCR_PREFIX, $result->getAttribute('rdf:resource'), "The $i-th attribute does not start with " . HANDLE_CCR_PREFIX);
        }
    }

    private function retrieveConceptDomViaNotation($client, $notation, $accept) {
        $uri = BASE_URI_ . '/public/api/find-concepts?q=notation:' . $notation;
        $body = $this->retrieveConceptAsResponseBody($client, $uri, $accept);
        $dom = new Zend_Dom_Query();
        $dom->setDocumentXML($body);
        return $dom;
    }

    private function retrieveConceptIdFromNotation($client, $notation, $accept) {
        $uri = BASE_URI_ . '/public/api/find-concepts?q=notation:' . $notation . "&fl=uuid&format=json";
        return $this->retrieveConceptAsResponseBody($client, $uri, $accept);
    }

    private function retrieveConceptAsResponseBody($client, $uri, $accept) {
        print "\n Retrieving " . $uri;
        $client->setUri($uri);
        $client->setConfig(array(
            'maxredirects' => 1,
            'timeout' => 300));

        $client->SetHeaders('Accept', $accept);
        $response = $client->request();
        print "\n Retrieving concept, status : " . $response->getStatus();
        print "\n Retrieving concept, message: " . $response->getMessage() . "\n";
        return $response->getBody();
    }

    private function exportConcept($client, $conceptId, $fullFileName) {

        $url = BASE_URI_ . '/public/editor/concept/export';
        $currentURLpostParameter = EXPORT_PARAMETER_CURRENT_URL_PREFIX . USER_NUMBER . "%2Fconcept%2F" . $conceptId . "%2F";
        $client->setUri($url);
        $client->setConfig(array(
            'maxredirects' => 1,
            'timeout' => 30));
        $client->setHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept-Language' => 'en-US,en',
            'Accept-Encoding' => 'gzip, deflate')
        );

        $client->SetParameterPost('fileName', $fullFileName);
        $client->SetParameterPost('format', 'xml');
        $client->SetParameterPost('maxDepth', 1);
        $client->SetParameterPost('exportableFields', "");
        $client->SetParameterPost('filedsToExport', "");
        $client->SetParameterPost('type', 'concept');
        $client->SetParameterPost('additionalData', $conceptId);
        $client->SetParameterPost('currentUrl', $currentURLpostParameter);
        $client->SetParameterPost('exportButton', 'Export');
        $response = $client->request('POST');
        return $response;
    }

    private function importFromRawData($client, $conceptStatus) {
        $randomn = rand(0, 2048);
        $tag = 'Import' . $randomn;
        $notation = 'textCorpus' . $randomn;
        $uuid = uniqid(UUID_PREFIX);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' .
                '<rdf:RDF  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:skos="http://www.w3.org/2004/02/skos/core#">' .
                '<rdf:Description xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" rdf:about="' . $uuid . '">' .
                '<rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' .
                '<skos:notation>' . $notation . '</skos:notation>' .
                '<skos:inScheme rdf:resource="http://hdl.handle.net/11148/CCR_P-DialogueActs_1bb8b49f-7260-6731-6479-408c29cead73"/>' .
                '<skos:inScheme rdf:resource="http://hdl.handle.net/11148/CCR_P-LexicalSemantics_0d519a3c-85a6-ea17-d93c-8b89339ffc88"/>' .
                '<skos:inScheme rdf:resource="http://hdl.handle.net/11148/CCR_P-Metadata_deedbe7e-9a1d-4388-2857-ad0daaf06793"/>' .
                '<skos:inScheme rdf:resource="http://hdl.handle.net/11148/CCR_P-Morphosyntax_c99c78ee-1425-c8f3-33e3-fe2a4b2ec7ca"/>' .
                '<skos:inScheme rdf:resource="http://hdl.handle.net/11148/CCR_P-SemanticContentRepresentation_37ab80c4-cf9f-18dd-c319-e6554b1d9462"/>' .
                '<skos:inScheme rdf:resource="http://hdl.handle.net/11148/CCR_P-Syntax_ba63dab1-103c-f8ee-81c3-f32a101e5c96"/>' .
                '<skos:inScheme rdf:resource="http://hdl.handle.net/11148/CCR_P-Terminology_bf8fdd3f-0075-bca2-ae35-1070be98f856"/>' .
                '<skos:inScheme rdf:resource="http://hdl.handle.net/11148/CCR_P-Translation_d8214c27-7c8f-9d05-e6ca-ea8fdc922a1c"/>' .
                '<skos:topConceptOf rdf:resource="http://hdl.handle.net/11148/CCR_P-Metadata_deedbe7e-9a1d-4388-2857-ad0daaf06793"/>' .
                '<skos:topConceptOf rdf:resource="http://hdl.handle.net/11148/CCR_P-Terminology_bf8fdd3f-0075-bca2-ae35-1070be98f856"/>' .
                '<skos:topConceptOf rdf:resource="http://hdl.handle.net/11148/CCR_P-Morphosyntax_c99c78ee-1425-c8f3-33e3-fe2a4b2ec7ca"/>' .
                '<skos:topConceptOf rdf:resource="http://hdl.handle.net/11148/CCR_P-SemanticContentRepresentation_37ab80c4-cf9f-18dd-c319-e6554b1d9462"/>' .
                '<skos:topConceptOf rdf:resource="http://hdl.handle.net/11148/CCR_P-Syntax_ba63dab1-103c-f8ee-81c3-f32a101e5c96"/>' .
                '<skos:topConceptOf rdf:resource="http://hdl.handle.net/11148/CCR_P-LexicalSemantics_0d519a3c-85a6-ea17-d93c-8b89339ffc88"/>' .
                '<skos:topConceptOf rdf:resource="http://hdl.handle.net/11148/CCR_P-DialogueActs_1bb8b49f-7260-6731-6479-408c29cead73"/>' .
                '<skos:topConceptOf rdf:resource="http://hdl.handle.net/11148/CCR_P-Translation_d8214c27-7c8f-9d05-e6ca-ea8fdc922a1c"/>' .
                '<skos:prefLabel xml:lang="en">' . $tag . '</skos:prefLabel>' .
                '<skos:scopeNote xml:lang="en">A text corpus may be limited according to aspects of subject fields, size or time, e.g. mathematical texts, certain periodicals from 1986 onwards. It is used as source material for further linguistic analysis or terminology work. (source: ISO 1087-2, 2.7)</skos:scopeNote>' .
                '<skos:definition xml:lang="en">A systematic collection of machine-readable texts or parts of text prepared, coded and stored according to predefined rules. (source: ISO 1087-2, 2.7)</skos:definition>' .
                '<dcterms:modified>2015-01-20T13:09:14Z</dcterms:modified>' .
                '</rdf:Description>' .
                '</rdf:RDF>';
        $boundary = '36374246216810994721943965972';

        // importeren
        $uri = BASE_URI_ . '/public/editor/collections/import/collection/' . COLLECTION_1_code;
        $client->setUri($uri);
        print "\n Import request uri: " .$uri . "\n";
        $client->setConfig(array(
            'maxredirects' => 2,
            'timeout' => 30));
        $client->SetHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml+rdf',
            'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
            'Accept-Language' => 'en-US,en',
            'Accept-Encoding' => 'gzip, deflate',
            'Connection' => 'keep-alive')
        );
        
        $boundary = "--" . $boundary;
        $boundaryLn = $boundary . "\n";
        $lnBoundaryLn = "\n" . $boundaryLn;

        $part1 = 'Content-Disposition: form-data; name="MAX_FILE_SIZE"
           

10485760';

        $part2 = 'Content-Disposition: form-data; name="xml"; filename="tttt.xml"
Content-Type: text/xml

' . $xml;

        if ($conceptStatus === 'approved') {
            $part3 = 'Content-Disposition: form-data; name="status
                
approved' . $lnBoundaryLn ;
        } else  if ($conceptStatus === 'candidate') {
            $part3 = 'Content-Disposition: form-data; name="status"

candidate' . $lnBoundaryLn;
        } 
         else if ($conceptStatus === 'default') {
          $part3 ="";   
    } else {
        print "\n Checking import with status $conceptStatus not implemented."; 
        return null;
}
        
        
        $part4 = 'Content-Disposition: form-data; name="ignoreIncomingStatus"

0';

        $part5 = 'Content-Disposition: form-data; name="lang"

en';

        $part6 = 'Content-Disposition: form-data; name="toBeChecked"

0';

        $part7 = 'Content-Disposition: form-data; name="purge"

0';

        $part8 = 'Content-Disposition: form-data; name="onlyNewConcepts"

0';

        $part9 = 'Content-Disposition: form-data; name="submit"

Submit';

        $postData = $boundaryLn . $part1 . $lnBoundaryLn . $part2 . $lnBoundaryLn . $part3 .  
                $part4 . $lnBoundaryLn . $part5 . $lnBoundaryLn . $part6 . $lnBoundaryLn .
                $part7 . $lnBoundaryLn . $part8 . $lnBoundaryLn . $part9 . "\n" . $boundary . "--";
                
        $client->setRawData($postData);
        $response = $client->request(Zend_Http_Client::POST);
        $status = $response->GetStatus();
        print "\n Import response status: " . $status;
        print "\n Import response message: " . $response->GetMessage();
        //Logging :: var_error_log("\n Response body ", $response->getBody(), dirname(__DIR__) . "/OpenSkos-1-picturae/ImportResponse.html");
        if ($status == 200) {
            $this->notationg = $notation;
            print "\n Imported concept's tag is " . $tag;
            print "\n Imported concept's notation is " . $notation;
        }
        return $response;
    }

}

?>
