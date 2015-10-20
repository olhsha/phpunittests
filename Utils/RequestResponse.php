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
    
    
    
    public static function setNamespaces() {
         $namespaces = array(
            "rdf" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
            "skos" => "http://www.w3.org/2004/02/skos/core#",
            "openskos" => "http://openskos.org/xmlns/openskos.xsd"
        );
        return $namespaces;
    }

}