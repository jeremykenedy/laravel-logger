<?php

namespace jeremykenedy\LaravelLogger\App\Http\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use jeremykenedy\LaravelLogger\Support\ActivityData;

trait ActivityLogger
{
    public function activity($description = null, $details = null, ?array $rel = null)
    {
        $data = (new ActivityData)->make($description, $details, $rel);
        self::storeActivity($data);
    }

    private static function storeActivity(array $data): void
    {
        $validator = Validator::make($data, config('LaravelLogger.defaultActivityModel')::rules());
        if ($validator->fails()) {
            $errors = self::prepareErrorMessage($validator->errors(), $data);
            if (config('LaravelLogger.logDBActivityLogFailuresToFile')) {
                Log::error('Failed to record activity event. Failed Validation: '.$errors);
            }
        } else {
            config('LaravelLogger.defaultActivityModel')::create($data);
        }
    }

    private static function prepareErrorMessage($validatorErrors, $data)
    {
        $errors = $validatorErrors->toArray();
        array_walk($errors, function (array &$value, $key) use ($data): void {
            $value[] = 'Value: '.(is_scalar($data[$key]) || $data[$key] === null ? (string) $data[$key] : json_encode($data[$key]));
        });

        return json_encode($errors);
    }
}
