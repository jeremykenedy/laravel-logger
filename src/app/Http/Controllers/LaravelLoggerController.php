<?php

namespace jeremykenedy\LaravelLogger\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use jeremykenedy\LaravelLogger\App\Http\Traits\IpAddressDetails;
use jeremykenedy\LaravelLogger\App\Http\Traits\UserAgentDetails;
use jeremykenedy\LaravelLogger\App\Models\Activity;
use Illuminate\Http\Request;

class LaravelLoggerController extends Controller
{
    use IpAddressDetails, UserAgentDetails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the activities log dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function showAccessLog()
    {

        $activities = Activity::orderBy('created_at', 'desc')->get();

        $activities->map(function ($activity) {
            $eventTime = Carbon::parse($activity->updated_at);
            $activity['timePassed'] = $eventTime->diffForHumans();
            $activity['userAgentDetails'] = UserAgentDetails::details($activity->useragent);
            $activity['langDetails'] = UserAgentDetails::localeLang($activity->locale);
            $activity['userDetails'] = config('LaravelLogger.defaultUserModel')::find($activity->userId);

            return $activity;
        });

        return View('LaravelLogger::logger.activity-log', compact('activities'));
    }

    /**
     * Show an individual activity log entry.
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

        $userActivities = Activity::where('userId', $activity->userId)
                       ->orderBy('created_at', 'desc')
                       ->get();

        $userActivities->map(function ($userActivity) {
            $eventTime = Carbon::parse($userActivity->updated_at);
            $userActivity['timePassed'] = $eventTime->diffForHumans();
            $userActivity['userAgentDetails'] = UserAgentDetails::details($userActivity->useragent);
            $userActivity['langDetails'] = UserAgentDetails::localeLang($userActivity->locale);
            $userActivity['userDetails'] = config('LaravelLogger.defaultUserModel')::find($userActivity->userId);

            return $userActivity;
        });

        $data  = [
            'activity'          => $activity,
            'userDetails'       => $userDetails,
            'ipAddressDetails'  => $ipAddressDetails,
            'timePassed'        => $timePassed,
            'userAgentDetails'  => $userAgentDetails,
            'langDetails'       => $langDetails,
            'userActivities'    => $userActivities,
        ];

        return View('LaravelLogger::logger.activity-log-item', $data);

    }

}
