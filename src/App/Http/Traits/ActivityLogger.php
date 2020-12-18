<?php

namespace jeremykenedy\LaravelLogger\App\Http\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Jaybizzle\LaravelCrawlerDetect\Facades\LaravelCrawlerDetect as Crawler;

trait ActivityLogger
{
    /**
     * Laravel Logger Log Activity.
     *
     * @param null $description
     * @param null $details
     *
     * @return void
     */
    public static function activity($description = null, $details = null)
    {
        $userType = trans('LaravelLogger::laravel-logger.userTypes.guest');
        $userId = null;

        if (Auth::check()) {
            $userType = trans('LaravelLogger::laravel-logger.userTypes.registered');
            $userIdField = config('LaravelLogger.defaultUserIDField');
            $userId = Request::user()->{$userIdField};
        }

        if (Crawler::isCrawler()) {
            $userType = trans('LaravelLogger::laravel-logger.userTypes.crawler');
            if (is_null($description)) {
                $description = $userType.' '.trans('LaravelLogger::laravel-logger.verbTypes.crawled').' '.Request::fullUrl();
            }
        }

        if (!$description) {
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

        $data = [
            'description'   => $description,
            'details'       => $details,
            'userType'      => $userType,
            'userId'        => $userId,
            'route'         => Request::fullUrl(),
            'ipAddress'     => Request::ip(),
            'userAgent'     => Request::header('user-agent'),
            'locale'        => Request::header('accept-language'),
            'referer'       => Request::header('referer'),
            'methodType'    => Request::method(),
        ];

        // Validation Instance
        $validator = Validator::make($data, config('laravel-logger.defaultActivityModel')::rules());
        if ($validator->fails()) {
            $errors = self::prepareErrorMessage($validator->errors(), $data);
            if (config('LaravelLogger.logDBActivityLogFailuresToFile')) {
                Log::error('Failed to record activity event. Failed Validation: '.$errors);
            }
        } else {
            self::storeActivity($data);
        }
    }

    /**
     * Store activity entry to database.
     *
     * @param array $data
     *
     * @return void
     */
    private static function storeActivity($data)
    {
        config('laravel-logger.defaultActivityModel')::create([
            'description'   => $data['description'],
            'details'       => $data['details'],
            'userType'      => $data['userType'],
            'userId'        => $data['userId'],
            'route'         => $data['route'],
            'ipAddress'     => $data['ipAddress'],
            'userAgent'     => $data['userAgent'],
            'locale'        => $data['locale'],
            'referer'       => $data['referer'],
            'methodType'    => $data['methodType'],
        ]);
    }

    /**
     * Prepare Error Message (add the actual value of the error field).
     *
     * @param $validator
     * @param $data
     *
     * @return string
     */
    private static function prepareErrorMessage($validatorErrors, $data)
    {
        $errors = json_decode(json_encode($validatorErrors, true));
        array_walk($errors, function (&$value, $key) use ($data) {
            array_push($value, "Value: $data[$key]");
        });

        return json_encode($errors, true);
    }
}
