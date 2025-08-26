<?php

namespace App\Traits;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JsonSerializable;

trait HasJsonResponse
{
    use Translatable;

    /**
     * Choose to wrap data or not.
     */
    private bool $useResponseWrapper = true;

    /**
     * Return a generic HTTP response.
     *
     * @param string $message
     * @param int $status
     * @param mixed $data
     * @param array $headers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function jsonResponse(int $status, string $message, $data = null, array $headers = []): JsonResponse
    {
        $isSuccessful = $status >= 100 && $status < 300;

        $normalizeData = $this->normalizeData($data);

        $messageData = $this->retrieveResponseMessage(
            $message ?: 'Request was received',
            $normalizeData,
            $isSuccessful
        );

        $responseData = ['success' => $isSuccessful, 'message' => $messageData['message']];

        if (!$isSuccessful) {
            $responseData += $this->pullErrorCodeFromData($normalizeData, $message, $messageData['key']);
        }

        // Check if $normalizeData is an array with a single element
        if (is_array($normalizeData) && count($normalizeData) === 1) {
            $responseData['data'] = reset($normalizeData); // Extract the first (and only) element
        } else {
            $responseData['data'] = $normalizeData;
        }

        return new JsonResponse($responseData, $status, $headers);
    }

    /**
     * Wrap JsonResponses to conform to the API response structure.
     *
     * Particularly handy for Laravel API Resources/Collections.
     *
     * Usage
     *
     * $this->wrapJsonResponse(new UserCollection(User::paginate())->response())
     * $this->wrapJsonResponse(new UserResource(User::find())->response())
     *
     * @param \Illuminate\Http\JsonResponse $response
     * @param string $message
     * @param bool $wrap
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function wrapJsonResponse(JsonResponse $response, string $message = null, bool $wrap = false): JsonResponse
    {
        $data = $response->getData(true);
        $responseData = is_array($data) ? $data : ['message_data' => $data];
        $message = (string) ($message ?: Arr::pull($responseData, 'message', ''));

        $this->useResponseWrapper = $wrap;

        return $this->jsonResponse($response->status(), $message, $responseData)
            ->withHeaders($response->headers);
    }

    /**
     * Translates the message.
     *
     * @param mixed $data
     */
    protected function retrieveResponseMessage(string $message, &$data, bool $successful): array
    {
        if ($successful) {
            return $this->translateMessageToArray($message);
        }

        $attributes = is_array($data) ? Arr::pull($data, 'error_attributes', []) : [];
        $prefix = $this->hasTranslationKey($message) ? null : 'errors';

        return $this->translateMessageToArray($message, $attributes, $prefix);
    }

    /**
     * Pull out error code from the response data generated from the response message.
     *
     * @param mixed $data
     */
    protected function pullErrorCodeFromData(&$data, string $originalMessage, ?string $translatedKey = null): array
    {
        if (Arr::has(Arr::wrap($data), 'error_code')) {
            return ['error_code' => (string) Arr::pull($data, 'error_code')];
        }

        if (!is_null($translatedKey) && Str::contains($originalMessage, 'error_code.')) {
            return ['error_code' => $translatedKey];
        }

        return [];
    }

    /**
     * Try to normalize the data to an array form, if not return data as-is.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    protected function normalizeData($data)
    {
        if (is_array($data) || is_null($data)) {
            return $data;
        }

        $map = [
            Jsonable::class => fn($data) => json_decode($data->toJson(0), true),
            JsonSerializable::class => fn($data) => $data->jsonSerialize(),
            Arrayable::class => fn($data) => $data->toArray(),
        ];

        $normalizedData = with($data, Arr::first($map, fn($cb, $key) => is_a($data, $key)));

        // Check if $normalizedData is an array with only one element
        if (is_array($normalizedData) && count($normalizedData) === 1) {
            return reset($normalizedData); // Return the first (and only) element
        }

        return $normalizedData;
    }

    /**
     * Force json response to ignore response wrapper.
     */
    protected function withoutWrap(): self
    {
        $this->useResponseWrapper = false;

        return $this;
    }
}
