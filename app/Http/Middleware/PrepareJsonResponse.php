<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PrepareJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var Response $response */
        $response = $next($request);
        $content = $response->getOriginalContent();
        if (!is_null($content))
        {
            if ($response instanceof JsonResponse)
            {
                if ($content instanceof JsonResource)
                {
                    $content->resource = $this->modelAndEnumObjectsToArray($content->resource);
                    if (is_array($content->resource))
                    {
                        $content->resource = $this->snakeCaseKeysToCamelCase($content->resource);
                    }
                    else
                    {
                        $content->resource = $this->snakeCaseKeysToCamelCase([$content->resource])[0];
                    }
                }
                else
                {
                    $content = $this->modelAndEnumObjectsToArray($content);
                    if (is_array($content))
                    {
                        $content = $this->snakeCaseKeysToCamelCase($content);
                    }
                    else
                    {
                        $content = $this->snakeCaseKeysToCamelCase([$content])[0];
                    }
                }

                $response->setData($content);
            }
        }
        return $response;
    }

    public function snakeCaseKeysToCamelCase(array $inputArray, $capitalizeFirstCharacter = false)
    {
        foreach ($inputArray as $key => $value)
        {
            $newKeyName = $key;
            if (is_string($key))
            {
                $newKeyName = str_replace('_', '', ucwords($key, '_'));
                if (!$capitalizeFirstCharacter) {
                    $newKeyName = lcfirst($newKeyName);
                }

                if(
                    // does this key already exist in the array?
                    !isset($inputArray[$newKeyName])
                    // Is the new key different from the old key?
                    && $key !== $newKeyName
                )
                {
                    unset($inputArray[$key]);
                    $inputArray[$newKeyName] = $value;
                }
            }
            if ($value instanceof JsonResource)
            {
                $value->resource = $this->modelAndEnumObjectsToArray($value->resource);
                if (is_array($value->resource))
                {
                    $value->resource = $this->snakeCaseKeysToCamelCase($value->resource);
                }
                else
                {
                    $value->resource = $this->snakeCaseKeysToCamelCase([$value->resource])[0];
                }
            }
            if (is_array($value))
            {
                $inputArray[$newKeyName] = $this->snakeCaseKeysToCamelCase($value, $capitalizeFirstCharacter);
            }
        }
        return $inputArray;
    }

    private function modelAndEnumObjectsToArray($objectOrArray)
    {
        if ($objectOrArray instanceof Collection || $objectOrArray instanceof Model)
        {
            $objectOrArray = $objectOrArray->toArray();
        }
        if ($objectOrArray instanceof Carbon)
        {
            $objectOrArray = $objectOrArray->timestamp;
        }
        if (!is_array($objectOrArray))
        {
            return $objectOrArray;
        }
        foreach ($objectOrArray as &$item)
        {
            $item = $this->modelAndEnumObjectsToArray($item);
        }
        return $objectOrArray;
    }
}
