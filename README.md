# Simple PHP Proxy

This proxy script allows you to forward all HTTP/HTTPS requests to another server. Works for all common request types 
including GET, POST requests with files, PATCH and PUT requests. It has minimal set of requirements 
(PHP >=5.6, libcurl, gzip) which are available even on the smallest free hostings and has its own simple authorization 
and cookie support.

## How to use
* Copy the [Proxy.php](Proxy.php) script to publicly-accessible folder of a PHP web server (the script is standalone and has no PHP dependencies)
* Make a cURL request targeting this script
* Add **Proxy-Auth** header with auth key [found here](https://github.com/zounar/php-proxy/blob/master/Proxy.php#L40)
* Add **Proxy-Target-URL** header with URL to be requested by the proxy
* (Optional) Add **Proxy-Debug** header for debug mode

In order to protect using proxy by unauthorized users, consider changing `Proxy-Auth` token in [proxy source file](https://github.com/zounar/php-proxy/blob/master/Proxy.php#L40) and in all your requests.

## How to use (via composer)
This might be useful when you want to redirect requests coming into your app. 

* Run `composer require zounar/php-proxy`
* Add `Proxy::run();` line to where you want to execute it (usually into a controller action)
  * In this example, the script is in `AppController` - `actionProxy`:
    ```
    use Zounar\PHPProxy\Proxy;
    
    class AppController extends Controller {

        public function actionProxy() {
            Proxy::$AUTH_KEY = '<your-new-key>';
            // Do your custom logic before running proxy
            $responseCode = Proxy::run();
            // Do your custom logic after running proxy
            // You can utilize HTTP response code returned from the run() method
        }
    }
    ```
* Make a cURL request to your web
  * In the example, it would be `http://your-web.com/app/proxy`
* Add **Proxy-Auth** header with auth key [found here](https://github.com/zounar/php-proxy/blob/master/Proxy.php#L40)
* Add **Proxy-Target-URL** header with URL to be requested by the proxy
* (Optional) Add **Proxy-Debug** header for debug mode

In order to protect using proxy by unauthorized users, consider changing `Proxy-Auth` token by calling
`Proxy::$AUTH_KEY = '<your-new-key>';` before `Proxy::run()`. Then change the token in all your requests.

## Usage example
Following example shows how to execute GET request to https://www.github.com. Proxy script is at http://www.foo.bar/Proxy.php. All proxy settings are kept default, the response is automatically echoed.

```php
$request = curl_init('http://www.foo.bar/Proxy.php');

curl_setopt($request, CURLOPT_HTTPHEADER, array(
    'Proxy-Auth: Bj5pnZEX6DkcG6Nz6AjDUT1bvcGRVhRaXDuKDX9CjsEs2',
    'Proxy-Target-URL: https://www.github.com'
));

curl_exec($request);
```

## Debugging
In order to show some debug info from the proxy, add `Proxy-Debug: 1` header into the request. This will show debug info in plain-text containing request headers, response headers and response body.

```php
$request = curl_init('http://www.foo.bar/Proxy.php');

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
$request = curl_init('http://www.foo.bar/Proxy.php');

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
$request = curl_init('http://www.foo.bar/Proxy.php');

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
$request = curl_init('http://www.foo.bar/Proxy.php');

curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
curl_setopt($request, CURLOPT_HTTPHEADER, array(
    'Proxy-Auth: Bj5pnZEX6DkcG6Nz6AjDUT1bvcGRVhRaXDuKDX9CjsEs2',
    'Proxy-Target-URL: https://www.github.com'
));

$response = curl_exec($request);
```
