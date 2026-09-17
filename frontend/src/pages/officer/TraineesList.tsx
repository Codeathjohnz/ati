import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { traineesApi, type TraineeFilters } from '../../api/trainees'
import { metaApi } from '../../api/meta'
import { extractErrorMessage } from '../../api/client'
import { Badge, Button, Card, ErrorBlock, Field, Input, LoadingBlock, Pagination, Select } from '../../components/ui'

const emptyFilters: TraineeFilters = {}

export default function TraineesList() {
  const [filters, setFilters] = useState<TraineeFilters>(emptyFilters)
  const [page, setPage] = useState(1)
  const [showFilters, setShowFilters] = useState(false)

  const { data: meta } = useQuery({ queryKey: ['meta'], queryFn: metaApi.get })

  const { data, isLoading, error } = useQuery({
    queryKey: ['trainees', { ...filters, page }],
    queryFn: () => traineesApi.list({ ...filters, page }),
  })

  function updateFilter<K extends keyof TraineeFilters>(key: K, value: TraineeFilters[K]) {
    setFilters((f) => ({ ...f, [key]: value }))
    setPage(1)
  }

  function clearFilters() {
    setFilters(emptyFilters)
    setPage(1)
  }

  const activeFilterCount = Object.values(filters).filter((v) => v !== undefined && v !== '').length

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-xl font-semibold text-slate-900">Trainees</h1>
          <p className="text-sm text-slate-500">Searchable database of all ATI-Caraga trainees.</p>
        </div>
        <div className="flex gap-2">
          <Button variant="secondary" onClick={() => setShowFilters((s) => !s)}>
            Filters {activeFilterCount > 0 && <Badge tone="green">{activeFilterCount}</Badge>}
          </Button>
          <Link to="/officer/trainees/new">
            <Button>+ Register Trainee</Button>
          </Link>
        </div>
      </div>

      <Card>
        <div className="border-b border-slate-100 p-4">
          <Input
            placeholder="Search by name, email, or contact number…"
            value={filters.q ?? ''}
            onChange={(e) => updateFilter('q', e.target.value)}
          />
        </div>

        {showFilters && (
          <div className="grid grid-cols-1 gap-3 border-b border-slate-100 p-4 sm:grid-cols-2 lg:grid-cols-4">
            <Field label="Sex">
              <Select value={filters.sex ?? ''} onChange={(e) => updateFilter('sex', e.target.value)}>
                <option value="">Any</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </Select>
            </Field>
            <Field label="Province">
              <Select value={filters.province ?? ''} onChange={(e) => updateFilter('province', e.target.value)}>
                <option value="">Any</option>
                {meta?.provinces.map((p) => (
                  <option key={p} value={p}>{p}</option>
                ))}
              </Select>
            </Field>
            <Field label="City/Municipality">
              <Input value={filters.city_municipality ?? ''} onChange={(e) => updateFilter('city_municipality', e.target.value)} />
            </Field>
            <Field label="LGU Income Class">
              <Select value={filters.lgu_income_class ?? ''} onChange={(e) => updateFilter('lgu_income_class', e.target.value)}>
                <option value="">Any</option>
                {meta?.income_classes.map((c) => (
                  <option key={c} value={c}>{c}</option>
                ))}
              </Select>
            </Field>
            <Field label="Educational Level">
              <Select value={filters.educational_level ?? ''} onChange={(e) => updateFilter('educational_level', e.target.value)}>
                <option value="">Any</option>
                {meta?.educational_levels.map((l) => (
                  <option key={l} value={l}>{l}</option>
                ))}
              </Select>
            </Field>
            <Field label="Organization/Affiliation">
              <Input value={filters.organization_affiliation ?? ''} onChange={(e) => updateFilter('organization_affiliation', e.target.value)} />
            </Field>
            <Field label="Year Last Attended">
              <Input type="number" placeholder="e.g. 2025" value={filters.year_last_attended ?? ''} onChange={(e) => updateFilter('year_last_attended', e.target.value)} />
            </Field>
            <Field label="Age Range">
              <div className="flex gap-2">
                <Input type="number" placeholder="Min" value={filters.age_min ?? ''} onChange={(e) => updateFilter('age_min', e.target.value)} />
                <Input type="number" placeholder="Max" value={filters.age_max ?? ''} onChange={(e) => updateFilter('age_max', e.target.value)} />
              </div>
            </Field>
            <div className="flex items-end sm:col-span-2 lg:col-span-4">
              <Button variant="ghost" onClick={clearFilters}>Clear all filters</Button>
            </div>
          </div>
        )}

        {isLoading && <LoadingBlock />}
        {error && <div className="p-5"><ErrorBlock message={extractErrorMessage(error)} /></div>}

        {data && (
          <>
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm">
                <thead className="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                  <tr>
                    <th className="px-5 py-2.5">Name</th>
                    <th className="px-5 py-2.5">Sex / Age</th>
                    <th className="px-5 py-2.5">Location</th>
                    <th className="px-5 py-2.5">Organization</th>
                    <th className="px-5 py-2.5">Trainings</th>
                    <th className="px-5 py-2.5" />
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {data.data.map((t) => (
                    <tr key={t.id}>
                      <td className="px-5 py-3 font-medium text-slate-800">{t.full_name}</td>
                      <td className="px-5 py-3 text-slate-600">{t.sex}{t.age ? `, ${t.age}` : ''}</td>
                      <td className="px-5 py-3 text-slate-600">{t.city_municipality}, {t.province}</td>
                      <td className="px-5 py-3 text-slate-600">{t.organization_affiliation ?? '—'}</td>
                      <td className="px-5 py-3 text-slate-600">{t.training_count ?? 0}</td>
                      <td className="px-5 py-3 text-right">
                        <Link to={`/officer/trainees/${t.id}`}>
                          <Button size="sm" variant="secondary">View</Button>
                        </Link>
                      </td>
                    </tr>
                  ))}
                  {data.data.length === 0 && (
                    <tr>
                      <td colSpan={6} className="px-5 py-10 text-center text-sm text-slate-400">No trainees match these filters.</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
            <Pagination page={data.meta.current_page} lastPage={data.meta.last_page} onChange={setPage} />
          </>
        )}
      </Card>
    </div>
  )
}
