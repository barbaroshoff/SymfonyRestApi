<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestHandler implements RequestHandlerInterface
{
    private $responseFormat;

    public function handleRequest(Request $request)
    {
        $this->responseFormat = $request->headers->get('Accept') === 'application/xml' ? 'xml' : 'json';

        $jsonData = $request->getContent();
        $requestData = json_decode($jsonData, true);

        $author = $request->query->get('authorSearch');
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');

        $requestData['authorSearch'] = $author;
        $requestData['limit'] = $limit;
        $requestData['offset'] = $offset;

        return $requestData;
    }

    public function createResponse(array $data): Response
    {
        if ($this->responseFormat === 'xml') {
            $response = new Response($this->arrayToXml($data));
            $response->headers->set('Content-Type', 'application/xml');
        } else {
            $response = new Response(json_encode($data));
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }

    function arrayToXml(array $data, \SimpleXMLElement $xml = null): string
    {
        if ($xml === null) {
            $xml = new \SimpleXMLElement('<books/>');
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }

        return $xml->asXML();
    }
}