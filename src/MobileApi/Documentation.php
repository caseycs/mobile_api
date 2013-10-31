<?php
namespace MobileApi;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Documentation
{
    const ROUTE = 'documentation';
    const SYSTEM_RESPONSE_PREFIX = '\\MobileApi\\Message\\Response\\';
    const COMMENT_PREFIX_PROPERTY = 'property.';
    const COMMENT_PREFIX_ENUM = 'enum.';

    private $url, $requests, $responses, $request_prefix, $response_prefix;

    private $responses_system = array(
        'ErrorMobileApi_1',
        'ErrorUploadMobileApi_1',
    );

    public function __construct($title, $url, $request_prefix, array $requests, $response_prefix, array $responses)
    {
        $this->title = $title;
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
            return $this->requestsList();
        }
    }

    private function requestsList()
    {
        $data = array(
            'project' => $this->title,
            'meta_title' => $this->title,
        );

        sort($this->requests);
        foreach ($this->requests as $name) {
            $class = $this->request_prefix . '\\' . $name;

            if (!class_exists($class)) {
                return new Response("request class {$class} not found", 500);
            }

//            $Message = new $class;
            $data['requests'][] = array('name' => $name, 'url' => $this->url . '?request=' . $name);
        }

        $html = $this->getMustache()->loadTemplate('index')->render($data);

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

        $data = array(
            'project' => $this->title,
            'meta_title' => $name . ' - ' . $this->title,
            'url' => $this->url,
            'request' => $name,
        );

        /* @var Message\Request\RequestInterface $Message */
        $Message = new $class;
        $data += $this->processStructureAndDescription($Message);

        foreach ($this->responses_system as $name) {
            $data['responses_system'][] = array(
                'name' => $name,
                'url' => $this->url . '?response=' . $name,
            );
        }

        $tmp = $Message->getAvailableResponses();
        sort($tmp);
        foreach ($tmp as $name) {
            $data['responses_application'][] = array(
                'name' => $name,
                'url' => $this->url . '?response=' . $name,
            );
        }

        $html = $this->getMustache()->loadTemplate('request')->render($data);

        return new Response($html);
    }

    private function response($name)
    {
        if (in_array($name, $this->responses_system)) {
            $class = self::SYSTEM_RESPONSE_PREFIX . $name;
        } else {
            if (!in_array($name, $this->responses)) {
                return new Response("response {$name} not found", 404);
            }

            $class = $this->response_prefix . '\\' . $name;
            if (!class_exists($class)) {
                return new Response("response class {$name} not found", 500);
            }
        }

        $data = array(
            'project' => $this->title,
            'meta_title' => $name . ' - ' . $this->title,
            'url' => $this->url,
            'response' => $name,
        );

        $Message = new $class;
        $data += $this->processStructureAndDescription($Message);

        $html = $this->getMustache()->loadTemplate('response')->render($data);

        return new Response($html);
    }

    private function explainStructure(Message\MessageInterface $Message, array $comments)
    {
        $result = array();

        foreach ($Message->getStructure() as $field => $params) {
            $this->explainField($field, $params, $result, $comments, array());
        }

        return $result;
    }

    private function getMustache()
    {
        $mustache = new \Mustache_Engine(array(
            'loader' => new \Mustache_Loader_FilesystemLoader(__DIR__ . '/../../mustache'),
            'partials_loader' => new \Mustache_Loader_FilesystemLoader(__DIR__ . '/../../mustache/partials'),
            'logger' => new \Mustache_Logger_StreamLogger('php://stderr'),
            'strict_callables' => true,
        ));
        return $mustache;
    }

    private function processStructureAndDescription(Message\MessageInterface $Message)
    {
        $ReflectionClass = new \ReflectionClass($Message);

        $comment_message = $this->processDocComment($ReflectionClass->getDocComment());

        //extract constants values and fields comments
        $a = strpos($comment_message, PHP_EOL . PHP_EOL . self::COMMENT_PREFIX_PROPERTY);
        $b = strpos($comment_message, PHP_EOL . PHP_EOL . self::COMMENT_PREFIX_ENUM);
        $tmp2 = min(array($a, $b));

        $comments = array();
        if ($tmp2) {
            $comment_fields = trim(substr($comment_message, $tmp2));
            $comment_message = substr($comment_message, 0, $tmp2);

            $pattern = '~((?:' . self::COMMENT_PREFIX_ENUM . '|' . self::COMMENT_PREFIX_PROPERTY . ')[0-9a-z\._]+)~i';
            $comment_fields = preg_split($pattern, $comment_fields, -1, \PREG_SPLIT_DELIM_CAPTURE | \ PREG_SPLIT_NO_EMPTY);
            do {
                $field = current($comment_fields);
                $description = next($comment_fields);

                if (trim($field) && trim($description)) {
                    $comments[trim($field)] = trim($description);
                }
            } while (next($comment_fields));
        }

        $result = array(
            'description' => nl2br($comment_message),
            'structure' => $this->explainStructure($Message, $comments),
        );

        if ($Message instanceof Message\Request\UploadInterface) {
            $result['upload_interface_field'] = Application::UPLOAD_FIELD;
        }

        return $result;
    }

    private function processDocComment($string)
    {
        if (0 === strpos($string, "/**\n")) {
            $string = preg_replace('|^\/\*+|', '', $string);
            $string = preg_replace('|\*+\/$|', '', $string);
            $string = preg_replace("|\n\s+\* ?|", PHP_EOL, $string);
            $string = trim($string);
        }
        return $string;
    }

    private function explainField($name, $params, array &$result, array $comments, array $parents)
    {
        $tmp2 = 'property.' . ($parents ? join('.', $parents) . '.' : '') . $name;
        $description = isset($comments[$tmp2]) ? $comments[$tmp2] : '';

        $tmp = array(
            'padding_left' => count($parents) * 2,
            'name' => $name,
            'presence' => $params[0],
            'type' => $params[1],
            'description' => nl2br($description),
        );

        if ($params[1] === Message\Field::ENUM) {
            foreach ($params[2] as $value) {
                $tmp2 = 'enum.' . ($parents ? join('.', $parents) . '.' : '') . $name . '.' . (string)$value;
                $description = isset($comments[$tmp2]) ? $comments[$tmp2] : '';

                $tmp['enum']['values'][] = array(
                    'name' => $value,
                    'description' => nl2br($description),
                );
            }
        }

        $result[] = $tmp;

        if ($params[1] === Message\Field::ASSOC) {
            foreach ($params[2] as $name2 => $params2) {
                $tmp = $parents;
                $tmp[] = $name;
                $this->explainField($name2, $params2, $result, $comments, $tmp);
            }
        }
    }
}
