<?php
namespace App\Http\Middleware;
use Closure;
class Cors
{
  public function handle($request, Closure $next)
  {
    $response = $next($request);

    $response->headers->set('Access-Control-Allow-Origin' , '*');
    $response->headers->set('Access-Control-Allow-Credentials',' true');
    $response->headers->set('Access-Control-Allow-Methods', '*');
    $response->headers->set('Access-Control-Max-Age', '1000');
    $response->headers->set('Access-Control-Allow-Headers', 'Origin,Content-Type, Accept, Authorization, X-Requested-With, Application,X-Token-Auth,X-Auth-Token');

    return $response;
    //POST, GET, OPTIONS, PUT, DELETE
    }

}
