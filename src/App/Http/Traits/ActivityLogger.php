<?php

namespace jeremykenedy\LaravelLogger\App\Http\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use jeremykenedy\LaravelLogger\Facades\Crawler;

trait ActivityLogger
{
    public function activity($description = null, $details = null, ?array $rel = null)
    {
        $userType = trans('LaravelLogger::laravel-logger.userTypes.guest');
        $userId = null;

        if (Auth::check()) {
            $userType = trans('LaravelLogger::laravel-logger.userTypes.registered');
            $userIdField = config('LaravelLogger.defaultUserIDField');
            $userId = Auth::user()->{$userIdField};
        }

        if (Crawler::isCrawler()) {
            $userType = trans('LaravelLogger::laravel-logger.userTypes.crawler');
            if (is_null($description)) {
                $description = $userType.' '.trans('LaravelLogger::laravel-logger.verbTypes.crawled').' '.Request::fullUrl();
            }
        }

        if (! $description) {
            switch (strtolower(Request::method())) {
                case 'post':
                    $verb = trans('LaravelLogger::laravel-logger.verbTypes.created');
                    break;

                case 'patch':
                case 'put':
                    $verb = trans('LaravelLogger::laravel-logger.verbTypes.edited');
                    break;

                case 'delete':
                    $verb = trans('LaravelLogger::laravel-logger.verbTypes.deleted');
                    break;

                case 'get':
                default:
                    $verb = trans('LaravelLogger::laravel-logger.verbTypes.viewed');
                    break;
            }

            $description = $verb.' '.Request::path();
        }

        $ip = Request::ip();

        $relId = null;
        $relModel = null;
        if (is_array($rel) && array_key_exists('id', $rel) && array_key_exists('model', $rel)) {
            $relId = $rel['id'];
            $relModel = $rel['model'];
        }

        $data = [
            'description' => $description,
            'details' => $details,
            'userType' => $userType,
            'userId' => $userId,
            'route' => Request::fullUrl(),
            'ipAddress' => $ip,
            'userAgent' => Request::header('user-agent'),
            'locale' => Request::header('accept-language'),
            'referer' => Request::header('referer'),
            'methodType' => Request::method(),
            'relId' => $relId,
            'relModel' => $relModel,
        ];

        $validator = Validator::make($data, config('LaravelLogger.defaultActivityModel')::rules());
        if ($validator->fails()) {
            $errors = self::prepareErrorMessage($validator->errors(), $data);
            if (config('LaravelLogger.logDBActivityLogFailuresToFile')) {
                Log::error('Failed to record activity event. Failed Validation: '.$errors);
            }
        } else {
            self::storeActivity($data);
        }
    }

    private static function storeActivity(array $data): void
    {
        config('LaravelLogger.defaultActivityModel')::create([
            'description' => $data['description'],
            'details' => $data['details'],
            'userType' => $data['userType'],
            'userId' => $data['userId'],
            'route' => $data['route'],
            'ipAddress' => $data['ipAddress'],
            'userAgent' => $data['userAgent'],
            'locale' => $data['locale'],
            'referer' => $data['referer'],
            'methodType' => $data['methodType'],
            'relId' => $data['relId'],
            'relModel' => $data['relModel'],
        ]);
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
