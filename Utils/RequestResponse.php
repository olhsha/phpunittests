<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class RequestResponse {
    
    public static function CreateConceptRequest($client, $xml, $autoGenerateIdentifiers) {
        $client->setUri(BASE_URI_ . "/public/api/concept?");
        $client->setConfig(array(
            'maxredirects' => 0,
            'timeout' => 30));

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
    
    public static function GetCollection($client, $requestString, $contentType) {
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
    
    public static function setNamespaces() {
         $namespaces = array(
            "rdf" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
            "skos" => "http://www.w3.org/2004/02/skos/core#",
            "openskos" => "http://openskos.org/xmlns/openskos.xsd"
             //"dc" => "http://purl.org/dc/elements/1.1/"
        );
        return $namespaces;
    }

}