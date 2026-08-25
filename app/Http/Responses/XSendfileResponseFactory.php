<?php

namespace App\Http\Responses;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Glide\Responses\ResponseFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

class XSendfileResponseFactory implements ResponseFactoryInterface
{
    /**
     * Create response.
     * @param FilesystemOperator $cache
     * @param $path
     * @return Response
     * @throws FilesystemException
     */
    public function create(FilesystemOperator $cache, $path): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', $cache->mimeType($path));
        $response->headers->set('Content-Length', $cache->fileSize($path));
//        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Redirect', "/i/{$path}"); // Nginx
        $response->headers->set('X-Sendfile', "/i/{$path}"); // Apache
        $response->headers->set('X-LIGHTTPD-send-file', "/i/{$path}"); // Lighttpd
        $response->headers->set('X-LiteSpeed-Location', "/i/{$path}"); // LiteSpeed
//        $response->headers->set('X-Accel-Limit-Rate', '1024');
        $response->headers->set('X-Accel-Buffering', 'no');

        $response->setPublic();
        $response->setMaxAge(31536000);
        $response->setExpires(date_create()->modify('+1 years'));

        return $response;
    }
}
