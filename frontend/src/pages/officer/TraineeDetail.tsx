import { useState, type ReactNode } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Link, useNavigate, useParams } from 'react-router-dom'
import clsx from 'clsx'
import { traineesApi } from '../../api/trainees'
import { trainingHistoryApi, type TrainingHistoryPayload } from '../../api/trainingHistory'
import { trainingProgramsApi } from '../../api/trainingPrograms'
import { documentsApi } from '../../api/documents'
import { competencyGapsApi } from '../../api/competencyGaps'
import { extractErrorMessage } from '../../api/client'
import type { TraineeProfile } from '../../types'
import {
  Badge,
  Button,
  Card,
  CardHeader,
  ChainLevelStatusBadge,
  ComplianceBadge,
  ErrorBlock,
  Field,
  Input,
  LoadingBlock,
  Modal,
  PasswordInput,
  Select,
  Textarea,
  YesNoBadge,
} from '../../components/ui'

const TABS = ['Overview', 'Training History', 'Documents', 'Competency Gaps', 'Activity History'] as const
type Tab = (typeof TABS)[number]

export default function TraineeDetail() {
  const { id } = useParams()
  const traineeId = Number(id)
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const [tab, setTab] = useState<Tab>('Overview')

  const { data: trainee, isLoading, error } = useQuery({
    queryKey: ['trainees', traineeId],
    queryFn: () => traineesApi.get(traineeId),
  })

  const deleteMutation = useMutation({
    mutationFn: () => traineesApi.remove(traineeId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['trainees'] })
      navigate('/officer/trainees')
    },
  })

  if (isLoading) return <LoadingBlock />
  if (error || !trainee) return <ErrorBlock message={extractErrorMessage(error, 'Trainee not found.')} />

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <Link to="/officer/trainees" className="text-xs font-medium text-brand-700 hover:underline">← Back to Trainees</Link>
          <h1 className="mt-1 text-xl font-semibold text-slate-900">{trainee.full_name}</h1>
          <p className="text-sm text-slate-500">
            {trainee.sex}{trainee.age ? `, ${trainee.age} years old` : ''} &middot; {trainee.city_municipality}, {trainee.province}
          </p>
        </div>
        <div className="flex gap-2">
          <Link to={`/officer/trainees/${traineeId}/edit`}>
            <Button variant="secondary">Edit Profile</Button>
          </Link>
          <Button
            variant="danger"
            onClick={() => {
              if (confirm(`Delete ${trainee.full_name}? This removes all their training and document records.`)) {
                deleteMutation.mutate()
              }
            }}
          >
            Delete
          </Button>
        </div>
      </div>

      <div className="flex gap-1 overflow-x-auto border-b border-slate-200">
        {TABS.map((t) => (
          <button
            key={t}
            onClick={() => setTab(t)}
            className={clsx(
              'whitespace-nowrap border-b-2 px-4 py-2.5 text-sm font-medium',
              tab === t ? 'border-brand-600 text-brand-700' : 'border-transparent text-slate-500 hover:text-slate-800',
            )}
          >
            {t}
          </button>
        ))}
      </div>

      {tab === 'Overview' && <OverviewTab trainee={trainee} />}
      {tab === 'Training History' && <TrainingHistoryTab traineeId={traineeId} />}
      {tab === 'Documents' && <DocumentsTab traineeId={traineeId} />}
      {tab === 'Competency Gaps' && <CompetencyGapsTab traineeId={traineeId} />}
      {tab === 'Activity History' && <ActivityHistoryTab traineeId={traineeId} />}
    </div>
  )
}

function OverviewTab({ trainee }: { trainee: TraineeProfile }) {
  const queryClient = useQueryClient()
  const [modalOpen, setModalOpen] = useState(false)
  const [form, setForm] = useState({ username: '', email: '', password: '' })
  const [error, setError] = useState<string | null>(null)

  const linkMutation = useMutation({
    mutationFn: () => traineesApi.linkAccount(trainee.id, form),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['trainees', trainee.id] })
      setModalOpen(false)
      setForm({ username: '', email: '', password: '' })
    },
    onError: (err) => setError(extractErrorMessage(err)),
  })

  return (
    <Card>
      <CardHeader title="Comprehensive Trainee Profile" />
      <dl className="grid grid-cols-1 gap-x-6 gap-y-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
        <Detail label="Full Name" value={trainee.full_name} />
        <Detail label="Sex" value={trainee.sex} />
        <Detail label="Birthdate" value={trainee.birthdate ?? '—'} />
        <Detail label="Age" value={trainee.age ?? '—'} />
        <Detail label="Contact Number" value={trainee.contact_number ?? '—'} />
        <Detail label="Email" value={trainee.email ?? '—'} />
        <Detail label="Address" value={[trainee.address_line, trainee.barangay].filter(Boolean).join(', ') || '—'} />
        <Detail label="City/Municipality" value={trainee.city_municipality} />
        <Detail label="Province" value={trainee.province} />
        <Detail label="LGU Income Class" value={trainee.lgu_income_class ?? '—'} />
        <Detail label="Educational Level" value={trainee.educational_level ?? '—'} />
        <Detail label="Organization/Affiliation" value={trainee.organization_affiliation ?? '—'} />
        <Detail label="Occupation" value={trainee.occupation ?? '—'} />
        <Detail
          label="Login Account"
          value={
            trainee.has_login ? (
              <Badge tone="green">Linked</Badge>
            ) : (
              <div className="flex items-center gap-2">
                <Badge tone="slate">None</Badge>
                <Button size="sm" variant="secondary" onClick={() => { setError(null); setModalOpen(true) }}>
                  Set Up Portal Access
                </Button>
              </div>
            )
          }
        />
      </dl>

      <Modal open={modalOpen} onClose={() => setModalOpen(false)} title="Set Up Trainee Portal Access">
        <div className="space-y-4">
          {error && <ErrorBlock message={error} />}
          <Field label="Username">
            <Input value={form.username} onChange={(e) => setForm({ ...form, username: e.target.value })} required />
          </Field>
          <Field label="Email (optional)">
            <Input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
          </Field>
          <Field label="Password">
            <PasswordInput value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} required />
          </Field>
          <Button
            className="w-full"
            disabled={!form.username || !form.password || linkMutation.isPending}
            onClick={() => linkMutation.mutate()}
          >
            Create Login Account
          </Button>
        </div>
      </Modal>
    </Card>
  )
}

function Detail({ label, value }: { label: string; value: ReactNode }) {
  return (
    <div>
      <dt className="text-xs font-medium uppercase tracking-wide text-slate-400">{label}</dt>
      <dd className="mt-0.5 text-sm text-slate-800">{value}</dd>
    </div>
  )
}

function TrainingHistoryTab({ traineeId }: { traineeId: number }) {
  const queryClient = useQueryClient()
  const [modalOpen, setModalOpen] = useState(false)
  const [form, setForm] = useState<TrainingHistoryPayload>({ training_program_id: 0, date_attended: '', remarks: '' })
  const [error, setError] = useState<string | null>(null)

  const { data: history, isLoading } = useQuery({
    queryKey: ['trainees', traineeId, 'training-history'],
    queryFn: () => trainingHistoryApi.list(traineeId),
  })
  const { data: programs } = useQuery({ queryKey: ['training-programs', 'all'], queryFn: () => trainingProgramsApi.list({ per_page: 100 }) })

  const createMutation = useMutation({
    mutationFn: () => trainingHistoryApi.create(traineeId, form),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['trainees', traineeId] })
      setModalOpen(false)
      setForm({ training_program_id: 0, date_attended: '', remarks: '' })
    },
    onError: (err) => setError(extractErrorMessage(err)),
  })

  const deleteMutation = useMutation({
    mutationFn: (historyId: number) => trainingHistoryApi.remove(historyId),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['trainees', traineeId] }),
  })

  return (
    <Card>
      <CardHeader
        title="Training History Records"
        subtitle="Programs attended, chain level, and date."
        action={<Button size="sm" onClick={() => { setError(null); setModalOpen(true) }}>+ Record Attendance</Button>}
      />
      {isLoading && <LoadingBlock />}
      <div className="divide-y divide-slate-100">
        {history?.map((h) => (
          <div key={h.id} className="flex items-center justify-between px-5 py-3 text-sm">
            <div>
              <p className="font-medium text-slate-800">{h.training_program?.name}</p>
              <p className="text-xs text-slate-500">{h.date_attended} &middot; {h.chain_level}</p>
              {h.remarks && <p className="mt-0.5 text-xs text-slate-400">{h.remarks}</p>}
            </div>
            <Button size="sm" variant="danger" onClick={() => deleteMutation.mutate(h.id)}>Remove</Button>
          </div>
        ))}
        {history?.length === 0 && <p className="px-5 py-8 text-center text-sm text-slate-400">No training history recorded yet.</p>}
      </div>

      <Modal open={modalOpen} onClose={() => setModalOpen(false)} title="Record Training Attendance">
        <div className="space-y-4">
          {error && <ErrorBlock message={error} />}
          <Field label="Training Program">
            <Select
              value={form.training_program_id || ''}
              onChange={(e) => setForm({ ...form, training_program_id: Number(e.target.value) })}
            >
              <option value="">Select a training program…</option>
              {programs?.data.map((p) => (
                <option key={p.id} value={p.id}>{p.name} ({p.chain_level})</option>
              ))}
            </Select>
          </Field>
          <Field label="Date Attended">
            <Input type="date" value={form.date_attended} onChange={(e) => setForm({ ...form, date_attended: e.target.value })} />
          </Field>
          <Field label="Remarks (optional)">
            <Textarea rows={2} value={form.remarks} onChange={(e) => setForm({ ...form, remarks: e.target.value })} />
          </Field>
          <Button
            className="w-full"
            disabled={!form.training_program_id || !form.date_attended || createMutation.isPending}
            onClick={() => createMutation.mutate()}
          >
            Save Attendance Record
          </Button>
        </div>
      </Modal>
    </Card>
  )
}

function DocumentsTab({ traineeId }: { traineeId: number }) {
  const queryClient = useQueryClient()
  const { data, isLoading } = useQuery({
    queryKey: ['trainees', traineeId, 'documents'],
    queryFn: () => documentsApi.forTrainee(traineeId),
  })

  const toggleMutation = useMutation({
    mutationFn: ({ historyId, docId, submitted }: { historyId: number; docId: number; submitted: boolean }) =>
      documentsApi.update(historyId, docId, submitted),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['trainees', traineeId] }),
  })

  if (isLoading) return <LoadingBlock />

  return (
    <div className="space-y-4">
      <Card className="px-5 py-4">
        <div className="flex items-center justify-between">
          <p className="text-sm font-medium text-slate-700">Overall Compliance Status</p>
          <ComplianceBadge status={data?.overall_status ?? 'No Records'} />
        </div>
      </Card>

      {data?.per_training.map((entry) => (
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
                <div className="flex items-center gap-3">
                  <YesNoBadge value={doc.submitted} />
                  <Button
                    size="sm"
                    variant="secondary"
                    onClick={() =>
                      toggleMutation.mutate({ historyId: entry.training_history_id, docId: doc.required_document_id, submitted: !doc.submitted })
                    }
                  >
                    Mark {doc.submitted ? 'Not Submitted' : 'Submitted'}
                  </Button>
                </div>
              </div>
            ))}
          </div>
        </Card>
      ))}

      {(!data || data.per_training.length === 0) && (
        <Card className="px-5 py-10 text-center text-sm text-slate-400">
          No training history yet — document tracking begins once a training is recorded.
        </Card>
      )}
    </div>
  )
}

function CompetencyGapsTab({ traineeId }: { traineeId: number }) {
  const { data, isLoading } = useQuery({
    queryKey: ['trainees', traineeId, 'competency-gaps'],
    queryFn: () => competencyGapsApi.forTrainee(traineeId),
  })

  if (isLoading) return <LoadingBlock />

  return (
    <Card>
      <CardHeader
        title="Competency Gap Analysis"
        subtitle="Chain levels required vs. completed, based on recorded training history."
        action={data && <Badge tone={data.has_gaps ? 'amber' : 'green'}>{data.gap_count} gap{data.gap_count === 1 ? '' : 's'}</Badge>}
      />
      <div className="divide-y divide-slate-100">
        {data?.chain_levels.map((level) => (
          <div key={level.chain_level} className="flex items-center justify-between px-5 py-3 text-sm">
            <span className="font-medium text-slate-800">{level.chain_level}</span>
            <ChainLevelStatusBadge completed={level.completed} />
          </div>
        ))}
      </div>
    </Card>
  )
}

function ActivityHistoryTab({ traineeId }: { traineeId: number }) {
  const { data, isLoading } = useQuery({
    queryKey: ['trainees', traineeId, 'activity-history'],
    queryFn: () => traineesApi.activityHistory(traineeId),
  })
  const [printing, setPrinting] = useState(false)

  async function handlePrint() {
    setPrinting(true)
    try {
      const blob = await traineesApi.activityHistoryPdf(traineeId)
      const url = window.URL.createObjectURL(blob)
      window.open(url, '_blank')
    } finally {
      setPrinting(false)
    }
  }

  if (isLoading) return <LoadingBlock />

  return (
    <div className="space-y-4">
      <div className="flex justify-end">
        <Button variant="secondary" onClick={handlePrint} disabled={printing}>
          {printing ? 'Preparing…' : '🖨 View / Print PDF'}
        </Button>
      </div>

      <Card>
        <CardHeader title="Recent Activity Log" />
        <div className="divide-y divide-slate-100">
          {data?.activity_log.map((log) => (
            <div key={log.id} className="flex items-start justify-between gap-4 px-5 py-3 text-sm">
              <div>
                <p className="text-slate-800">{log.description ?? log.action}</p>
                <p className="mt-0.5 text-xs text-slate-400">{log.user?.name ?? 'System'}</p>
              </div>
              <span className="shrink-0 text-xs text-slate-400">{new Date(log.created_at).toLocaleString()}</span>
            </div>
          ))}
          {data?.activity_log.length === 0 && <p className="px-5 py-8 text-center text-sm text-slate-400">No activity recorded yet.</p>}
        </div>
      </Card>

      <Card>
        <CardHeader title="Upcoming Training Confirmations" />
        <div className="divide-y divide-slate-100">
          {data?.confirmations.map((c) => (
            <div key={c.id} className="flex items-center justify-between px-5 py-3 text-sm">
              <span className="text-slate-800">{c.training_program?.name}</span>
              <Badge tone={c.status === 'confirmed' ? 'green' : c.status === 'declined' ? 'red' : 'amber'}>{c.status}</Badge>
            </div>
          ))}
          {data?.confirmations.length === 0 && <p className="px-5 py-8 text-center text-sm text-slate-400">No upcoming confirmations.</p>}
        </div>
      </Card>
    </div>
  )
}
