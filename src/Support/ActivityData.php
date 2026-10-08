<?php

namespace jeremykenedy\LaravelLogger\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use jeremykenedy\LaravelLogger\Facades\Crawler;

class ActivityData
{
    public function make($description, $details, ?array $related): array
    {
        [$userType, $userId, $crawler] = $this->user();
        [$relatedId, $relatedModel] = $this->related($related);

        return [
            'description' => $this->description($description, $userType, $crawler),
            'details' => $details,
            'userType' => $userType,
            'userId' => $userId,
            'route' => Request::fullUrl(),
            'ipAddress' => Request::ip(),
            'userAgent' => Request::header('user-agent'),
            'locale' => Request::header('accept-language'),
            'referer' => Request::header('referer'),
            'methodType' => Request::method(),
            'relId' => $relatedId,
            'relModel' => $relatedModel,
        ];
    }

    private function user(): array
    {
        $type = trans('LaravelLogger::laravel-logger.userTypes.guest');
        $id = null;
        if (Auth::check()) {
            $type = trans('LaravelLogger::laravel-logger.userTypes.registered');
            $id = Auth::user()->{config('LaravelLogger.defaultUserIDField')};
        }
        $crawler = (bool) Crawler::isCrawler();
        if ($crawler) {
            $type = trans('LaravelLogger::laravel-logger.userTypes.crawler');
        }

        return [$type, $id, $crawler];
    }

    private function description($description, $userType, bool $crawler)
    {
        if ($crawler && $description === null) {
            return $userType.' '.trans('LaravelLogger::laravel-logger.verbTypes.crawled').' '.Request::fullUrl();
        }
        if ($description) {
            return $description;
        }

        $verbs = ['post' => 'created', 'patch' => 'edited', 'put' => 'edited', 'delete' => 'deleted'];
        $verb = $verbs[strtolower(Request::method())] ?? 'viewed';

        return trans('LaravelLogger::laravel-logger.verbTypes.'.$verb).' '.Request::path();
    }

    private function related(?array $related): array
    {
        if ($related !== null && array_key_exists('id', $related) && array_key_exists('model', $related)) {
            return [$related['id'], $related['model']];
        }

        return [null, null];
    }
}
