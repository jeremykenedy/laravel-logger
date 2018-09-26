<?php

namespace jeremykenedy\LaravelLogger\App\Http\Traits;

use Crawler;
use Illuminate\Support\Facades\Log;
use jeremykenedy\LaravelLogger\App\Models\Activity;
use Validator;

trait ActivityLogger
{
    /**
     * Laravel Logger Log Activity.
     *
     * @param string $description
     *
     * @return void
     */
    public static function activity($description = null)
    {
        $userType = trans('LaravelLogger::laravel-logger.userTypes.guest');
        $userId = null;

        if (\Auth::check()) {
            $userType = trans('LaravelLogger::laravel-logger.userTypes.registered');
            $userId = \Request::user()->id;
        }

        if (Crawler::isCrawler()) {
            $userType = trans('LaravelLogger::laravel-logger.userTypes.crawler');
            $description = $userType.' '.trans('LaravelLogger::laravel-logger.verbTypes.crawled').' '.\Request::fullUrl();
        }

        if (!$description) {
            switch (strtolower(\Request::method())) {
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

            $description = $verb.' '.\Request::path();
        }

        $data = [
            'description'   => $description,
            'userType'      => $userType,
            'userId'        => $userId,
            'route'         => \Request::fullUrl(),
            'ipAddress'     => \Request::ip(),
            'userAgent'     => \Request::header('user-agent'),
            'locale'        => \Request::header('accept-language'),
            'referer'       => \Request::header('referer'),
            'methodType'    => \Request::method(),
        ];

        // Validation Instance
        $validator = Validator::make($data, Activity::Rules([]));
        if ($validator->fails()) {
            $errors = json_encode($validator->errors(), true);
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
        Activity::create([
            'description'   => $data['description'],
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
}
