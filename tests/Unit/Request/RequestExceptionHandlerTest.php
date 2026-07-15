<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Dru1x\ExpoPush\Tests\Unit\Request;

use Dru1x\ExpoPush\PushError\PushErrorCode;
use Dru1x\ExpoPush\PushError\PushErrorCollection;
use Dru1x\ExpoPush\Request\RequestExceptionHandler;
use JsonException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Response;

class RequestExceptionHandlerTest extends TestCase
{
    protected PushErrorCollection $errors;
    protected RequestExceptionHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->errors  = new PushErrorCollection();
        $this->handler = new RequestExceptionHandler(batchSize: 100, errors: $this->errors);
    }

    #[Test]
    public function invoke_with_fatal_request_exception_adds_push_error(): void
    {
        $fatalRequestException = $this->createMock(FatalRequestException::class);

        $handler = $this->handler;
        $handler($fatalRequestException, 1);

        $this->assertCount(1, $this->errors);

        $error = $this->errors->get(0);
        $this->assertEquals(PushErrorCode::Failed, $error->code);
        $this->assertEquals(100, $error->startIndex);
        $this->assertEquals(199, $error->endIndex);
    }

    #[Test]
    public function invoke_with_request_exception_adds_push_error(): void
    {
        $exceptionResponse = $this->createMock(Response::class);
        $exceptionResponse
            ->method('json')
            ->with('errors')
            ->willReturn([
                [
                    'code'    => 'PUSH_TOO_MANY_NOTIFICATIONS',
                    'message' => 'You are trying to send more than 100 push notifications in one request',
                ],
            ]);

        $requestException = $this->createMock(RequestException::class);
        $requestException
            ->method('getResponse')
            ->willReturn($exceptionResponse);

        $handler = $this->handler;
        $handler($requestException, 3);

        $this->assertCount(1, $this->errors);

        $error = $this->errors->get(0);
        $this->assertEquals(PushErrorCode::PushTooManyNotifications, $error->code);
        $this->assertEquals('You are trying to send more than 100 push notifications in one request', $error->message);
        $this->assertEquals(300, $error->startIndex);
        $this->assertEquals(399, $error->endIndex);
    }

    #[Test]
    public function invoke_with_request_exception_and_multiple_response_errors_adds_multiple_push_errors(): void
    {
        $exceptionResponse = $this->createMock(Response::class);
        $exceptionResponse
            ->method('json')
            ->with('errors')
            ->willReturn([
                [
                    'code'    => 'PUSH_TOO_MANY_NOTIFICATIONS',
                    'message' => 'You are trying to send more than 100 push notifications in one request',
                ],
                [
                    'code'    => 'PUSH_TOO_MANY_RECEIPTS',
                    'message' => 'You are trying to fetch more than 1000 receipts in one request',
                    'details' => ['reason' => 'too many ids'],
                ],
                [
                    'code'    => 'SOME_UNKNOWN_CODE',
                    'message' => 'Something unexpected happened',
                ],
            ]);

        $requestException = $this->createMock(RequestException::class);
        $requestException
            ->method('getResponse')
            ->willReturn($exceptionResponse);

        $handler = $this->handler;
        $handler($requestException, 5);

        $this->assertCount(3, $this->errors);

        $firstError = $this->errors->get(0);
        $this->assertEquals(PushErrorCode::PushTooManyNotifications, $firstError->code);
        $this->assertEquals('You are trying to send more than 100 push notifications in one request', $firstError->message);
        $this->assertNull($firstError->details);
        $this->assertEquals(500, $firstError->startIndex);
        $this->assertEquals(599, $firstError->endIndex);

        $secondError = $this->errors->get(1);
        $this->assertEquals(PushErrorCode::PushTooManyReceipts, $secondError->code);
        $this->assertEquals('You are trying to fetch more than 1000 receipts in one request', $secondError->message);
        $this->assertEquals(['reason' => 'too many ids'], $secondError->details);
        $this->assertEquals(500, $secondError->startIndex);
        $this->assertEquals(599, $secondError->endIndex);

        $thirdError = $this->errors->get(2);
        $this->assertEquals(PushErrorCode::Unknown, $thirdError->code);
        $this->assertEquals('Something unexpected happened', $thirdError->message);
        $this->assertNull($thirdError->details);
        $this->assertEquals(500, $thirdError->startIndex);
        $this->assertEquals(599, $thirdError->endIndex);
    }

    #[Test]
    public function invoke_with_request_exception_and_non_json_response_adds_generic_push_error(): void
    {
        $exceptionResponse = $this->createMock(Response::class);
        $exceptionResponse
            ->method('json')
            ->with('errors')
            ->willThrowException(new JsonException('Syntax error'));

        $requestException = new RequestException($exceptionResponse, 'There was an error with the request: 502');

        $handler = $this->handler;
        $handler($requestException, 2);

        $this->assertCount(1, $this->errors);

        $error = $this->errors->get(0);
        $this->assertEquals(PushErrorCode::Failed, $error->code);
        $this->assertEquals('There was an error with the request: 502', $error->message);
        $this->assertEquals(200, $error->startIndex);
        $this->assertEquals(299, $error->endIndex);
    }

    #[Test]
    public function invoke_with_request_exception_and_missing_errors_key_adds_generic_push_error(): void
    {
        $exceptionResponse = $this->createMock(Response::class);
        $exceptionResponse
            ->method('json')
            ->with('errors')
            ->willReturn(null);

        $requestException = new RequestException($exceptionResponse, 'There was an error with the request: 500');

        $handler = $this->handler;
        $handler($requestException, 4);

        $this->assertCount(1, $this->errors);

        $error = $this->errors->get(0);
        $this->assertEquals(PushErrorCode::Failed, $error->code);
        $this->assertEquals('There was an error with the request: 500', $error->message);
        $this->assertEquals(400, $error->startIndex);
        $this->assertEquals(499, $error->endIndex);
    }
}