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
use jeremykenedy\LaravelLogger\Support\Dashboard;
use jeremykenedy\LaravelLogger\Support\ExcelExport;

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
            $collectionItem->userAgentDetails = UserAgentDetails::details($collectionItem->userAgent);
            $collectionItem->langDetails = UserAgentDetails::localeLang($collectionItem->locale);
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
        $query = $this->applyDateFilter($query, $request);
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
            'ipAddressDetails' => IpAddressDetails::checkIP($activity->ipAddress),
            'timePassed' => Carbon::parse($activity->created_at)->diffForHumans(),
            'userAgentDetails' => UserAgentDetails::details($activity->userAgent),
            'langDetails' => UserAgentDetails::localeLang($activity->locale),
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

    private function applyDateFilter($query, Request $request)
    {
        if (! config('LaravelLogger.enableDateFiltering')) {
            return $query;
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

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
            $activities = $this->applyDateFilter($activities, $request);
        }

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

    private function exportToCsv($activities)
    {
        $filename = 'activity_log_'.now()->format('Y-m-d_H-i-s').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($activities): void {
            $file = fopen('php://output', 'w');

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
            ], ',', '"', '');

            foreach ($activities as $activity) {
                fputcsv($file, array_map([ExcelExport::class, 'safeCell'], [
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
                ]), ',', '"', '');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToJson($activities)
    {
        $filename = 'activity_log_'.now()->format('Y-m-d_H-i-s').'.json';

        $data = $activities->map(function ($activity): array {
            return [
                'id' => $activity->id,
                'description' => $activity->description,
                'details' => $activity->details,
                'user_type' => $activity->userType,
                'user_id' => $activity->userId,
                'user_email' => $activity->userDetails ? $activity->userDetails->email : null,
                'route' => $activity->route,
                'ip_address' => $activity->ipAddress,
                'user_agent' => $activity->userAgent,
                'locale' => $activity->locale,
                'referer' => $activity->referer,
                'method_type' => $activity->methodType,
                'created_at' => $activity->created_at,
                'updated_at' => $activity->updated_at,
                'time_passed' => $activity->timePassed,
                'user_agent_details' => $activity->userAgentDetails,
                'lang_details' => $activity->langDetails,
            ];
        });

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function exportToExcel($activities)
    {
        return (new ExcelExport)->download($activities);
    }
}
