import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { adminApi } from '../../api/admin'
import { extractErrorMessage } from '../../api/client'
import { Card, ErrorBlock, Input, LoadingBlock, Pagination } from '../../components/ui'

export default function AdminAuditLogs() {
  const [page, setPage] = useState(1)
  const [action, setAction] = useState('')

  const { data, isLoading, error } = useQuery({
    queryKey: ['admin', 'audit-logs', { page, action }],
    queryFn: () => adminApi.auditLogs({ page, action: action || undefined }),
  })

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-semibold text-slate-900">Audit Logs</h1>
        <p className="text-sm text-slate-500">System-wide activity and user action history.</p>
      </div>

      <Card>
        <div className="border-b border-slate-100 px-5 py-3">
          <Input
            placeholder="Filter by action (e.g. trainee.created)…"
            value={action}
            onChange={(e) => { setAction(e.target.value); setPage(1) }}
            className="max-w-sm"
          />
        </div>

        {isLoading && <LoadingBlock />}
        {error && <div className="p-5"><ErrorBlock message={extractErrorMessage(error)} /></div>}

        {data && (
          <>
            <div className="divide-y divide-slate-100">
              {data.data.map((log) => (
                <div key={log.id} className="flex items-start justify-between gap-4 px-5 py-3 text-sm">
                  <div>
                    <p className="text-slate-800">{log.description ?? log.action}</p>
                    <p className="mt-0.5 text-xs text-slate-400">
                      {log.user?.name ?? 'System'} &middot; {log.action}
                    </p>
                  </div>
                  <span className="shrink-0 text-xs text-slate-400">{new Date(log.created_at).toLocaleString()}</span>
                </div>
              ))}
              {data.data.length === 0 && <p className="px-5 py-10 text-center text-sm text-slate-400">No activity recorded yet.</p>}
            </div>
            <Pagination page={data.meta.current_page} lastPage={data.meta.last_page} onChange={setPage} />
          </>
        )}
      </Card>
    </div>
  )
}
