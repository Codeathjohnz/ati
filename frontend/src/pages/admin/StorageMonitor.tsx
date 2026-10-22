import { useQuery } from '@tanstack/react-query'
import { adminApi, type StorageConsumer } from '../../api/admin'
import { extractErrorMessage } from '../../api/client'
import { Badge, Button, Card, CardHeader, ErrorBlock, LoadingBlock } from '../../components/ui'

function formatBytes(bytes: number | null): string {
  if (bytes === null || Number.isNaN(bytes)) return '—'
  if (bytes === 0) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB', 'TB']
  const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1)
  const value = bytes / 1024 ** exponent
  return `${value.toFixed(exponent === 0 ? 0 : 2)} ${units[exponent]}`
}

export default function StorageMonitor() {
  const { data, isLoading, error, refetch, isFetching } = useQuery({
    queryKey: ['admin', 'storage'],
    queryFn: adminApi.storageUsage,
    staleTime: 0, // always a fresh read, never a cached/stale figure
  })

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-xl font-semibold text-slate-900">Storage Monitor</h1>
          <p className="text-sm text-slate-500">
            Live filesystem usage for the server this backend runs on — read directly off disk on every load, not cached.
          </p>
        </div>
        <Button variant="secondary" onClick={() => refetch()} disabled={isFetching}>
          {isFetching ? 'Refreshing…' : 'Refresh'}
        </Button>
      </div>

      {isLoading && <LoadingBlock label="Reading disk usage…" />}
      {error && <ErrorBlock message={extractErrorMessage(error)} />}

      {data && (
        <>
          <Card>
            <CardHeader
              title="Filesystem Capacity"
              subtitle={`Table A-1 — measured at ${data.filesystem.path}`}
              action={<Badge tone="slate">{new Date(data.generated_at).toLocaleString()}</Badge>}
            />
            <div className="grid grid-cols-2 gap-4 p-5 md:grid-cols-4">
              <Stat label="Total" value={formatBytes(data.filesystem.total_bytes)} />
              <Stat label="Used" value={formatBytes(data.filesystem.used_bytes)} />
              <Stat label="Free" value={formatBytes(data.filesystem.free_bytes)} />
              <Stat
                label="Used %"
                value={data.filesystem.used_percent !== null ? `${data.filesystem.used_percent}%` : '—'}
              />
            </div>
            {data.filesystem.used_percent !== null && (
              <div className="px-5 pb-5">
                <div className="h-2.5 overflow-hidden rounded-full bg-slate-100">
                  <div
                    className={`h-full rounded-full ${data.filesystem.used_percent >= 90 ? 'bg-red-500' : data.filesystem.used_percent >= 75 ? 'bg-amber-500' : 'bg-brand-600'}`}
                    style={{ width: `${Math.min(data.filesystem.used_percent, 100)}%` }}
                  />
                </div>
              </div>
            )}
            <div className="border-t border-slate-100 px-5 py-3 text-xs text-slate-400">
              Cross-check: run <code className="rounded bg-slate-100 px-1.5 py-0.5 text-slate-600">df -h</code> in the same
              container (Dokploy → backend service → Terminal) — Total/Used/Free here should match that output for this
              mount within rounding.
            </div>
          </Card>

          <Card>
            <CardHeader
              title="Largest Storage Consumers"
              subtitle={`Table A-2 — breakdown of the application directory (${formatBytes(data.app_total_bytes)} total)`}
            />
            <div className="divide-y divide-slate-100">
              {data.top_consumers.map((c: StorageConsumer) => (
                <div key={c.path} className="px-5 py-3">
                  <div className="mb-1.5 flex items-center justify-between text-sm">
                    <div>
                      <span className="font-medium text-slate-800">{c.label}</span>
                      <code className="ml-2 text-xs text-slate-400">{c.path}</code>
                    </div>
                    <span className="font-semibold text-slate-900">
                      {formatBytes(c.size_bytes)} <span className="font-normal text-slate-400">({c.percent_of_app}%)</span>
                    </span>
                  </div>
                  <div className="h-1.5 overflow-hidden rounded-full bg-slate-100">
                    <div className="h-full rounded-full bg-brand-600" style={{ width: `${Math.min(c.percent_of_app, 100)}%` }} />
                  </div>
                </div>
              ))}
              {data.other_bytes > 0 && (
                <div className="flex items-center justify-between px-5 py-3 text-sm text-slate-500">
                  <span>Other (.git, logs not tracked above, etc.)</span>
                  <span>{formatBytes(data.other_bytes)}</span>
                </div>
              )}
            </div>
          </Card>
        </>
      )}
    </div>
  )
}

function Stat({ label, value }: { label: string; value: string }) {
  return (
    <div>
      <p className="text-xs font-medium uppercase tracking-wide text-slate-400">{label}</p>
      <p className="mt-1 text-xl font-semibold text-slate-900">{value}</p>
    </div>
  )
}
