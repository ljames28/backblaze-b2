<?php

namespace BackblazeB2;

use BackblazeB2\Exceptions\B2Exception;
use BackblazeB2\Exceptions\BadJsonException;
use BackblazeB2\Exceptions\BadValueException;
use BackblazeB2\Exceptions\BucketAlreadyExistsException;
use BackblazeB2\Exceptions\BucketNotEmptyException;
use BackblazeB2\Exceptions\FileNotPresentException;
use BackblazeB2\Exceptions\NotFoundException;
use BackblazeB2\Exceptions\UnauthorizedAccessException;
use BackblazeB2\Exceptions\ServiceUnavailableException;
use GuzzleHttp\Psr7\Response;

class ErrorHandler
{
    protected static $mappings = [
        'bad_json'                       => BadJsonException::class,
        'bad_value'                      => BadValueException::class,
        'duplicate_bucket_name'          => BucketAlreadyExistsException::class,
        'not_found'                      => NotFoundException::class,
        'file_not_present'               => FileNotPresentException::class,
        'cannot_delete_non_empty_bucket' => BucketNotEmptyException::class,
        'unauthorized'                   => UnauthorizedAccessException::class,
        'service_unavailable'            => ServiceUnavailableException::class,
    ];

    /**
     * @param Response $response
     *
     * @throws B2Exception
     */
    public static function handleErrorResponse(Response $response, $request_options)
    {
        $responseJson = json_decode($response->getBody(), true);

        if (isset(self::$mappings[$responseJson['code']])) {
            $exceptionClass = self::$mappings[$responseJson['code']];
        } else {
            // We don't have an exception mapped to this response error, throw generic exception
            $exceptionClass = B2Exception::class;
        }

        throw new $exceptionClass('Received error from B2: '.$responseJson['message'] . ' with code: ' . $responseJson['code'] . 
         $request_options['headers']['X-Bz-File-Name' ]);
    }
}
