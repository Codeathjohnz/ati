import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { competencyGapsApi, type CompetencyGapListFilters } from '../../api/competencyGaps'
import { metaApi } from '../../api/meta'
import { Badge, Button, Card, Field, LoadingBlock, Pagination, Select } from '../../components/ui'

export default function CompetencyGaps() {
  const [filters, setFilters] = useState<CompetencyGapListFilters>({})
  const [page, setPage] = useState(1)

  const { data: meta } = useQuery({ queryKey: ['meta'], queryFn: metaApi.get })
  const { data, isLoading } = useQuery({
    queryKey: ['competency-gaps', { ...filters, page }],
    queryFn: () => competencyGapsApi.list({ ...filters, page }),
  })

  function update<K extends keyof CompetencyGapListFilters>(key: K, value: CompetencyGapListFilters[K]) {
    setFilters((f) => ({ ...f, [key]: value }))
    setPage(1)
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-semibold text-slate-900">Competency Gap Analysis</h1>
        <p className="text-sm text-slate-500">
          Identify trainees who have not yet completed a given chain level, to plan targeted follow-up training.
        </p>
      </div>

      <Card>
        <div className="grid grid-cols-1 gap-3 border-b border-slate-100 p-4 sm:grid-cols-2 lg:grid-cols-4">
          <Field label="Chain Level (has gap in)">
            <Select value={filters.chain_level ?? ''} onChange={(e) => update('chain_level', e.target.value)}>
              <option value="">Any</option>
              {meta?.chain_levels.map((c) => <option key={c} value={c}>{c}</option>)}
            </Select>
          </Field>
          <Field label="Sex">
            <Select value={filters.sex ?? ''} onChange={(e) => update('sex', e.target.value)}>
              <option value="">Any</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </Select>
          </Field>
          <Field label="Province">
            <Select value={filters.province ?? ''} onChange={(e) => update('province', e.target.value)}>
              <option value="">Any</option>
              {meta?.provinces.map((p) => <option key={p} value={p}>{p}</option>)}
            </Select>
          </Field>
        </div>

        {isLoading && <LoadingBlock />}
        {data && (
          <>
            <div className="divide-y divide-slate-100">
              {data.data.map((row) => (
                <div key={row.trainee.id} className="flex items-center justify-between gap-4 px-5 py-3 text-sm">
                  <div>
                    <p className="font-medium text-slate-800">{row.trainee.full_name}</p>
                    <p className="text-xs text-slate-500">{row.trainee.city_municipality}, {row.trainee.province}</p>
                  </div>
                  <div className="flex flex-wrap items-center justify-end gap-1.5">
                    {row.gaps.map((g) => <Badge key={g} tone="amber">{g}</Badge>)}
                    {row.gaps.length === 0 && <Badge tone="green">No gaps</Badge>}
                    <Link to={`/officer/trainees/${row.trainee.id}`} className="ml-2">
                      <Button size="sm" variant="secondary">View</Button>
                    </Link>
                  </div>
                </div>
              ))}
              {data.data.length === 0 && <p className="px-5 py-10 text-center text-sm text-slate-400">No trainees match this filter.</p>}
            </div>
            <Pagination page={data.meta.current_page} lastPage={data.meta.last_page} onChange={setPage} />
          </>
        )}
      </Card>
    </div>
  )
}
