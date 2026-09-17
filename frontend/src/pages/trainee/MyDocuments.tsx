import { useQuery } from '@tanstack/react-query'
import { documentsApi } from '../../api/documents'
import { extractErrorMessage } from '../../api/client'
import { Card, CardHeader, ComplianceBadge, ErrorBlock, LoadingBlock, YesNoBadge } from '../../components/ui'

export default function MyDocuments() {
  const { data, isLoading, error } = useQuery({ queryKey: ['trainee', 'me', 'documents'], queryFn: documentsApi.myCompliance })

  if (isLoading) return <LoadingBlock />
  if (error || !data) return <ErrorBlock message={extractErrorMessage(error)} />

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-semibold text-slate-900">My Document Status</h1>
          <p className="text-sm text-slate-500">Submission status of your required post-training documents.</p>
        </div>
        <ComplianceBadge status={data.overall_status} />
      </div>

      {data.per_training.map((entry) => (
        <Card key={entry.training_history_id}>
          <CardHeader
            title={entry.training_program_name ?? 'Training'}
            subtitle={entry.date_attended?.slice(0, 10)}
            action={<ComplianceBadge status={entry.overall_status} />}
          />
          <div className="divide-y divide-slate-100">
            {entry.documents.map((doc) => (
              <div key={doc.required_document_id} className="flex items-center justify-between px-5 py-3 text-sm">
                <span className="text-slate-700">{doc.name}</span>
                <YesNoBadge value={doc.submitted} />
              </div>
            ))}
          </div>
        </Card>
      ))}

      {data.per_training.length === 0 && (
        <Card className="px-5 py-10 text-center text-sm text-slate-400">
          Document tracking will appear here once your first training attendance is recorded.
        </Card>
      )}
    </div>
  )
}
