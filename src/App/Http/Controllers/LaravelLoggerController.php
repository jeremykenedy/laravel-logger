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
        return $collectionItems->map(function ($collectionItem) {
            $eventTime = Carbon::parse($collectionItem->updated_at);
            $collectionItem->timePassed = $eventTime->diffForHumans();
            $collectionItem->userAgentDetails = UserAgentDetails::details($collectionItem->userAgent);
            $collectionItem->langDetails = UserAgentDetails::localeLang($collectionItem->locale);
            $collectionItem->userDetails = config('LaravelLogger.defaultUserModel')::find($collectionItem->userId);

            return $collectionItem;
        });
    }

    /**
     * Show the activities log dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function showAccessLog(Request $request)
    {
        if (config('LaravelLogger.loggerCursorPaginationEnabled')) {
            $activities = config('LaravelLogger.defaultActivityModel')::orderBy('created_at', 'desc');
            if (config('LaravelLogger.enableSearch')) {
                $activities = $this->searchActivityLog($activities, $request);
            }
            $activities = $activities->cursorPaginate(config('LaravelLogger.loggerPaginationPerPage'))->withQueryString();
            $totalActivities = 0;
        } elseif (config('LaravelLogger.loggerPaginationEnabled')) {
            $activities = config('LaravelLogger.defaultActivityModel')::orderBy('created_at', 'desc');

            // Apply date filtering
            if (config('LaravelLogger.enableDateFiltering')) {
                $activities = $this->applyDateFilter($activities, $request);
            }

            if (config('LaravelLogger.enableSearch')) {
                $activities = $this->searchActivityLog($activities, $request);
            }
            $activities = $activities->paginate(config('LaravelLogger.loggerPaginationPerPage'))->withQueryString();
            $totalActivities = $activities->total();
        } else {
            $activities = config('LaravelLogger.defaultActivityModel')::orderBy('created_at', 'desc');

            // Apply date filtering
            if (config('LaravelLogger.enableDateFiltering')) {
                $activities = $this->applyDateFilter($activities, $request);
            }

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

        return view('LaravelLogger::logger.activity-log', $data);
    }

    /**
     * Show an individual activity log entry.
     *
     * @return \Illuminate\Http\Response
     */
    public function showAccessLogEntry(Request $request, int $id)
    {
        $activity = config('LaravelLogger.defaultActivityModel')::findOrFail($id);

        $userDetails = config('LaravelLogger.defaultUserModel')::find($activity->userId);
        $userAgentDetails = UserAgentDetails::details($activity->userAgent);
        $ipAddressDetails = IpAddressDetails::checkIP($activity->ipAddress);
        $langDetails = UserAgentDetails::localeLang($activity->locale);
        $eventTime = Carbon::parse($activity->created_at);
        $timePassed = $eventTime->diffForHumans();

        if (config('LaravelLogger.loggerCursorPaginationEnabled')) {
            $userActivities = config('LaravelLogger.defaultActivityModel')::where('userId', $activity->userId)
                ->orderBy('created_at', 'desc')
                ->cursorPaginate(config('LaravelLogger.loggerPaginationPerPage'));
            $totalUserActivities = 0;
        } elseif (config('LaravelLogger.loggerPaginationEnabled')) {
            $userActivities = config('LaravelLogger.defaultActivityModel')::where('userId', $activity->userId)
            ->orderBy('created_at', 'desc');

            // Apply date filtering
            if (config('LaravelLogger.enableDateFiltering')) {
                $userActivities = $this->applyDateFilter($userActivities, $request);
            }

            $userActivities = $userActivities->paginate(config('LaravelLogger.loggerPaginationPerPage'));
            $totalUserActivities = $userActivities->total();
        } else {
            $userActivities = config('LaravelLogger.defaultActivityModel')::where('userId', $activity->userId)
            ->orderBy('created_at', 'desc');

            // Apply date filtering
            if (config('LaravelLogger.enableDateFiltering')) {
                $userActivities = $this->applyDateFilter($userActivities, $request);
            }

            $userActivities = $userActivities->get();
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

        return view('LaravelLogger::logger.activity-log-item', $data);
    }

    /**
     * Remove the specified resource from storage.
     *
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
    public function showClearedActivityLog(Request $request)
    {
        if (config('LaravelLogger.loggerCursorPaginationEnabled')) {
            $activities = config('LaravelLogger.defaultActivityModel')::onlyTrashed()
                ->orderBy('created_at', 'desc')
                ->paginate(config('LaravelLogger.loggerPaginationPerPage'));
            $totalActivities = 0;
        } elseif (config('LaravelLogger.loggerPaginationEnabled')) {
            $activities = config('LaravelLogger.defaultActivityModel')::onlyTrashed()
            ->orderBy('created_at', 'desc');

            // Apply date filtering
            if (config('LaravelLogger.enableDateFiltering')) {
                $activities = $this->applyDateFilter($activities, $request);
            }

            $activities = $activities->paginate(config('LaravelLogger.loggerPaginationPerPage'));
            $totalActivities = $activities->total();
        } else {
            $activities = config('LaravelLogger.defaultActivityModel')::onlyTrashed()
            ->orderBy('created_at', 'desc');

            // Apply date filtering
            if (config('LaravelLogger.enableDateFiltering')) {
                $activities = $this->applyDateFilter($activities, $request);
            }

            $activities = $activities->get();
            $totalActivities = $activities->count();
        }

        self::mapAdditionalDetails($activities);

        $data = [
            'activities'        => $activities,
            'totalActivities'   => $totalActivities,
        ];

        return view('LaravelLogger::logger.activity-log-cleared', $data);
    }

    /**
     * Show an individual cleared (soft deleted) activity log entry.
     *
     * @return \Illuminate\Http\Response
     */
    public function showClearedAccessLogEntry(Request $request, int $id)
    {
        $activity = $this->getClearedActvity($id);

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

        return view('LaravelLogger::logger.activity-log-item', $data);
    }

    /**
     * Get Cleared (Soft Deleted) Activity - Helper Method.
     *
     *
     * @return \Illuminate\Http\Response
     */
    private function getClearedActvity(int $id)
    {
        $activity = config('LaravelLogger.defaultActivityModel')::onlyTrashed()->where('id', $id)->get();
        if (count($activity) !== 1) {
            return abort(404);
        }

        return $activity[0];
    }

    /**
     * Destroy the specified resource from storage.
     *
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
     * Apply date filtering to the activity log query.
     *
     * @param mixed $query
     *
     * @return mixed
     */
    private function applyDateFilter($query, Request $request)
    {
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // Filter by predefined periods
        if ($request->filled('period')) {
            $period = $request->get('period');
            switch ($period) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', today()->subDay());
                    break;
                case 'last_7_days':
                    $query->where('created_at', '>=', now()->subDays(7));
                    break;
                case 'last_30_days':
                    $query->where('created_at', '>=', now()->subDays(30));
                    break;
                case 'last_3_months':
                    $query->where('created_at', '>=', now()->subMonths(3));
                    break;
                case 'last_6_months':
                    $query->where('created_at', '>=', now()->subMonths(6));
                    break;
                case 'last_year':
                    $query->where('created_at', '>=', now()->subYear());
                    break;
            }
        }

        return $query;
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

    /**
     * Export activity log data in various formats.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportActivityLog(Request $request)
    {
        $format = $request->get('format', 'csv');
        $activities = config('LaravelLogger.defaultActivityModel')::orderBy('created_at', 'desc');

        // Apply date filtering
        if (config('LaravelLogger.enableDateFiltering')) {
            $activities = $this->applyDateFilter($activities, $request);
        }

        // Apply search filters
        if (config('LaravelLogger.enableSearch')) {
            $activities = $this->searchActivityLog($activities, $request);
        }

        $activities = $activities->get();
        $activities = $this->mapAdditionalDetails($activities);

        switch ($format) {
            case 'csv':
                return $this->exportToCsv($activities);
            case 'json':
                return $this->exportToJson($activities);
            case 'excel':
                return $this->exportToExcel($activities);
            default:
                return redirect()->back()->with('error', 'Invalid export format');
        }
    }

    /**
     * Export activities to CSV format.
     *
     * @param mixed $activities
     *
     * @return \Illuminate\Http\Response
     */
    private function exportToCsv($activities)
    {
        $filename = 'activity_log_'.now()->format('Y-m-d_H-i-s').'.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($activities): void {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'ID',
                'Description',
                'Details',
                'User Type',
                'User ID',
                'User Email',
                'Route',
                'IP Address',
                'User Agent',
                'Locale',
                'Referer',
                'Method Type',
                'Created At',
                'Updated At',
            ]);

            // CSV Data
            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->id,
                    $activity->description,
                    $activity->details,
                    $activity->userType,
                    $activity->userId,
                    $activity->userDetails ? $activity->userDetails->email : 'N/A',
                    $activity->route,
                    $activity->ipAddress,
                    $activity->userAgent,
                    $activity->locale,
                    $activity->referer,
                    $activity->methodType,
                    $activity->created_at,
                    $activity->updated_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export activities to JSON format.
     *
     * @param mixed $activities
     *
     * @return \Illuminate\Http\Response
     */
    private function exportToJson($activities)
    {
        $filename = 'activity_log_'.now()->format('Y-m-d_H-i-s').'.json';

        $data = $activities->map(function ($activity): array {
            return [
                'id'                 => $activity->id,
                'description'        => $activity->description,
                'details'            => $activity->details,
                'user_type'          => $activity->userType,
                'user_id'            => $activity->userId,
                'user_email'         => $activity->userDetails ? $activity->userDetails->email : null,
                'route'              => $activity->route,
                'ip_address'         => $activity->ipAddress,
                'user_agent'         => $activity->userAgent,
                'locale'             => $activity->locale,
                'referer'            => $activity->referer,
                'method_type'        => $activity->methodType,
                'created_at'         => $activity->created_at,
                'updated_at'         => $activity->updated_at,
                'time_passed'        => $activity->timePassed,
                'user_agent_details' => $activity->userAgentDetails,
                'lang_details'       => $activity->langDetails,
            ];
        });

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Export activities to Excel format.
     *
     * @param mixed $activities
     *
     * @return \Illuminate\Http\Response
     */
    private function exportToExcel($activities)
    {
        $filename = 'activity_log_'.now()->format('Y-m-d_H-i-s').'.xlsx';

        // For Excel export, we'll use a simple CSV format with .xlsx extension
        // In a real implementation, you might want to use Laravel Excel package
        $headers = [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($activities): void {
            $file = fopen('php://output', 'w');

            // Excel Headers
            fputcsv($file, [
                'ID',
                'Description',
                'Details',
                'User Type',
                'User ID',
                'User Email',
                'Route',
                'IP Address',
                'User Agent',
                'Locale',
                'Referer',
                'Method Type',
                'Created At',
                'Updated At',
            ]);

            // Excel Data
            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->id,
                    $activity->description,
                    $activity->details,
                    $activity->userType,
                    $activity->userId,
                    $activity->userDetails ? $activity->userDetails->email : 'N/A',
                    $activity->route,
                    $activity->ipAddress,
                    $activity->userAgent,
                    $activity->locale,
                    $activity->referer,
                    $activity->methodType,
                    $activity->created_at,
                    $activity->updated_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
