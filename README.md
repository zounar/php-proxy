# Simple PHP Proxy

This proxy script allows you to forward all HTTP/HTTPS requests to another server. Works for all common request types including GET , POST requests with files, PATCH and PUT requests. It has minimal requirements (PHP 5.4, libcurl) which are available even on the smallest free hostings and has it's own simple authorization and cookie support.

## How to use
* Simply upload the script to any PHP server
* Make a cURL request targetting this script
* Add **Proxy-Auth** header with auth key [found here](https://github.com/ZonRobin/php-proxy/blob/master/proxy.php#L27)
* Add **Proxy-Target-URL** header with URL to be requested by the proxy
* (Optional) Add **Proxy-Debug** header for debug mode

In order to protect using proxy by unauthorized users, consider changing `Proxy-Auth` token in [proxy source file](https://github.com/ZonRobin/php-proxy/blob/master/proxy.php#L27) and in all your requests.

## Example
Following example shows how to execute GET request to https://www.github.com. Proxy script is at http://www.foo.bar/proxy.php. All proxy settings are kept default, the response is automatically echoed.

```php
$request = curl_init('http://www.foo.bar/proxy.php');

curl_setopt($request, CURLOPT_HTTPHEADER, array(
    'Proxy-Auth: Bj5pnZEX6DkcG6Nz6AjDUT1bvcGRVhRaXDuKDX9CjsEs2',
    'Proxy-Target-URL: https://www.github.com'
));

curl_exec($request);
```

## Debugging
In order to show some debug info from the proxy, add `Proxy-Debug: 1` header into the request. This will show debug info in plain-text containing request headers, response headers and response body.

```php
$request = curl_init('http://www.foo.bar/proxy.php');

curl_setopt($request, CURLOPT_HTTPHEADER, array(
    'Proxy-Auth: Bj5pnZEX6DkcG6Nz6AjDUT1bvcGRVhRaXDuKDX9CjsEs2',
    'Proxy-Target-URL: https://www.github.com',
    'Proxy-Debug: 1'
));

curl_exec($request);
```

## Specifying User-Agent
Some sites may return different content for different user agents. In such case add `User-Agent` header to cURL request, it will be automatically passed to the request for target site. In this case it's Firefox 70 for Ubuntu.

```php
$request = curl_init('http://www.foo.bar/proxy.php');

curl_setopt($request, CURLOPT_HTTPHEADER, array(
    'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:70.0) Gecko/20100101 Firefox/70.0',
    'Proxy-Auth: Bj5pnZEX6DkcG6Nz6AjDUT1bvcGRVhRaXDuKDX9CjsEs2',
    'Proxy-Target-URL: https://www.github.com'
));

curl_exec($request);
```

## Error 301 Moved permanently
It might occur that there's a redirection when calling the proxy (not the target site), eg. during `http -> https` redirection. You can either modify/fix the proxy URL (which is recommended), or add `CURLOPT_FOLLOWLOCATION` option before `curl_exec`.

```php
$request = curl_init('http://www.foo.bar/proxy.php');

curl_setopt($request, CURLOPT_FOLLOWLOCATION, true );
curl_setopt($request, CURLOPT_HTTPHEADER, array(
    'Proxy-Auth: Bj5pnZEX6DkcG6Nz6AjDUT1bvcGRVhRaXDuKDX9CjsEs2',
    'Proxy-Target-URL: https://www.github.com'
));

curl_exec($request);
```

## Save response into variable
The default cURL behavior is to echo the response of `curl_exec`. In order to save response into variable, all you have to do is to add `CURLOPT_RETURNTRANSFER` cURL option.

```php
$request = curl_init('http://www.foo.bar/proxy.php');

curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
curl_setopt($request, CURLOPT_HTTPHEADER, array(
    'Proxy-Auth: Bj5pnZEX6DkcG6Nz6AjDUT1bvcGRVhRaXDuKDX9CjsEs2',
    'Proxy-Target-URL: https://www.github.com'
));

$response = curl_exec($request);
```