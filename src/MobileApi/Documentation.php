<?php
namespace MobileApi;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Documentation
{
    const ROUTE = 'documentation';

    private $url, $requests, $responses, $request_prefix, $response_prefix;

    public function __construct($url, $request_prefix, array $requests, $response_prefix, array $responses)
    {
        $this->url = $url;

        $this->request_prefix = $request_prefix;
        $this->requests = $requests;

        $this->response_prefix = $response_prefix;
        $this->responses = $responses;
    }

    public function show(Request $Request)
    {
        if ($Request->query->has('request')) {
            return $this->request($Request->query->get('request'));
        } elseif ($Request->query->has('response')) {
            return $this->response($Request->query->get('response'));
        } else {
            return $this->requests();
        }
    }

    private function requests()
    {
        $html = '';
        foreach ($this->requests as $name) {
            $class = $this->request_prefix . '\\' . $name;

            if (!class_exists($class)) {
                return new Response("request class {$name} not found", 500);
            }

            $Message = new $class;
            $html .= get_class($Message);
        }
        return new Response($html);
    }

    private function request($name)
    {
        if (!in_array($name, $this->requests)) {
            return new Response("request {$name} not found", 404);
        }

        $class = $this->request_prefix . '\\' . $name;
        if (!class_exists($class)) {
            return new Response("request class {$name} not found", 500);
        }

        $Message = new $class;
        $html = '';
        $html .= $this->explainRequest($name, $Message);
        return new Response($html);
    }

    private function response($name)
    {
        if (!in_array($name, $this->responses)) {
            return new Response("response {$name} not found", 404);
        }

        $class = $this->response_prefix . '\\' . $name;
        if (!class_exists($class)) {
            return new Response("response class {$name} not found", 500);
        }

        $Message = new $class;
        $html = '';
        $html .= $this->explainResponse($name, $Message);
        return new Response($html);
    }

    private function explainRequest($name, Message\Request\RequestInterface $Message)
    {
        $html = $name . '=';
        $html .= get_class($Message);
        $html .= json_encode($Message->getStructure());
        $html .= json_encode($Message->getAvailableResponses());
        return $html;
    }

    private function explainResponse($name, Message\Response\ResponseInterface $Message)
    {
        $html = $name . '=';
        $html .= get_class($Message);
        $html .= json_encode($Message->getStructure());
        return $html;
    }
}
