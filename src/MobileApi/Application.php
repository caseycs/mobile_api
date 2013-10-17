<?php
namespace MobileApi;

use MobileApi\Message\Request\UploadInterface;
use MobileApi\Message\Response\ErrorUploadMobileApi_1;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use MobileApi\Message\Response\ErrorMobileApi_1;

class Application implements HttpKernelInterface
{
    /**
     * @var Request
     */
    private $Request;

    /**
     * @var Message\Request\RequestInterface
     */
    private $ApiRequest;

    /**
     * @var HandlerInterface
     */
    private $preHandler;

    /* @var string */
    private $controller_prefix, $message_request_prefix;

    /* @var array */
    private $controllers;

    /* @var bool */
    private
        $use_bson = false,
        $response_bson = false;

    public function setControllerPrefix($controller_prefix)
    {
        $this->controller_prefix = $controller_prefix;
    }

    public function setMessageRequestPrefix($message_request_prefix)
    {
        $this->message_request_prefix = $message_request_prefix;
    }

    public function setControllers(array $controllers)
    {
        $this->controllers = $controllers;
    }

    public function useBSON($use_bson)
    {
        $this->use_bson = $use_bson;
    }

    public function handle(Request $Request, $type = self::MASTER_REQUEST, $catch = true)
    {
        try {
            return $this->handleRaw($Request);
        } catch (\Exception $Exception) {
            if ($catch === true) {
                return $this->sendExceptionResponse($Exception);
            } else {
                throw $Exception;
            }
        }
    }

    public function setPreHandler(HandlerInterface $Handler) {
        $this->preHandler = $Handler;
    }

    private function handleRaw(Request $Request)
    {
        assert(!empty($this->controller_prefix));
        assert(!empty($this->message_request_prefix));
        assert(!empty($this->controllers));

        $this->Request = $Request;

        $MessageManager = new Message\Manager();

        $RequestContext = new RequestContext();
        $RequestContext->fromRequest($this->Request);

        $UrlMatcher = $this->getUrlMatcher($RequestContext);

        $route = $this->getRoute($UrlMatcher, $this->Request);
        if (!$route) {
            return $this->getResponseError(
                ErrorMobileApi_1::UNKOWN_COMMAND,
                'route not found',
                404
            );
        }

        $this->ApiRequest = $this->getApiRequest($route['_route'], $route['protocol_version']);
        if (!$this->ApiRequest) {
            return $this->getResponseError(
                ErrorMobileApi_1::REQUEST_CLASS_NOT_FOUND,
                'request class not found',
                500
            );
        }

        if (!$this->fillRequest()) {
            return $this->getResponseError(
                ErrorMobileApi_1::REQUEST_DECODE_FAIL,
                'request not decoded',
                400
            );
        }

        if ($this->ApiRequest instanceof UploadInterface) {
            $errorResponse = $this->fillUpload($this->ApiRequest);
            if (null !== $errorResponse) {
                return $errorResponse;
            }
        } elseif($this->Request->files->count() > 0) {
            return $this->getResponseErrorUpload(
                ErrorUploadMobileApi_1::NOT_UPLOAD_INTERFACE,
                'request not implement upload interface',
                400
            );
        }

        if (!$MessageManager->isValid($this->ApiRequest, $error)) {
            return $this->getResponseError(
                ErrorMobileApi_1::REQUEST_INVALID,
                'request invalid: ' . $error,
                400
            );
        }

        /* @var ControllerInterface $Controller */
        $Controller = new $route['controller'];

        $Response = null;
        if (null !== $this->preHandler) {
            $Response = $this->preHandler->run($Controller, $this->ApiRequest);
        }

        if (null === $Response) {
            $Response = $Controller->run($this->ApiRequest);
        }

        if (!$this->checkResponseAppropriate($Response)) {
            return $this->getResponseError(
                ErrorMobileApi_1::REQUEST_RESPONSE_NOT_APPROPRIATE,
                'response not appropriate',
                500
            );
        }

        if (!$MessageManager->isValid($Response, $error)) {
            return $this->getResponseError(
                ErrorMobileApi_1::RESPONSE_INVALID,
                'response invalid: ' . $error,
                500
            );
        }

        return $this->getResponse($Response, 200);
    }

    private function getUrlMatcher(RequestContext $RequestContext)
    {
        $RouteCollection = new RouteCollection();

        foreach ($this->controllers as $controller) {
            $Route = new Route(
                '/' . $controller  .'/{protocol_version}',
                array('controller' => '\\' . $this->controller_prefix . '\\' . $controller),
                array('protocol_version' => '\d+')
            );
            $RouteCollection->add($controller, $Route);
        }

        $UrlMatcher = new UrlMatcher($RouteCollection, $RequestContext);
        return $UrlMatcher;
    }

    private function getRoute(UrlMatcher $UrlMatcher)
    {
        try {
            return $UrlMatcher->match($this->Request->getPathInfo());
        } catch (ResourceNotFoundException $Exception) {
            return false;
        }
    }

    private function fillUpload(UploadInterface $Request)
    {
        $files = $this->Request->files;
        if ($files->count() == 0) {
            return $this->getResponseErrorUpload(
                ErrorUploadMobileApi_1::FILE_NOT_FOUND,
                'file in request not found',
                400
            );
        }

        if (!$files->has('file')) {
            return $this->getResponseErrorUpload(
                ErrorUploadMobileApi_1::WRONG_FILED,
                'wrong field',
                400
            );
        }

        /** @var $file UploadedFile */
        $file = $files->get('file');

        if (!$file->isValid()) {
            return $this->getResponseErrorUpload(
                ErrorUploadMobileApi_1::UPLOAD_ERROR,
                $this->codeToMessage($file->getError()),
                400
            );
        }

        $Request->setFile($file);

        return null;
    }

    private function fillRequest()
    {
        $request_array = $this->getRequestArray();
        if (!is_array($request_array)) {
            return false;
        }

        foreach ($request_array as $k => $v) {
            $this->ApiRequest->$k = $v;
        }

        return true;
    }

    private function getRequestArray()
    {
        if ($this->Request->getMethod() === 'GET') {
            if ($this->Request->query->has('json')) {
                if ($this->Request->query->get('json') === '') {
                    return array();
                }

                $result = @json_decode($this->Request->query->get('json'), true);
                if (is_array($result)) {
                    return $result;
                } else {
                    return false;
                }
            }

            $GetParser = new Message\Request\ParameterParser;
            return $GetParser->toArray($this->ApiRequest, $this->Request->query);
        } elseif ($this->Request->getMethod() === 'POST') {
            if ($this->Request->request->has('json')) {
                if ($this->Request->request->get('json') === '') {
                    return array();
                }

                $result = @json_decode($this->Request->request->get('json'), true);
                if (is_array($result)) {
                    return $result;
                } else {
                    return false;
                }
            }

            if ($this->use_bson && $this->Request->getContent()) {
                $this->response_bson = true;
                try {
                    $result = @bson_decode($this->Request->getContent());
                    if (is_array($result)) {
                        return $result;
                    } else {
                        return false;
                    }
                } catch (\MongoException $Exception) {
                    return false;
                }
            }

            $GetParser = new Message\Request\ParameterParser;
            return $GetParser->toArray($this->ApiRequest, $this->Request->request);
        } else {
            return false;
        }
    }

    private function getApiRequest($route, $protocol_version)
    {
        $request_class = '\\' . $this->message_request_prefix . '\\' . $route . '_' . $protocol_version;
        if (class_exists($request_class)) {
            $Request = new $request_class;
            return $Request;
        } else {
            return false;
        }
    }

    private function checkResponseAppropriate(Message\MessageInterface $Response)
    {
        return in_array(get_class($Response), $this->ApiRequest->getAvailableResponses());
    }

    private function getResponse(Message\Response\ResponseInterface $Response, $http_code)
    {
        $response = array(
            'message' => $Response->getName(),
            'body' => (array)$Response
        );

        if ($this->response_bson) {
            $content = bson_encode($response);
        } else {
            $content = json_encode($response);
        }

        $Response = new Response($content, $http_code);
        return $Response;
    }

    private function getResponseError($code, $message, $http_code)
    {
        $Response = new ErrorMobileApi_1;
        $Response->code = $code;
        $Response->message = $message;

        return $this->getResponse($Response, $http_code);
    }

    private function getResponseErrorUpload($code, $message, $http_code)
    {
        $Response = new ErrorUploadMobileApi_1();
        $Response->code = $code;
        $Response->message = $message;

        return $this->getResponse($Response, $http_code);
    }

    private function codeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;

            default:
                $message = "Unknown upload error";
                break;
        }
        return $message;
    }

public function sendExceptionResponse(\Exception $Exception)
    {
        $message = get_class($Exception) . ' ' . $Exception->getMessage();
        return $this->getResponseError(ErrorMobileApi_1::SERVER_ERROR, $message, 500);
    }
}
