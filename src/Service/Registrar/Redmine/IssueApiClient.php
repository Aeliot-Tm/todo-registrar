<?php

declare(strict_types=1);

/*
 * This file is part of the TODO Registrar project.
 *
 * (c) Anatoliy Melnikov <5785276@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Aeliot\TodoRegistrar\Service\Registrar\Redmine;

use Aeliot\TodoRegistrar\Exception\Api\UnexpectedResponseException;
use Psr\Http\Client\ClientExceptionInterface;
use Redmine\Client\Client;
use Redmine\Exception\ClientException;

/**
 * @internal
 */
final readonly class IssueApiClient
{
    public function __construct(
        private Client $client,
    ) {
    }

    public function create(Issue $issue): \SimpleXMLElement
    {
        // Redmine API library already wraps data in ['issue' => ...], so pass data directly
        $data = $issue->getData();

        try {
            $response = $this->client->getApi('issue')->create($data);
        } catch (ClientExceptionInterface $e) {
            // Handle HTTP errors (403, 404, etc.)
            $exceptionMessage = \sprintf(
                'Redmine API error: %s. Request data: %s',
                $e->getMessage(),
                json_encode($data),
            );
            throw new UnexpectedResponseException($exceptionMessage, 0, $e);
        } catch (ClientException $exception) {
            throw new UnexpectedResponseException('Cannot create issue in Redmine', 0, $exception);
        }

        if ($response instanceof \SimpleXMLElement) {
            return $response;
        }

        // Handle error response (string or array)
        if (\is_string($response)) {
            $errorMessage = $response ?: 'Empty response from Redmine API';
            $exceptionMessage = \sprintf(
                'Redmine API error: %s. Request data: %s',
                $errorMessage,
                json_encode($data),
            );
            throw new UnexpectedResponseException($exceptionMessage);
        }

        $exceptionMessage = \sprintf(
            'Redmine API returned unexpected response type: expected SimpleXMLElement, got %s. Request data: %s',
            get_debug_type($response),
            json_encode($data),
        );
        throw new UnexpectedResponseException($exceptionMessage);
    }
}
