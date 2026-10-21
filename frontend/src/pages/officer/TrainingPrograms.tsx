import { useEffect, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { trainingProgramsApi, type TrainingProgramPayload } from '../../api/trainingPrograms'
import { trainingProgramTestsApi } from '../../api/tests'
import { metaApi } from '../../api/meta'
import { extractErrorMessage } from '../../api/client'
import type { TestQuestion, TestType, TrainingProgram } from '../../types'
import { Badge, Button, Card, ErrorBlock, Field, Input, LoadingBlock, Modal, Pagination, Select, Textarea } from '../../components/ui'

const emptyForm: TrainingProgramPayload = { name: '', description: '', chain_level: 'Production', venue: '', start_date: '', end_date: '', status: 'upcoming' }

const statusTone: Record<string, 'slate' | 'green' | 'amber' | 'blue'> = {
  upcoming: 'blue',
  ongoing: 'amber',
  completed: 'green',
  cancelled: 'slate',
}

export default function TrainingPrograms() {
  const queryClient = useQueryClient()
  const [page, setPage] = useState(1)
  const [modalOpen, setModalOpen] = useState(false)
  const [editing, setEditing] = useState<TrainingProgram | null>(null)
  const [form, setForm] = useState<TrainingProgramPayload>(emptyForm)
  const [error, setError] = useState<string | null>(null)
  const [testsProgram, setTestsProgram] = useState<TrainingProgram | null>(null)

  const { data: meta } = useQuery({ queryKey: ['meta'], queryFn: metaApi.get })
  const { data, isLoading } = useQuery({
    queryKey: ['training-programs', page],
    queryFn: () => trainingProgramsApi.list({ page }),
  })

  const createMutation = useMutation({
    mutationFn: trainingProgramsApi.create,
    onSuccess: () => { queryClient.invalidateQueries({ queryKey: ['training-programs'] }); close() },
    onError: (err) => setError(extractErrorMessage(err)),
  })
  const updateMutation = useMutation({
    mutationFn: (payload: TrainingProgramPayload) => trainingProgramsApi.update(editing!.id, payload),
    onSuccess: () => { queryClient.invalidateQueries({ queryKey: ['training-programs'] }); close() },
    onError: (err) => setError(extractErrorMessage(err)),
  })
  const deleteMutation = useMutation({
    mutationFn: (id: number) => trainingProgramsApi.remove(id),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['training-programs'] }),
  })

  function openCreate() {
    setEditing(null)
    setForm(emptyForm)
    setError(null)
    setModalOpen(true)
  }

  function openEdit(p: TrainingProgram) {
    setEditing(p)
    setForm({ name: p.name, description: p.description ?? '', chain_level: p.chain_level, venue: p.venue ?? '', start_date: p.start_date, end_date: p.end_date ?? '', status: p.status })
    setError(null)
    setModalOpen(true)
  }

  function close() {
    setModalOpen(false)
  }

  function handleSubmit() {
    setError(null)
    if (editing) updateMutation.mutate(form)
    else createMutation.mutate(form)
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-xl font-semibold text-slate-900">Training Programs</h1>
          <p className="text-sm text-slate-500">Manage training programs by chain level, schedule, and status.</p>
        </div>
        <Button onClick={openCreate}>+ New Training Program</Button>
      </div>

      <Card>
        {isLoading && <LoadingBlock />}
        {data && (
          <>
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm">
                <thead className="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                  <tr>
                    <th className="px-5 py-2.5">Name</th>
                    <th className="px-5 py-2.5">Chain Level</th>
                    <th className="px-5 py-2.5">Dates</th>
                    <th className="px-5 py-2.5">Attendees</th>
                    <th className="px-5 py-2.5">Status</th>
                    <th className="px-5 py-2.5" />
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {data.data.map((p) => (
                    <tr key={p.id}>
                      <td className="px-5 py-3 font-medium text-slate-800">{p.name}</td>
                      <td className="px-5 py-3 text-slate-600">{p.chain_level}</td>
                      <td className="px-5 py-3 text-slate-600">{p.start_date}{p.end_date ? ` – ${p.end_date}` : ''}</td>
                      <td className="px-5 py-3 text-slate-600">{p.attendee_count ?? 0}</td>
                      <td className="px-5 py-3"><Badge tone={statusTone[p.status]}>{p.status}</Badge></td>
                      <td className="px-5 py-3 text-right">
                        <div className="flex justify-end gap-2">
                          <Button size="sm" variant="secondary" onClick={() => setTestsProgram(p)}>Tests</Button>
                          <Button size="sm" variant="secondary" onClick={() => openEdit(p)}>Edit</Button>
                          <Button
                            size="sm"
                            variant="danger"
                            onClick={() => { if (confirm(`Delete "${p.name}"?`)) deleteMutation.mutate(p.id) }}
                          >
                            Delete
                          </Button>
                        </div>
                      </td>
                    </tr>
                  ))}
                  {data.data.length === 0 && (
                    <tr><td colSpan={6} className="px-5 py-10 text-center text-sm text-slate-400">No training programs yet.</td></tr>
                  )}
                </tbody>
              </table>
            </div>
            <Pagination page={data.meta.current_page} lastPage={data.meta.last_page} onChange={setPage} />
          </>
        )}
      </Card>

      <Modal open={modalOpen} onClose={close} title={editing ? 'Edit Training Program' : 'New Training Program'}>
        <div className="space-y-4">
          {error && <ErrorBlock message={error} />}
          <Field label="Program Name">
            <Input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
          </Field>
          <Field label="Description">
            <Textarea rows={2} value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} />
          </Field>
          <div className="grid grid-cols-2 gap-4">
            <Field label="Chain Level">
              <Select value={form.chain_level} onChange={(e) => setForm({ ...form, chain_level: e.target.value })}>
                {(meta?.chain_levels ?? ['Production', 'Post-Harvest', 'Processing', 'Marketing', 'Consumption']).map((c) => (
                  <option key={c} value={c}>{c}</option>
                ))}
              </Select>
            </Field>
            <Field label="Status">
              <Select value={form.status} onChange={(e) => setForm({ ...form, status: e.target.value })}>
                {(meta?.training_statuses ?? ['upcoming', 'ongoing', 'completed', 'cancelled']).map((s) => (
                  <option key={s} value={s}>{s}</option>
                ))}
              </Select>
            </Field>
          </div>
          <Field label="Venue">
            <Input value={form.venue} onChange={(e) => setForm({ ...form, venue: e.target.value })} />
          </Field>
          <div className="grid grid-cols-2 gap-4">
            <Field label="Start Date">
              <Input type="date" value={form.start_date} onChange={(e) => setForm({ ...form, start_date: e.target.value })} required />
            </Field>
            <Field label="End Date">
              <Input type="date" value={form.end_date} onChange={(e) => setForm({ ...form, end_date: e.target.value })} />
            </Field>
          </div>
          <Button
            className="w-full"
            onClick={handleSubmit}
            disabled={!form.name || !form.start_date || createMutation.isPending || updateMutation.isPending}
          >
            {editing ? 'Save Changes' : 'Create Training Program'}
          </Button>
        </div>
      </Modal>

      <Modal
        open={testsProgram !== null}
        onClose={() => setTestsProgram(null)}
        title={testsProgram ? `Tests — ${testsProgram.name}` : 'Tests'}
        wide
      >
        {testsProgram && <TestsManager trainingProgramId={testsProgram.id} />}
      </Modal>
    </div>
  )
}

const emptyQuestion = (): TestQuestion => ({ type: 'multiple_choice', question_text: '', options: ['', ''], correct_answer: '', points: 1 })

function TestsManager({ trainingProgramId }: { trainingProgramId: number }) {
  const [activeType, setActiveType] = useState<TestType>('pre_test')

  return (
    <div>
      <div className="mb-4 flex gap-2 border-b border-slate-200">
        {(['pre_test', 'post_test'] as const).map((t) => (
          <button
            key={t}
            onClick={() => setActiveType(t)}
            className={`border-b-2 px-3 py-2 text-sm font-medium ${
              activeType === t ? 'border-brand-600 text-brand-700' : 'border-transparent text-slate-500 hover:text-slate-700'
            }`}
          >
            {t === 'pre_test' ? 'Pre-Test' : 'Post-Test'}
          </button>
        ))}
      </div>
      <TestEditor key={activeType} trainingProgramId={trainingProgramId} type={activeType} />
    </div>
  )
}

function TestEditor({ trainingProgramId, type }: { trainingProgramId: number; type: TestType }) {
  const queryClient = useQueryClient()
  const { data, isLoading } = useQuery({
    queryKey: ['training-programs', trainingProgramId, 'tests', type],
    queryFn: () => trainingProgramTestsApi.show(trainingProgramId, type),
  })

  const [title, setTitle] = useState('')
  const [instructions, setInstructions] = useState('')
  const [passingScore, setPassingScore] = useState(70)
  const [questions, setQuestions] = useState<TestQuestion[]>([emptyQuestion()])
  const [error, setError] = useState<string | null>(null)
  const [saved, setSaved] = useState(false)

  useEffect(() => {
    if (data?.exists && data.test) {
      setTitle(data.test.title)
      setInstructions(data.test.instructions ?? '')
      setPassingScore(data.test.passing_score)
      setQuestions(data.test.questions.length > 0 ? data.test.questions : [emptyQuestion()])
    } else if (data && !data.exists) {
      setTitle('')
      setInstructions('')
      setPassingScore(70)
      setQuestions([emptyQuestion()])
    }
  }, [data])

  const saveMutation = useMutation({
    mutationFn: () => trainingProgramTestsApi.upsert(trainingProgramId, type, { title, instructions, passing_score: passingScore, questions }),
    onSuccess: () => {
      setError(null)
      setSaved(true)
      setTimeout(() => setSaved(false), 2000)
      queryClient.invalidateQueries({ queryKey: ['training-programs', trainingProgramId, 'tests', type] })
    },
    onError: (err) => setError(extractErrorMessage(err)),
  })

  const removeMutation = useMutation({
    mutationFn: () => trainingProgramTestsApi.remove(trainingProgramId, type),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['training-programs', trainingProgramId, 'tests', type] }),
  })

  const publishMutation = useMutation({
    // Save whatever's currently in the form first, so Publish can never make
    // live a stale version from before the officer's last unsaved edits.
    mutationFn: async () => {
      await trainingProgramTestsApi.upsert(trainingProgramId, type, { title, instructions, passing_score: passingScore, questions })
      return trainingProgramTestsApi.publish(trainingProgramId, type)
    },
    onSuccess: () => {
      setError(null)
      queryClient.invalidateQueries({ queryKey: ['training-programs', trainingProgramId, 'tests', type] })
    },
    onError: (err) => setError(extractErrorMessage(err)),
  })

  const unpublishMutation = useMutation({
    mutationFn: () => trainingProgramTestsApi.unpublish(trainingProgramId, type),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['training-programs', trainingProgramId, 'tests', type] }),
  })

  function updateQuestion(i: number, patch: Partial<TestQuestion>) {
    setQuestions((qs) => qs.map((q, idx) => (idx === i ? { ...q, ...patch } : q)))
  }

  function updateOption(qi: number, oi: number, value: string) {
    setQuestions((qs) =>
      qs.map((q, idx) => (idx === qi ? { ...q, options: (q.options ?? []).map((o, j) => (j === oi ? value : o)) } : q)),
    )
  }

  if (isLoading) return <LoadingBlock />

  const isValid = title.trim() !== '' && questions.every((q) => q.question_text.trim() !== '' && (q.correct_answer ?? '').trim() !== '')

  return (
    <div className="space-y-4">
      {error && <ErrorBlock message={error} />}
      {data?.exists && (
        <div className="flex items-center gap-2">
          <Badge tone={data.test?.is_published ? 'green' : 'amber'}>{data.test?.is_published ? 'Published' : 'Draft'}</Badge>
          <span className="text-xs text-slate-500">
            {data.test?.is_published
              ? "Trainees can see and take this test right now."
              : "Trainees won't see this until you publish it."}
          </span>
        </div>
      )}
      <Field label="Test Title">
        <Input value={title} onChange={(e) => setTitle(e.target.value)} placeholder="e.g. Production Basics Pre-Test" />
      </Field>
      <Field label="Instructions (optional)">
        <Textarea rows={2} value={instructions} onChange={(e) => setInstructions(e.target.value)} />
      </Field>
      <Field label="Passing Score (%)">
        <Input
          type="number"
          min={0}
          max={100}
          value={passingScore}
          onChange={(e) => setPassingScore(Number(e.target.value))}
          className="w-28"
        />
      </Field>

      <div className="space-y-3">
        <p className="text-sm font-medium text-slate-700">Questions</p>
        {questions.map((q, i) => (
          <Card key={i} className="p-4">
            <div className="flex items-start justify-between gap-3">
              <div className="flex-1 space-y-3">
                <div className="flex gap-3">
                  <Select
                    className="w-48"
                    value={q.type}
                    onChange={(e) =>
                      updateQuestion(i, {
                        type: e.target.value as TestQuestion['type'],
                        options: e.target.value === 'multiple_choice' ? ['', ''] : null,
                        correct_answer: '',
                      })
                    }
                  >
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="fill_blank">Fill in the Blank</option>
                    <option value="identification">Identification</option>
                  </Select>
                  <Input
                    type="number"
                    min={1}
                    value={q.points}
                    onChange={(e) => updateQuestion(i, { points: Number(e.target.value) })}
                    className="w-20"
                    title="Points"
                  />
                </div>
                <Textarea
                  rows={2}
                  placeholder="Question text"
                  value={q.question_text}
                  onChange={(e) => updateQuestion(i, { question_text: e.target.value })}
                />
                {q.type === 'multiple_choice' ? (
                  <div className="space-y-2">
                    {(q.options ?? []).map((opt, oi) => (
                      <div key={oi} className="flex items-center gap-2">
                        <input
                          type="radio"
                          name={`correct-${i}`}
                          checked={q.correct_answer === opt && opt !== ''}
                          onChange={() => updateQuestion(i, { correct_answer: opt })}
                          title="Mark as correct answer"
                        />
                        <Input
                          value={opt}
                          onChange={(e) => {
                            updateOption(i, oi, e.target.value)
                            if (q.correct_answer === opt) updateQuestion(i, { correct_answer: e.target.value })
                          }}
                          placeholder={`Option ${oi + 1}`}
                          className="flex-1"
                        />
                        {(q.options?.length ?? 0) > 2 && (
                          <button
                            type="button"
                            className="text-xs text-red-600"
                            onClick={() => updateQuestion(i, { options: (q.options ?? []).filter((_, j) => j !== oi) })}
                          >
                            Remove
                          </button>
                        )}
                      </div>
                    ))}
                    <Button size="sm" variant="ghost" onClick={() => updateQuestion(i, { options: [...(q.options ?? []), ''] })}>
                      + Add Option
                    </Button>
                    <p className="text-xs text-slate-400">Select the radio button next to the correct option.</p>
                  </div>
                ) : (
                  <Field label="Correct Answer">
                    <Input value={q.correct_answer} onChange={(e) => updateQuestion(i, { correct_answer: e.target.value })} />
                  </Field>
                )}
              </div>
              <button
                type="button"
                className="text-xs text-red-600 disabled:text-slate-300"
                disabled={questions.length <= 1}
                onClick={() => setQuestions((qs) => qs.filter((_, idx) => idx !== i))}
              >
                Remove question
              </button>
            </div>
          </Card>
        ))}
        <Button size="sm" variant="secondary" onClick={() => setQuestions((qs) => [...qs, emptyQuestion()])}>
          + Add Question
        </Button>
      </div>

      <div className="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-4">
        <div className="flex items-center gap-2">
          <Button onClick={() => saveMutation.mutate()} disabled={!isValid || saveMutation.isPending}>
            {saveMutation.isPending ? 'Saving…' : 'Save Draft'}
          </Button>
          {saved && <Badge tone="green">Saved</Badge>}
          {data?.exists &&
            (data.test?.is_published ? (
              <Button variant="secondary" size="sm" onClick={() => unpublishMutation.mutate()} disabled={unpublishMutation.isPending}>
                {unpublishMutation.isPending ? 'Unpublishing…' : 'Unpublish'}
              </Button>
            ) : (
              <Button variant="secondary" size="sm" onClick={() => publishMutation.mutate()} disabled={!isValid || publishMutation.isPending}>
                {publishMutation.isPending ? 'Publishing…' : 'Publish'}
              </Button>
            ))}
        </div>
        {data?.exists && (
          <Button
            variant="danger"
            size="sm"
            onClick={() => {
              if (confirm('Remove this test? Trainees will no longer be able to take it — the document will fall back to a plain upload.')) {
                removeMutation.mutate()
              }
            }}
          >
            Remove Test
          </Button>
        )}
      </div>
      <p className="text-xs text-slate-400">
        Save Draft keeps changes private to you. Publish is a separate step that makes the test visible and takeable by trainees —
        unsaved question edits are saved automatically before publishing.
      </p>
    </div>
  )
}
