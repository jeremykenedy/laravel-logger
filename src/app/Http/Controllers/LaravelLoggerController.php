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
use jeremykenedy\LaravelLogger\App\Models\Activity;

class LaravelLoggerController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, IpAddressDetails, UserAgentDetails, ValidatesRequests;

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
            $collectionItem['userAgentDetails'] = UserAgentDetails::details($collectionItem->useragent);
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
            $activities = Activity::orderBy('created_at', 'desc');
            if (config('LaravelLogger.enableSearch')) {
                $activities = $this->searchActivityLog($activities, $request);
            }
            $activities = $activities->paginate(config('LaravelLogger.loggerPaginationPerPage'));
            $totalActivities = $activities->total();
        } else {
            $activities = Activity::orderBy('created_at', 'desc');

            if (config('LaravelLogger.enableSearch')) {
                $activities = $this->searchActivityLog($activities, $request);
            }
            $activities = $activities->get();
            $totalActivities = $activities->count();
        }

        self::mapAdditionalDetails($activities);

        $users = config('LaravelLogger.defaultUserModel')::all();

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
        $activity = Activity::findOrFail($id);

        $userDetails = config('LaravelLogger.defaultUserModel')::find($activity->userId);
        $userAgentDetails = UserAgentDetails::details($activity->useragent);
        $ipAddressDetails = IpAddressDetails::checkIP($activity->ipAddress);
        $langDetails = UserAgentDetails::localeLang($activity->locale);
        $eventTime = Carbon::parse($activity->created_at);
        $timePassed = $eventTime->diffForHumans();

        if (config('LaravelLogger.loggerPaginationEnabled')) {
            $userActivities = Activity::where('userId', $activity->userId)
            ->orderBy('created_at', 'desc')
            ->paginate(config('LaravelLogger.loggerPaginationPerPage'));
            $totalUserActivities = $userActivities->total();
        } else {
            $userActivities = Activity::where('userId', $activity->userId)
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
        $activities = Activity::all();
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
            $activities = Activity::onlyTrashed()
            ->orderBy('created_at', 'desc')
            ->paginate(config('LaravelLogger.loggerPaginationPerPage'));
            $totalActivities = $activities->total();
        } else {
            $activities = Activity::onlyTrashed()
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
        $userAgentDetails = UserAgentDetails::details($activity->useragent);
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
        $activity = Activity::onlyTrashed()->where('id', $id)->get();
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
        $activities = Activity::onlyTrashed()->get();
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
        $activities = Activity::onlyTrashed()->get();
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
    public function searchActivityLog($query, $requeset)
    {
        if (in_array('description', explode(',', config('LaravelLogger.searchFields'))) && $requeset->get('description')) {
            $query->where('description', 'like', '%'.$requeset->get('description').'%');
        }

        if (in_array('user', explode(',', config('LaravelLogger.searchFields'))) && $requeset->get('user')) {
            $query->where('userId', '=', $requeset->get('user'));
        }

        if (in_array('method', explode(',', config('LaravelLogger.searchFields'))) && $requeset->get('method')) {
            $query->where('methodType', '=', $requeset->get('method'));
        }

        if (in_array('route', explode(',', config('LaravelLogger.searchFields'))) && $requeset->get('route')) {
            $query->where('route', 'like', '%'.$requeset->get('route').'%');
        }

        if (in_array('ip', explode(',', config('LaravelLogger.searchFields'))) && $requeset->get('ip_address')) {
            $query->where('ipAddress', 'like', '%'.$requeset->get('ip_address').'%');
        }

        return $query;
    }
}
