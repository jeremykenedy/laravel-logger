<?php

namespace jeremykenedy\LaravelLogger\Support;

use Illuminate\Http\Request;

class ActivityFilters
{
    public function dates($query, Request $request)
    {
        if (! config('LaravelLogger.enableDateFiltering')) {
            return $query;
        }

        foreach (['date_from' => '>=', 'date_to' => '<='] as $field => $operator) {
            if ($request->filled($field)) {
                $query->whereDate('created_at', $operator, $request->input($field));
            }
        }

        return $this->period($query, $request->input('period'));
    }

    private function period($query, $period)
    {
        $dates = ['today' => today(), 'yesterday' => today()->subDay()];
        if (isset($dates[$period])) {
            return $query->whereDate('created_at', $dates[$period]);
        }

        $starts = [
            'last_7_days' => now()->subDays(7),
            'last_30_days' => now()->subDays(30),
            'last_3_months' => now()->subMonths(3),
            'last_6_months' => now()->subMonths(6),
            'last_year' => now()->subYear(),
        ];

        return isset($starts[$period]) ? $query->where('created_at', '>=', $starts[$period]) : $query;
    }

    public function search($query, $request)
    {
        $fields = explode(',', config('LaravelLogger.searchFields'));
        $filters = [
            'description' => ['description', 'description', 'like'],
            'user' => ['user', 'userId', '='],
            'method' => ['method', 'methodType', '='],
            'route' => ['route', 'route', 'like'],
            'ip' => ['ip_address', 'ipAddress', 'like'],
        ];
        foreach ($filters as $field => [$parameter, $column, $operator]) {
            $value = $request->get($parameter);
            if ($field === 'user') {
                $value = (int) $value;
            }
            if (in_array($field, $fields) && $value) {
                $query->where($column, $operator, $operator === 'like' ? '%'.$value.'%' : $value);
            }
        }

        return $query;
    }
}
