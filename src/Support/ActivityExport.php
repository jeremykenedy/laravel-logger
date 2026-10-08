<?php

namespace jeremykenedy\LaravelLogger\Support;

class ActivityExport
{
    public function download($activities, string $format)
    {
        switch ($format) {
            case 'csv':
                return $this->csv($activities);
            case 'json':
                return $this->json($activities);
            case 'excel':
                return $this->excel($activities);
            default:
                return redirect()->back()->with('error', 'Invalid export format');
        }
    }

    private function csv($activities)
    {
        $filename = 'activity_log_'.now()->format('Y-m-d_H-i-s').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($activities): void {
            $file = fopen('php://output', 'w');
            $escape = PHP_VERSION_ID < 70400 ? '\\' : '';

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
            ], ',', '"', $escape);

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
                ]), ',', '"', $escape);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function json($activities)
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

    private function excel($activities)
    {
        return (new ExcelExport)->download($activities);
    }
}
