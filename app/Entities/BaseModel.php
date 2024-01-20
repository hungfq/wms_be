<?php

namespace App\Entities;

use App\Databases\BaseBuilder;
use App\Entities\Observer\EventDBObserver;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    const CREATED_BY = 'created_by';
    const UPDATED_BY = 'updated_by';

    protected $connection = 'mysql';

    protected static function boot()
    {
        parent::boot();
        static::observe(EventDBObserver::class);
    }

    /**
     * @return int
     */
    public function freshTimestamp()
    {
        return time();
    }

    public static function getTableName()
    {
        return (new static)->getTable();
    }

    public static function getColumnName($column)
    {
        return self::getTableName() . '.' . $column;
    }

    public function getCreatedAtAttribute($date)
    {
        return $date;
    }

    public function getUpdatedAtAttribute($date)
    {
        return $date;
    }


    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return BaseBuilder|static
     */
    public function newEloquentBuilder($query)
    {
        return new BaseBuilder($query);
    }

    /**
     * @return array
     */
    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }


    public function getQualifiedUpdatedByColumn()
    {
        return $this->qualifyColumn($this->getUpdatedByColumn());
    }

    public function getUpdatedByColumn()
    {
        return self::UPDATED_BY;
    }

    public function getQualifiedCreatedByColumn()
    {
        return $this->qualifyColumn($this->getCreatedByColumn());
    }


    public function getCreatedByColumn()
    {
        return self::CREATED_BY;
    }
}
