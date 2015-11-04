<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class RequestResponse {
    
    public static function CreateConceptRequest($client, $xml, $autoGenerateIdentifiers) {
        $client->setUri(BASE_URI_ . "/public/api/concept?");
        
        $response = $client
                ->setEncType('text/xml')
                ->setRawData($xml)
                ->setParameterGet('tenant', TENANT)
                ->setParameterGet('collection', COLLECTION_1_code)
                ->setParameterGet('key', API_KEY)
                ->setParameterGet('autoGenerateIdentifiers', $autoGenerateIdentifiers)
                ->request('POST');

        return $response;
    }
    
    public static function CreateConceptNoApikeyRequest($client, $xml, $autoGenerateIdentifiers) {
        $client->setUri(BASE_URI_ . "/public/api/concept?");
        $client->setConfig(array(
            'maxredirects' => 0,
            'timeout' => 30));

        $response = $client
                ->setEncType('text/xml')
                ->setRawData($xml)
                ->setParameterGet('tenant', TENANT)
                ->setParameterGet('collection', COLLECTION_1_code)
                ->setParameterGet('autoGenerateIdentifiers', $autoGenerateIdentifiers)
                ->request('POST');

        return $response;
    }
    
    public static function GetCollectionOrInstitution($client, $requestString, $contentType) {
        $client->setUri($requestString);
        $client->setConfig(array(
            'maxredirects' => 0,
            'timeout' => 30));

        $client->SetHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
                'Content-Type' => $contentType,
            'Accept-Language'=>'nl,en-US,en',
            'Accept-Encoding'=>'gzip, deflate',
            'Connection'=>'keep-alive')
        );
        
        $response = $client -> request(Zend_Http_Client::GET);

        return $response;
    }
    
    public static function ImportConceptRequest($client, $postData, $boundary) {
        
        $client ->setUri(BASE_URI_ . '/public/editor/collections/import/collection/collection');
        $client->setConfig(array(
            'maxredirects' => 10,
            'timeout' => 300));
        $client->SetHeaders(array(
            'Accept' => 'application/xml+rdf',
                'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
            'Accept-Language'=>'nl,en-US,en',
            'Accept-Encoding'=>'gzip, deflate',
            'Host' => '192.168.99.100',
            'Connection'=>'keep-alive')
        );
        
        $client -> setRawData($postData);
        $response = $client -> request(Zend_Http_Client::POST);
        return $response;
    }
    
    public static function AutocomleteRequest($client, $word, $parameterString) {
        $uri = BASE_URI_ . '/public/api/autocomplete/' . $word .  $parameterString;
        $client ->setUri($uri);
        $response = $client -> request(Zend_Http_Client::GET);
        return $response;
    }
    
    public static function DeleteRequest($client, $id){
        $client ->setUri(BASE_URI_ . '/public/api/concept');
        $client->setConfig(array(
            'maxredirects' => 0,
            'timeout' => 30));
        $response = $client
                ->setParameterGet('tenant', TENANT)
                ->setParameterGet('collection', COLLECTION_1_code)
                ->setParameterGet('key', API_KEY)
                ->setParameterGet('id', $id)
                ->request('DELETE');
        return $response;
    }
    
    public static function ExportRequest($client, $conceptId, $fullFileName){

       $url = BASE_URI_ . '/public/editor/concept/export';
       $host = str_replace("http://", "", BASE_URI_);
       $currentURLpostParameter = urlencode(BASE_URI_ . '/public/editor#search/user' . USER_NUMBER . '/concept/' . $conceptId . '/');
       $client->setUri($url);
       $client->setConfig(array(
            'maxredirects' => 2,
            'timeout' => 30));
       $client->setHeaders(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
                'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept-Language'=>'en-US,en',
            'Accept-Encoding'=>'gzip, deflate',
            'Host' => $host)
        );
        
        $client -> SetParameterPost('fileName', $fullFileName);
        $client -> SetParameterPost('format', 'xml');
        $client -> SetParameterPost('maxDepth', 1);
        $client -> SetParameterPost('exportableFields', "");
        $client -> SetParameterPost('filedsToExport', "");
        $client -> SetParameterPost('type', 'concept');
        $client -> SetParameterPost('additionalData', $conceptId);
        $client -> SetParameterPost('currentUrl', $currentURLpostParameter);
        $client -> SetParameterPost('exportButton', 'Export');
        $response = $client->request('POST');
        return $response;
    }
    
    public static function setNamespaces() {
         $namespaces = array(
            "rdf" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
            "skos" => "http://www.w3.org/2004/02/skos/core#",
            "openskos" => "http://openskos.org/xmlns/openskos.xsd",
            "dc" => "http://purl.org/dc/elements/1.1/",
            "dcterms" => "http://purl.org/dc/terms/" 
        );
        return $namespaces;
    }
    
    public static function deleteConcepts($abouts, $client){
        foreach ($abouts as $about) {
            if ($about != null) {
                $response = RequestResponse::DeleteRequest($client, $about);
                if ($response->getStatus() != 200) {
                    Logging::failureMessaging($response, 'deleting test concept ' . $about);
                }
            }
        }
    }
    
    public static function jsonP_decode_parameters($input, $callbackName){
        $inputTrimmed = trim($input);
        $errorMessage = "The input value \n". $input. "\n is not a valid jsonp value. \n";
        $begin = strpos($inputTrimmed, $callbackName . '(');
        if ($begin != 0) {
            if (!$begin) {
                print $errorMessage;
                print "\n Reason: it does not contain <callbackname>( \n";
                return null;
            }
            print $errorMessage;
            print "\n Reason: it does not start with <callbackname>( \n";
            return null;
        }
        $end = strrpos($inputTrimmed, ");");
        if ($end != strlen($inputTrimmed) - 2) {
            if (!$end) {
                print $errorMessage;
                print "\n Reason: it does not contain ); \n";
                return null;
            }
            print $errorMessage;
            print "\n Reason: it does not end with ); \n";
            return null;
        }
        $length = strlen($inputTrimmed) - (strlen($callbackName)+1) -2; // the input string should start with <callbackname( and end with );
        $parameters = substr($inputTrimmed, strlen($callbackName)+1, $length);
        return json_decode($parameters, true);
    }
    
    public static function getByIndex($list, $index) {
        if ($index < 0 || $index >= count($list)) {
            return null;
        }
        $list->rewind();
        $i = 0;
        while ($i < $index) {
            $list->next();
            $i++;
        }
        return $list->current();
    }

}