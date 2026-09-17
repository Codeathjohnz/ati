import { useQuery } from '@tanstack/react-query'
import { traineeSelfApi } from '../../api/traineeSelf'
import { extractErrorMessage } from '../../api/client'
import { Badge, Card, CardHeader, ErrorBlock, LoadingBlock } from '../../components/ui'

export default function MyTrainingHistory() {
  const { data, isLoading, error } = useQuery({
    queryKey: ['trainee', 'me', 'activity-history'],
    queryFn: traineeSelfApi.activityHistory,
  })

  if (isLoading) return <LoadingBlock />
  if (error || !data) return <ErrorBlock message={extractErrorMessage(error)} />

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-semibold text-slate-900">My Training History</h1>
        <p className="text-sm text-slate-500">Every training program you've attended, and the chain level it covered.</p>
      </div>

      <Card>
        <CardHeader title="Training Records" />
        <div className="divide-y divide-slate-100">
          {data.training_history.map((h) => (
            <div key={h.id} className="flex items-center justify-between px-5 py-3 text-sm">
              <div>
                <p className="font-medium text-slate-800">{h.training_program?.name}</p>
                <p className="text-xs text-slate-500">{h.date_attended}</p>
              </div>
              <Badge tone="blue">{h.chain_level}</Badge>
            </div>
          ))}
          {data.training_history.length === 0 && (
            <p className="px-5 py-10 text-center text-sm text-slate-400">No training history on record yet.</p>
          )}
        </div>
      </Card>
    </div>
  )
}
