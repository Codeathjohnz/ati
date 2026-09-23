import { useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { documentsApi } from '../../api/documents'
import { extractErrorMessage } from '../../api/client'
import { Card, CardHeader, ComplianceBadge, ErrorBlock, LoadingBlock, ReviewStatusBadge, Button } from '../../components/ui'
import type { DocumentStatusEntry } from '../../types'

const TESTABLE_CODES = new Set(['pre_test', 'post_test'])

export default function MyDocuments() {
  const { data, isLoading, error } = useQuery({ queryKey: ['trainee', 'me', 'documents'], queryFn: documentsApi.myCompliance })

  if (isLoading) return <LoadingBlock />
  if (error || !data) return <ErrorBlock message={extractErrorMessage(error)} />

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-semibold text-slate-900">My Document Status</h1>
          <p className="text-sm text-slate-500">
            Upload each required document — your Project Officer reviews it and approves it, or sends it back if
            something's missing.
          </p>
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
              <DocumentRow key={doc.required_document_id} trainingHistoryId={entry.training_history_id} doc={doc} />
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

function DocumentRow({ trainingHistoryId, doc }: { trainingHistoryId: number; doc: DocumentStatusEntry }) {
  const queryClient = useQueryClient()
  const fileInputRef = useRef<HTMLInputElement>(null)
  const [uploadError, setUploadError] = useState<string | null>(null)
  const isTestable = TESTABLE_CODES.has(doc.code)

  const uploadMutation = useMutation({
    mutationFn: (file: File) => documentsApi.myUpload(trainingHistoryId, doc.required_document_id, file),
    onSuccess: () => {
      setUploadError(null)
      queryClient.invalidateQueries({ queryKey: ['trainee', 'me', 'documents'] })
    },
    onError: (err) => setUploadError(extractErrorMessage(err, 'Could not upload this file.')),
  })

  function handleFileChange(e: React.ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0]
    if (file) uploadMutation.mutate(file)
    e.target.value = ''
  }

  return (
    <div className="px-5 py-3 text-sm">
      <div className="flex items-center justify-between gap-3">
        <div>
          <span className="text-slate-700">{doc.name}</span>
          {doc.remarks && <p className="mt-0.5 text-xs text-slate-400">{doc.remarks}</p>}
        </div>
        <div className="flex items-center gap-2">
          <ReviewStatusBadge submitted={doc.submitted} status={doc.review_status} />
        </div>
      </div>
      <div className="mt-2 flex flex-wrap items-center gap-2">
        {isTestable && (
          <Link to={`/trainee/training-history/${trainingHistoryId}/tests/${doc.code}`}>
            <Button size="sm" variant="secondary">
              Take Test
            </Button>
          </Link>
        )}
        {doc.has_attachment && (
          <Button size="sm" variant="ghost" onClick={() => documentsApi.download(trainingHistoryId, doc.required_document_id, 'me')}>
            View My Upload
          </Button>
        )}
        <label className="cursor-pointer">
          <input ref={fileInputRef} type="file" accept=".jpg,.jpeg,.png,.pdf" className="hidden" onChange={handleFileChange} />
          <span className="inline-flex items-center rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
            {uploadMutation.isPending ? 'Uploading…' : isTestable ? 'Upload a photo instead' : doc.has_attachment ? 'Replace upload' : 'Upload'}
          </span>
        </label>
      </div>
      {uploadError && <p className="mt-1.5 text-xs text-red-600">{uploadError}</p>}
    </div>
  )
}
