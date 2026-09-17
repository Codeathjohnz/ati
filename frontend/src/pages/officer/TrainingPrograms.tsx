import { useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { trainingProgramsApi, type TrainingProgramPayload } from '../../api/trainingPrograms'
import { metaApi } from '../../api/meta'
import { extractErrorMessage } from '../../api/client'
import type { TrainingProgram } from '../../types'
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
    </div>
  )
}
