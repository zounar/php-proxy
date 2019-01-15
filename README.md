# Simple PHP Proxy

This proxy script allows you to forward all HTTP/HTTPS requests to another server. Works both for simple GET requests and POST requests with files. It has minimal requirements (PHP 5.0, libcurl) which are available even on the smallest free hostings and has it's own simple authorization.

## How to use
* Simply put the script somewhere
* Make a CURL request for this script
* Add **Proxy-Auth** header with auth key [found here](https://github.com/ZonRobin/php-proxy/blob/master/proxy.php#L27)
* Add **Proxy-Target-URL** header with URL to be requested by the proxy
* (Optional) Add **Proxy-Debug** header for debug mode

## Example
Proxy script is at http://www.foo.bar/proxy.php. I want to execute a GET request to https://www.github.com. The auth token is default one, using debug mode.

```php
$request = curl_init('http://www.foo.bar/proxy.php');

curl_setopt($request, CURLOPT_HTTPHEADER, array(
    'Proxy-Auth: Bj5pnZEX6DkcG6Nz6AjDUT1bvcGRVhRaXDuKDX9CjsEs2',
    'Proxy-Target-URL: https://www.github.com',
    'Proxy-Debug: 1' 
));

$response = curl_exec($request);
```
