<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class RequestValidator
{
    public function validateJsonRequest(Request $request, array $requiredFields): array|JsonResponse
    {
        if ($request->getMethod() === 'GET') {
            foreach ($requiredFields as $field => $type)
                $body[$field] = $request->query->get($field);
        } else {
            $jsonBody = $request->getContent();
            $body = json_decode($jsonBody, true);
            // return new JsonResponse(['error' => $body], 400);

            if (json_last_error() !== JSON_ERROR_NONE)
                return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }
        foreach ($requiredFields as $field => $type) {
            if (!isset($body[$field])) {
                return new JsonResponse(['error' => 'Missing required field: ' . $field], 400);
            }

            if (!$this->isValidType($body[$field], $type)) {
                return new JsonResponse(['error' => 'Invalid field type for ' . $field], 400);
            }
        }

        return $body;
    }

    private function isValidType($value, string $type): bool
    {
        switch ($type) {
            case 'string':
                return is_string($value);
            case 'stringNotEmpty':
                return is_string($value) && !empty($value);
            case 'numeric':
                return is_numeric($value);
            case 'int':
                return is_int($value);
            case 'float':
                return is_float($value);
            default:
                return false;
        }
    }
}
