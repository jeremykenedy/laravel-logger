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

class LaravelLoggerController extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use IpAddressDetails;
    use UserAgentDetails;
    use ValidatesRequests;
    private $_rolesEnabled;
    private $_rolesMiddlware;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

        $this->_rolesEnabled = config('LaravelLogger.rolesEnabled');
        $this->_rolesMiddlware = config('LaravelLogger.rolesMiddlware');

        if ($this->_rolesEnabled) {
            $this->middleware($this->_rolesMiddlware);
        }
    }

    /**
     * Add additional details to a collections.
     *
     * @param collection $collectionItems
     *
     * @return collection
     */
    private function mapAdditionalDetails($collectionItems)
    {
        $collectionItems->map(function ($collectionItem) {
            $eventTime = Carbon::parse($collectionItem->updated_at);
            $collectionItem['timePassed'] = $eventTime->diffForHumans();
            $collectionItem['userAgentDetails'] = UserAgentDetails::details($collectionItem->userAgent);
            $collectionItem['langDetails'] = UserAgentDetails::localeLang($collectionItem->locale);
            $collectionItem['userDetails'] = config('LaravelLogger.defaultUserModel')::find($collectionItem->userId);

            return $collectionItem;
        });

        return $collectionItems;
    }

    /**
     * Show the activities log dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function showAccessLog(Request $request)
    {
        if (config('LaravelLogger.loggerPaginationEnabled')) {
            $activities = config('LaravelLogger.defaultActivityModel')::orderBy('created_at', 'desc');
            if (config('LaravelLogger.enableSearch')) {
                $activities = $this->searchActivityLog($activities, $request);
            }
            $activities = $activities->paginate(config('LaravelLogger.loggerPaginationPerPage'))->withQueryString();
            $totalActivities = $activities->total();
        } else {
            $activities = config('LaravelLogger.defaultActivityModel')::orderBy('created_at', 'desc');

            if (config('LaravelLogger.enableSearch')) {
                $activities = $this->searchActivityLog($activities, $request);
            }
            $activities = $activities->get();
            $totalActivities = $activities->count();
        }

        self::mapAdditionalDetails($activities);

        if (config('LaravelLogger.enableLiveSearch')) {
            // We are querying only the paginated userIds because in a big application querying all user data is performance heavy
            $user_ids = array_unique($activities->pluck('userId')->toArray());
            $users = config('LaravelLogger.defaultUserModel')::whereIn(config('LaravelLogger.defaultUserIDField'), $user_ids)->get();
        } else {
            $users = config('LaravelLogger.defaultUserModel')::all();
        }

        $data = [
            'activities'        => $activities,
            'totalActivities'   => $totalActivities,
            'users'             => $users,
        ];

        return View('LaravelLogger::logger.activity-log', $data);
    }

    /**
     * Show an individual activity log entry.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Illuminate\Http\Response
     */
    public function showAccessLogEntry(Request $request, $id)
    {
        $activity = config('LaravelLogger.defaultActivityModel')::findOrFail($id);

        $userDetails = config('LaravelLogger.defaultUserModel')::find($activity->userId);
        $userAgentDetails = UserAgentDetails::details($activity->userAgent);
        $ipAddressDetails = IpAddressDetails::checkIP($activity->ipAddress);
        $langDetails = UserAgentDetails::localeLang($activity->locale);
        $eventTime = Carbon::parse($activity->created_at);
        $timePassed = $eventTime->diffForHumans();

        if (config('LaravelLogger.loggerPaginationEnabled')) {
            $userActivities = config('LaravelLogger.defaultActivityModel')::where('userId', $activity->userId)
            ->orderBy('created_at', 'desc')
            ->paginate(config('LaravelLogger.loggerPaginationPerPage'));
            $totalUserActivities = $userActivities->total();
        } else {
            $userActivities = config('LaravelLogger.defaultActivityModel')::where('userId', $activity->userId)
            ->orderBy('created_at', 'desc')
            ->get();
            $totalUserActivities = $userActivities->count();
        }

        self::mapAdditionalDetails($userActivities);

        $data = [
            'activity'              => $activity,
            'userDetails'           => $userDetails,
            'ipAddressDetails'      => $ipAddressDetails,
            'timePassed'            => $timePassed,
            'userAgentDetails'      => $userAgentDetails,
            'langDetails'           => $langDetails,
            'userActivities'        => $userActivities,
            'totalUserActivities'   => $totalUserActivities,
            'isClearedEntry'        => false,
        ];

        return View('LaravelLogger::logger.activity-log-item', $data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function clearActivityLog(Request $request)
    {
        $activities = config('LaravelLogger.defaultActivityModel')::all();
        foreach ($activities as $activity) {
            $activity->delete();
        }

        return redirect('activity')->with('success', trans('LaravelLogger::laravel-logger.messages.logClearedSuccessfuly'));
    }

    /**
     * Show the cleared activity log - softdeleted records.
     *
     * @return \Illuminate\Http\Response
     */
    public function showClearedActivityLog()
    {
        if (config('LaravelLogger.loggerPaginationEnabled')) {
            $activities = config('LaravelLogger.defaultActivityModel')::onlyTrashed()
            ->orderBy('created_at', 'desc')
            ->paginate(config('LaravelLogger.loggerPaginationPerPage'));
            $totalActivities = $activities->total();
        } else {
            $activities = config('LaravelLogger.defaultActivityModel')::onlyTrashed()
            ->orderBy('created_at', 'desc')
            ->get();
            $totalActivities = $activities->count();
        }

        self::mapAdditionalDetails($activities);

        $data = [
            'activities'        => $activities,
            'totalActivities'   => $totalActivities,
        ];

        return View('LaravelLogger::logger.activity-log-cleared', $data);
    }

    /**
     * Show an individual cleared (soft deleted) activity log entry.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Illuminate\Http\Response
     */
    public function showClearedAccessLogEntry(Request $request, $id)
    {
        $activity = self::getClearedActvity($id);

        $userDetails = config('LaravelLogger.defaultUserModel')::find($activity->userId);
        $userAgentDetails = UserAgentDetails::details($activity->userAgent);
        $ipAddressDetails = IpAddressDetails::checkIP($activity->ipAddress);
        $langDetails = UserAgentDetails::localeLang($activity->locale);
        $eventTime = Carbon::parse($activity->created_at);
        $timePassed = $eventTime->diffForHumans();

        $data = [
            'activity'              => $activity,
            'userDetails'           => $userDetails,
            'ipAddressDetails'      => $ipAddressDetails,
            'timePassed'            => $timePassed,
            'userAgentDetails'      => $userAgentDetails,
            'langDetails'           => $langDetails,
            'isClearedEntry'        => true,
        ];

        return View('LaravelLogger::logger.activity-log-item', $data);
    }

    /**
     * Get Cleared (Soft Deleted) Activity - Helper Method.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    private static function getClearedActvity($id)
    {
        $activity = config('LaravelLogger.defaultActivityModel')::onlyTrashed()->where('id', $id)->get();
        if (count($activity) != 1) {
            return abort(404);
        }

        return $activity[0];
    }

    /**
     * Destroy the specified resource from storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function destroyActivityLog(Request $request)
    {
        $activities = config('LaravelLogger.defaultActivityModel')::onlyTrashed()->get();
        foreach ($activities as $activity) {
            $activity->forceDelete();
        }

        return redirect('activity')->with('success', trans('LaravelLogger::laravel-logger.messages.logDestroyedSuccessfuly'));
    }

    /**
     * Restore the specified resource from soft deleted storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function restoreClearedActivityLog(Request $request)
    {
        $activities = config('LaravelLogger.defaultActivityModel')::onlyTrashed()->get();
        foreach ($activities as $activity) {
            $activity->restore();
        }

        return redirect('activity')->with('success', trans('LaravelLogger::laravel-logger.messages.logRestoredSuccessfuly'));
    }

    /**
     * Search the activity log according to specific criteria.
     *
     * @param query
     * @param request
     *
     * @return filtered query
     */
    public function searchActivityLog($query, $request)
    {
        if (in_array('description', explode(',', config('LaravelLogger.searchFields'))) && $request->get('description')) {
            $query->where('description', 'like', '%'.$request->get('description').'%');
        }

        if (in_array('user', explode(',', config('LaravelLogger.searchFields'))) && (int) $request->get('user')) {
            $query->where('userId', '=', (int) $request->get('user'));
        }

        if (in_array('method', explode(',', config('LaravelLogger.searchFields'))) && $request->get('method')) {
            $query->where('methodType', '=', $request->get('method'));
        }

        if (in_array('route', explode(',', config('LaravelLogger.searchFields'))) && $request->get('route')) {
            $query->where('route', 'like', '%'.$request->get('route').'%');
        }

        if (in_array('ip', explode(',', config('LaravelLogger.searchFields'))) && $request->get('ip_address')) {
            $query->where('ipAddress', 'like', '%'.$request->get('ip_address').'%');
        }

        return $query;
    }

    /**
     * Search the database users according to specific criteria.
     *
     * @param request
     *
     * @return filtered user data
     */
    public function liveSearch(Request $request)
    {
        $filteredUsers = config('LaravelLogger.defaultUserModel')::when(request('userid'), function ($q) {
            return $q->where(config('LaravelLogger.defaultUserIDField'), (int) request('userid', 0));
        })->when(request('email'), function ($q) {
            return $q->where('email', 'like', '%'.request('email').'%');
        });

        return response()->json($filteredUsers->get()->pluck('email', config('LaravelLogger.defaultUserIDField')), 200);
    }
}
