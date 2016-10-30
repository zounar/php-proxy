<?php
/*
 * Author:
 *    Robin Zon <https://github.com/ZonRobin>
 *
 * Credits to:
 *    https://github.com/cowboy/php-simple-proxy/
 *    https://gist.github.com/iovar
 *    craig(at)raigfrancis.co.uk - for http_response_code
 *
 * Usage:
 *    To call this script two headers must be sent
 *        HTTP_PROXY_AUTH           Access key for the proxy (should be changed)
 *        HTTP_PROXY_TARGET_URL     URL to be called by this script
 *
 * Debug:
 *    To debug, send HTTP_PROXY_DEBUG header with any value
 *
 * Compatibility:
 *    PHP 5
 *    libcurl
 *    PHP safe_mode disabled
 */
//----------------------------------------------------------------------------------

// Your private auth key
define('AUTH_KEY', 'Bj5pnZEX6DkcG6Nz6AjDUT1bvcGRVhRaXDuKDX9CjsEs2');

// Name of the proxy auth key header
define('HTTP_PROXY_AUTH', 'HTTP_PROXY_AUTH');

// Name of the target url header
define('HTTP_PROXY_TARGET_URL', 'HTTP_PROXY_TARGET_URL');

// Name of remote debug header
define('HTTP_PROXY_DEBUG', 'HTTP_PROXY_DEBUG');

// Uncomment this to simulate target header
//$_SERVER[HTTP_PROXY_TARGET_URL] = 'https://github.com/';

// Uncomment this to simulate auth key (or to disable the need of passing the key with each request)
//$_SERVER[HTTP_PROXY_AUTH] = AUTH_KEY;

// Uncomment this to enable debug mode
//$_SERVER[HTTP_PROXY_DEBUG] = '1';

// If true, PHP safe mode compatibility will not be checked (you may not need it if no POST files are sent over proxy)
define('IGNORE_SAFE_MODE', false);

// Line break for debug purposes
define('HR', PHP_EOL . PHP_EOL . '----------------------------------------------' . PHP_EOL . PHP_EOL);


//----------------------------------------------------------------------------------
/**
 * @param mixed $variable
 * @param mixed $default
 * @return mixed
 */
function ri(&$variable, $default = NULL)
{
    if (isset($variable))
    {
        return $variable;
    }
    else
    {
        return $default;
    }
}


/**
 * @param string $message
 */
function exitWithError($message = 'unknown')
{
    echo 'PROXY ERROR: ' . $message;
    http_response_code(500);
    exit(500);
}


/**
 * @return array
 */
function getSkippedHeaders()
{
    return array(HTTP_PROXY_TARGET_URL, HTTP_PROXY_AUTH, 'HTTP_HOST', 'HTTP_ACCEPT_ENCODING');
}


if (!function_exists('errorHandler'))
{
    /**
     * @param int $code
     * @param string $message
     * @param string $file
     * @param string $line
     */
    function errorHandler($code, $message, $file, $line)
    {
        exitWithError($message . ' in ' . $file . ' at line ' . $line);
    }
}


if (!function_exists('exceptionHandler'))
{
    /**
     * @param Exception $ex
     */
    function exceptionHandler(Exception $ex)
    {
        exitWithError($ex->getMessage() . ' in '. $ex->getFile() . ' at line ' . $ex->getLine());
    }
}


if (!function_exists('http_response_code'))
{
    /**
     * @param int $code
     */
    function http_response_code($code)
    {
        switch ($code)
        {
            case 100: $text = 'Continue'; break;
            case 101: $text = 'Switching Protocols'; break;
            case 200: $text = 'OK'; break;
            case 201: $text = 'Created'; break;
            case 202: $text = 'Accepted'; break;
            case 203: $text = 'Non-Authoritative Information'; break;
            case 204: $text = 'No Content'; break;
            case 205: $text = 'Reset Content'; break;
            case 206: $text = 'Partial Content'; break;
            case 300: $text = 'Multiple Choices'; break;
            case 301: $text = 'Moved Permanently'; break;
            case 302: $text = 'Moved Temporarily'; break;
            case 303: $text = 'See Other'; break;
            case 304: $text = 'Not Modified'; break;
            case 305: $text = 'Use Proxy'; break;
            case 400: $text = 'Bad Request'; break;
            case 401: $text = 'Unauthorized'; break;
            case 402: $text = 'Payment Required'; break;
            case 403: $text = 'Forbidden'; break;
            case 404: $text = 'Not Found'; break;
            case 405: $text = 'Method Not Allowed'; break;
            case 406: $text = 'Not Acceptable'; break;
            case 407: $text = 'Proxy Authentication Required'; break;
            case 408: $text = 'Request Time-out'; break;
            case 409: $text = 'Conflict'; break;
            case 410: $text = 'Gone'; break;
            case 411: $text = 'Length Required'; break;
            case 412: $text = 'Precondition Failed'; break;
            case 413: $text = 'Request Entity Too Large'; break;
            case 414: $text = 'Request-URI Too Large'; break;
            case 415: $text = 'Unsupported Media Type'; break;
            case 500: $text = 'Internal Server Error'; break;
            case 501: $text = 'Not Implemented'; break;
            case 502: $text = 'Bad Gateway'; break;
            case 503: $text = 'Service Unavailable'; break;
            case 504: $text = 'Gateway Time-out'; break;
            case 505: $text = 'HTTP Version not supported'; break;
            default: $text = '';
        }

        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        header($protocol . ' ' . $code . ' ' . $text);
    }
}


//----------------------------------------------------------------------------------

// Compatibility checks

if (!IGNORE_SAFE_MODE && function_exists('ini_get') && ini_get('safe_mode'))
{
    exitWithError('Safe mode is enabled, this may cause problems with uploading files');
}

if (!function_exists('curl_init'))
{
    exitWithError('libcurl is not installed on this server');
}

if (class_exists('CURLFile'))
{
    define('CURLFILE', true);
}
else
{
    define('CURLFILE', false);
}

//----------------------------------------------------------------------------------

set_error_handler('errorHandler', E_ALL);
set_exception_handler('exceptionHandler');

//----------------------------------------------------------------------------------

// Check for auth token
if (ri($_SERVER[HTTP_PROXY_AUTH]) !== AUTH_KEY)
{
    exitWithError(HTTP_PROXY_AUTH . ' header is invalid');
}

// Check for debug token
if (!empty($_SERVER[HTTP_PROXY_DEBUG]))
{
    $debug = true;
}
else
{
    $debug = false;
}

// Get target URL
$targetURL = ri($_SERVER[HTTP_PROXY_TARGET_URL]);
if (empty($targetURL))
{
    exitWithError(HTTP_PROXY_TARGET_URL .' header is empty');
}
if (filter_var($targetURL, FILTER_VALIDATE_URL) === FALSE)
{
    exitWithError(HTTP_PROXY_TARGET_URL .' "'.$targetURL.'" is invalid');
}

//--------------------------------

// Add GET params to target URL
if (!empty($_SERVER['QUERY_STRING']))
{
    $targetURLParts = parse_url($targetURL);
    if (!empty($targetURLParts['query']))
    {
        $targetURL = $targetURL . '&' . $_SERVER['QUERY_STRING'];
    }
    else
    {
        $targetURL = trim($targetURL, '\?');
        $targetURL = $targetURL . '?' . $_SERVER['QUERY_STRING'];
    }
}

//-------------------------------

// Create CURL request
$request = curl_init($targetURL);

//-------------------------------

// Set input data
$requestMethod = strtoupper(ri($_SERVER['REQUEST_METHOD']));
if($requestMethod === "PUT" || $requestMethod === "PATCH")
{
    curl_setopt($request, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
}
elseif($requestMethod === "POST")
{
    $data = array();

    if (!empty($_FILES))
    {
        if (!CURLFILE)
        {
            curl_setopt($request, CURLOPT_SAFE_UPLOAD, false);
        }

        foreach ($_FILES AS $fileName => $file)
        {
            $filePath = realpath($file['tmp_name']);

            if (CURLFILE)
            {
                $data[$fileName] = new CURLFile($filePath);
            }
            else
            {
                $data[$fileName] = '@'.$filePath;
            }
        }
    }

    curl_setopt($request, CURLOPT_POSTFIELDS, $data + $_POST);
}

//--------------------------------

// Parse request headers
$httpHeaders = array();
$httpHeadersAll = array();
foreach( $_SERVER AS $key => $value )
{
    if (strpos($key, 'HTTP_') === 0)
    {
        $header = str_replace('_', '-', ucwords(strtolower(str_replace('HTTP_', '', $key)), '_')) . ': '. $value;

        if(!in_array($key, getSkippedHeaders()))
        {
            $httpHeaders[] = $header;
        }

        $httpHeadersAll[] = $header;
    }
}

curl_setopt($request, CURLOPT_FOLLOWLOCATION, true );
curl_setopt($request, CURLOPT_HEADER, true );
curl_setopt($request, CURLOPT_RETURNTRANSFER, true );
curl_setopt($request, CURLINFO_HEADER_OUT, true);
curl_setopt($request, CURLOPT_HTTPHEADER, $httpHeaders);

//----------------------------------

// Get response
$response = curl_exec($request);

$headerSize = curl_getinfo($request, CURLINFO_HEADER_SIZE);
$responseHeader = substr($response, 0, $headerSize);
$responseBody = substr($response, $headerSize);
$responseInfo = curl_getinfo($request);
$responseCode = ri($responseInfo['http_code'], 500);
$requestHeaders = preg_split( '/[\r\n]+/', ri($responseInfo['request_header'], ''));
if ($responseCode == 0)
{
    $responseCode = 404;
}


// Get real target URL after all redirects
$finalRequestURL = curl_getinfo($request, CURLINFO_EFFECTIVE_URL);
if (!empty($finalRequestURL))
{
    $finalRequestURLParts = parse_url($finalRequestURL);
    $finalURL = ri($finalRequestURLParts['scheme'], 'http') . '//' . ri($finalRequestURLParts['host']) . ri($finalRequestURLParts['path'], '');
}

curl_close($request);

//----------------------------------

// Split header text into an array.
$responseHeaders = preg_split('/[\r\n]+/', $responseHeader);
// Pass headers to output
foreach ($responseHeaders AS $header)
{
    if (preg_match('/^(?:Content-Type|Content-Language|Set-Cookie|X)/i', $header))
    {
        // Replace cookie domain and path
        if (strpos($header, 'Set-Cookie') !== false)
        {
            $header = preg_replace('/((?>domain)\s*=\s*)[^;\s]+/', '\1.' . $_SERVER['HTTP_HOST'], $header);
            $header = preg_replace('/\s*;?\s*path\s*=\s*[^;\s]+/', '', $header);
			header($header, false);
        }
		else
		{
			header($header);
		}
    }
}

//----------------------------------

if ($debug)
{
    echo 'Headers sent to proxy' . PHP_EOL . PHP_EOL;
    echo implode($httpHeadersAll, PHP_EOL);
    echo HR;

    echo '$_GET sent to proxy' . PHP_EOL . PHP_EOL;
    print_r($_GET);
    echo HR;

    echo '$_POST sent to proxy' . PHP_EOL . PHP_EOL;
    print_r($_POST);
    echo HR;

    echo 'Headers sent to target' . PHP_EOL . PHP_EOL;
    echo implode($requestHeaders, PHP_EOL);
    echo HR;

    echo 'Headers received from target' . PHP_EOL . PHP_EOL;
    echo implode($responseHeaders, PHP_EOL);
    echo HR;

    echo 'Headers sent back from proxy' . PHP_EOL . PHP_EOL;
    echo implode(headers_list(), PHP_EOL);
    echo HR;

    echo 'Body received from target' . PHP_EOL . PHP_EOL;
    echo $responseBody;
}
else
{
    http_response_code($responseCode);
    exit($responseBody);
}