<?php

namespace jeremykenedy\LaravelLogger\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use jeremykenedy\LaravelLogger\Support\UserAgentParser;

class Activity extends Model
{
    use SoftDeletes;

    protected $table;

    protected $connection;

    public $timestamps = true;

    protected $guarded = [
        'id',
    ];

    protected $fillable = [
        'description',
        'details',
        'userType',
        'userId',
        'route',
        'ipAddress',
        'userAgent',
        'locale',
        'referer',
        'methodType',
        'relId',
        'relModel',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'description' => 'string',
        'details' => 'string',
        'user' => 'integer',
        'route' => 'string',
        'ipAddress' => 'string',
        'userAgent' => 'string',
        'locale' => 'string',
        'referer' => 'string',
        'methodType' => 'string',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('LaravelLogger.loggerDatabaseTable');
        $this->connection = config('LaravelLogger.loggerDatabaseConnection');
    }

    public function getConnectionName()
    {
        return $this->connection;
    }

    public function getTableName()
    {
        return $this->table;
    }

    public function user()
    {
        return $this->hasOne(config('LaravelLogger.defaultUserModel'));
    }

    public static function rules($merge = []): array
    {
        return array_merge(
            [
                'description' => 'required|string',
                'details' => 'nullable|string',
                'userType' => 'required|string',
                'userId' => 'nullable|integer',
                'route' => 'nullable|url',
                'ipAddress' => 'nullable|ip',
                'userAgent' => 'nullable|string',
                'locale' => 'nullable|string',
                'referer' => 'nullable|string',
                'methodType' => 'nullable|string',
            ],
            $merge
        );
    }

    public function getUserAgentDetailsAttribute()
    {
        return (new UserAgentParser)->parse($this->getAttribute('userAgent'));
    }
}
