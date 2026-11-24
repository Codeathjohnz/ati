import { useQuery } from '@tanstack/react-query'
import { traineeSelfApi } from '../../api/traineeSelf'
import { extractErrorMessage } from '../../api/client'
import { Badge, Card, CardHeader, ChainLevelStatusBadge, ComplianceBadge, ErrorBlock, LoadingBlock } from '../../components/ui'

export default function MyActivityHistory() {
  const { data, isLoading, error } = useQuery({ queryKey: ['trainee', 'me', 'activity-history'], queryFn: traineeSelfApi.activityHistory })

  if (isLoading) return <LoadingBlock />
  if (error || !data) return <ErrorBlock message={extractErrorMessage(error)} />

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-semibold text-slate-900">My Complete Activity Record</h1>
        <p className="text-sm text-slate-500">Full view of your training participation and compliance history.</p>
      </div>

      <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <Card>
          <CardHeader title="Document Compliance" action={<ComplianceBadge status={data.document_compliance.overall_status} />} />
          <div className="divide-y divide-slate-100">
            {data.document_compliance.per_training.map((e) => (
              <div key={e.training_history_id} className="flex items-center justify-between px-5 py-3 text-sm">
                <span className="text-slate-700">{e.training_program_name}</span>
                <ComplianceBadge status={e.overall_status} />
              </div>
            ))}
          </div>
        </Card>

        <Card>
          <CardHeader title="Competency Gap Analysis" />
          <div className="divide-y divide-slate-100">
            {data.competency_gaps.chain_levels.map((l) => (
              <div key={l.chain_level} className="flex items-center justify-between px-5 py-3 text-sm">
                <span className="text-slate-700">{l.chain_level}</span>
                <ChainLevelStatusBadge completed={l.completed} />
              </div>
            ))}
          </div>
        </Card>
      </div>

      <Card>
        <CardHeader title="Activity Log" />
        <div className="divide-y divide-slate-100">
          {data.activity_log.map((log) => (
            <div key={log.id} className="flex items-start justify-between gap-4 px-5 py-3 text-sm">
              <p className="text-slate-800">{log.description ?? log.action}</p>
              <span className="shrink-0 text-xs text-slate-400">{new Date(log.created_at).toLocaleString()}</span>
            </div>
          ))}
          {data.activity_log.length === 0 && <p className="px-5 py-8 text-center text-sm text-slate-400">No activity recorded yet.</p>}
        </div>
      </Card>

      <Card>
        <CardHeader title="Training Confirmations" />
        <div className="divide-y divide-slate-100">
          {data.confirmations.map((c) => (
            <div key={c.id} className="flex items-center justify-between px-5 py-3 text-sm">
              <span className="text-slate-800">{c.training_program?.name}</span>
              <Badge tone={c.status === 'confirmed' ? 'green' : c.status === 'declined' ? 'red' : 'amber'}>{c.status}</Badge>
            </div>
          ))}
        </div>
      </Card>
    </div>
  )
}
