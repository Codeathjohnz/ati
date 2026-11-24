<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Rows imported from a historical dataset (Phase 7) are tagged
 * is_historical=true but otherwise live in the same table as everything
 * the live portal creates. Every normal query — dashboards, trainee lists,
 * reports, gap analysis — should never see them unless it explicitly asks
 * to, so they can't silently skew a live statistic or show up where an
 * officer or trainee wouldn't expect a record with no real portal activity
 * behind it.
 */
trait ExcludesHistoricalByDefault
{
    protected static function bootExcludesHistoricalByDefault(): void
    {
        static::addGlobalScope('excludeHistorical', function (Builder $builder) {
            $builder->where($builder->getModel()->getTable().'.is_historical', false);
        });
    }

    public function scopeWithHistorical(Builder $query): Builder
    {
        return $query->withoutGlobalScope('excludeHistorical');
    }

    public function scopeOnlyHistorical(Builder $query): Builder
    {
        return $query->withoutGlobalScope('excludeHistorical')->where($query->getModel()->getTable().'.is_historical', true);
    }
}
