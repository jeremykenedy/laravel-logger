<?php

namespace jeremykenedy\LaravelLogger\App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use jeremykenedy\LaravelLogger\App\Http\Traits\IpAddressDetails;
use jeremykenedy\LaravelLogger\App\Http\Traits\UserAgentDetails;
use jeremykenedy\LaravelLogger\Support\ActivityExport;
use jeremykenedy\LaravelLogger\Support\ActivityFilters;
use jeremykenedy\LaravelLogger\Support\Dashboard;
use jeremykenedy\LaravelLogger\Support\GeoLocation;
use jeremykenedy\LaravelLogger\Support\UserAgentParser;

class LaravelLoggerController extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use IpAddressDetails;
    use UserAgentDetails;
    use ValidatesRequests;

    private $_rolesEnabled;

    private $_rolesMiddlware;

    public function __construct()
    {
        $this->middleware('auth');

        $this->_rolesEnabled = config('LaravelLogger.rolesEnabled');
        $this->_rolesMiddlware = config('LaravelLogger.rolesMiddlware');

        if ($this->_rolesEnabled) {
            $this->middleware($this->_rolesMiddlware);
        }
    }

    private function mapAdditionalDetails($collectionItems)
    {
        $users = config('LaravelLogger.defaultUserModel')::whereIn(config('LaravelLogger.defaultUserIDField'), $collectionItems->pluck('userId')->filter()->unique()->all())->get()->keyBy(config('LaravelLogger.defaultUserIDField'));

        return $collectionItems->map(function ($collectionItem) use ($users) {
            $eventTime = Carbon::parse($collectionItem->updated_at);
            $collectionItem->timePassed = $eventTime->diffForHumans();
            $collectionItem->userAgentDetails = (new UserAgentParser)->parse($collectionItem->userAgent);
            $collectionItem->langDetails = (new UserAgentParser)->locale($collectionItem->locale);
            $collectionItem->userDetails = $users->get($collectionItem->userId);

            return $collectionItem;
        });
    }

    public function showAccessLog(Request $request)
    {
        $activities = $this->paginateActivities($this->activityQuery(), $request, true);

        return view(Dashboard::view('activity-log'), [
            'activities' => $activities,
            'totalActivities' => $this->activityTotal($activities),
            'users' => $this->dashboardUsers($activities),
        ]);
    }

    public function showAccessLogEntry(Request $request, int $id)
    {
        $activity = config('LaravelLogger.defaultActivityModel')::findOrFail($id);

        return view(Dashboard::view('activity-log-item'), $this->entryData($activity, $request, false));
    }

    private function activityQuery()
    {
        return config('LaravelLogger.defaultActivityModel')::orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    private function paginateActivities($query, Request $request, bool $search = false)
    {
        $query = (new ActivityFilters)->dates($query, $request);
        if ($search && config('LaravelLogger.enableSearch')) {
            $query = $this->searchActivityLog($query, $request);
        }
        if (config('LaravelLogger.loggerCursorPaginationEnabled')) {
            $activities = $query->cursorPaginate(config('LaravelLogger.loggerPaginationPerPage'))->withQueryString();
        } elseif (config('LaravelLogger.loggerPaginationEnabled')) {
            $activities = $query->paginate(config('LaravelLogger.loggerPaginationPerPage'))->withQueryString();
        } else {
            $activities = $query->get();
        }
        $this->mapAdditionalDetails($activities);

        return $activities;
    }

    private function activityTotal($activities): int
    {
        if (config('LaravelLogger.loggerCursorPaginationEnabled')) {
            return 0;
        }

        return config('LaravelLogger.loggerPaginationEnabled') ? $activities->total() : $activities->count();
    }

    private function dashboardUsers($activities)
    {
        $users = config('LaravelLogger.defaultUserModel')::query();
        if (config('LaravelLogger.enableLiveSearch')) {
            $users->whereIn(config('LaravelLogger.defaultUserIDField'), $activities->pluck('userId')->unique()->all());
        }

        return $users->get();
    }

    private function entryData($activity, Request $request, bool $cleared): array
    {
        $data = [
            'activity' => $activity,
            'userDetails' => config('LaravelLogger.defaultUserModel')::where(config('LaravelLogger.defaultUserIDField'), $activity->userId)->first(),
            'ipAddressDetails' => (new GeoLocation)->lookup($activity->ipAddress),
            'timePassed' => Carbon::parse($activity->created_at)->diffForHumans(),
            'userAgentDetails' => (new UserAgentParser)->parse($activity->userAgent),
            'langDetails' => (new UserAgentParser)->locale($activity->locale),
            'isClearedEntry' => $cleared,
        ];
        if (! $cleared) {
            $data['userActivities'] = $this->paginateActivities($this->activityQuery()->where('userId', $activity->userId), $request);
            $data['totalUserActivities'] = $this->activityTotal($data['userActivities']);
        }

        return $data;
    }

    public function clearActivityLog(Request $request)
    {
        $this->changeActivities(config('LaravelLogger.defaultActivityModel')::query(), 'delete');

        return redirect('activity')->with('success', trans('LaravelLogger::laravel-logger.messages.logClearedSuccessfuly'));
    }

    public function showClearedActivityLog(Request $request)
    {
        $activities = $this->paginateActivities($this->activityQuery()->onlyTrashed(), $request);

        return view(Dashboard::view('activity-log-cleared'), [
            'activities' => $activities,
            'totalActivities' => $this->activityTotal($activities),
        ]);
    }

    public function showClearedAccessLogEntry(Request $request, int $id)
    {
        $activity = config('LaravelLogger.defaultActivityModel')::onlyTrashed()->findOrFail($id);

        return view(Dashboard::view('activity-log-item'), $this->entryData($activity, $request, true));
    }

    public function destroyActivityLog(Request $request)
    {
        $this->changeActivities(config('LaravelLogger.defaultActivityModel')::onlyTrashed(), 'forceDelete');

        return redirect('activity')->with('success', trans('LaravelLogger::laravel-logger.messages.logDestroyedSuccessfuly'));
    }

    public function restoreClearedActivityLog(Request $request)
    {
        $this->changeActivities(config('LaravelLogger.defaultActivityModel')::onlyTrashed(), 'restore');

        return redirect('activity')->with('success', trans('LaravelLogger::laravel-logger.messages.logRestoredSuccessfuly'));
    }

    private function changeActivities($query, string $method): void
    {
        $query->chunkById(500, function ($activities) use ($method) {
            foreach ($activities as $activity) {
                $activity->{$method}();
            }
        });
    }

    public function searchActivityLog($query, $request)
    {
        return (new ActivityFilters)->search($query, $request);
    }

    public function liveSearch(Request $request)
    {
        $filteredUsers = config('LaravelLogger.defaultUserModel')::when(request('userid'), function ($q) {
            return $q->where(config('LaravelLogger.defaultUserIDField'), (int) request('userid', 0));
        })->when(request('email'), function ($q) {
            return $q->where('email', 'like', '%'.request('email').'%');
        });

        return response()->json($filteredUsers->get()->pluck('email', config('LaravelLogger.defaultUserIDField')), 200);
    }

    public function exportActivityLog(Request $request)
    {
        abort_unless(config('LaravelLogger.enableExport'), 403);
        $format = $request->get('format', 'csv');
        $activities = $this->activityQuery();

        if (config('LaravelLogger.enableDateFiltering')) {
            $activities = (new ActivityFilters)->dates($activities, $request);
        }

        if (config('LaravelLogger.enableSearch')) {
            $activities = $this->searchActivityLog($activities, $request);
        }

        $activities = $activities->get();
        $activities = $this->mapAdditionalDetails($activities);

        return (new ActivityExport)->download($activities, $format);
    }
}
