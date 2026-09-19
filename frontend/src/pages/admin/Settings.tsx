import { useEffect, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { adminApi } from '../../api/admin'
import { extractErrorMessage } from '../../api/client'
import { Badge, Button, Card, CardHeader, ErrorBlock, Field, Input, LoadingBlock, Modal } from '../../components/ui'
import type { RequiredDocument } from '../../types'

const SETTING_FIELDS: Array<{ key: string; label: string; placeholder: string }> = [
  { key: 'current_academic_term', label: 'Current Academic/Training Term', placeholder: 'e.g. CY 2026' },
  { key: 'training_program_categories', label: 'Training Program Categories (comma-separated)', placeholder: 'e.g. Crop Production, Livestock, Agribusiness' },
  { key: 'organization_name', label: 'Organization Name', placeholder: 'Agricultural Training Institute - Caraga' },
]

export default function AdminSettings() {
  const queryClient = useQueryClient()
  const { data, isLoading, error } = useQuery({ queryKey: ['admin', 'settings'], queryFn: adminApi.settings })
  const [values, setValues] = useState<Record<string, string>>({})
  const [saved, setSaved] = useState(false)

  useEffect(() => {
    if (data) setValues(data)
  }, [data])

  const saveMutation = useMutation({
    mutationFn: adminApi.updateSettings,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'settings'] })
      setSaved(true)
      setTimeout(() => setSaved(false), 2000)
    },
  })

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-semibold text-slate-900">System Settings</h1>
        <p className="text-sm text-slate-500">Configure academic term parameters and training program categories.</p>
      </div>

      <Card>
        <CardHeader title="Term &amp; Program Configuration" />
        {isLoading && <LoadingBlock />}
        {error && <div className="p-5"><ErrorBlock message={extractErrorMessage(error)} /></div>}
        {!isLoading && !error && (
          <div className="space-y-4 p-5">
            {SETTING_FIELDS.map((f) => (
              <Field key={f.key} label={f.label}>
                <Input
                  placeholder={f.placeholder}
                  value={values[f.key] ?? ''}
                  onChange={(e) => setValues({ ...values, [f.key]: e.target.value })}
                />
              </Field>
            ))}
            <div className="flex items-center gap-3 pt-2">
              <Button onClick={() => saveMutation.mutate(values)} disabled={saveMutation.isPending}>
                {saveMutation.isPending ? 'Saving…' : 'Save Settings'}
              </Button>
              {saved && <Badge tone="green">Saved</Badge>}
            </div>
          </div>
        )}
      </Card>

      <RequiredDocumentsPanel />
    </div>
  )
}

function RequiredDocumentsPanel() {
  const queryClient = useQueryClient()
  const { data, isLoading } = useQuery({ queryKey: ['admin', 'required-documents'], queryFn: adminApi.requiredDocuments })
  const [newDoc, setNewDoc] = useState({ code: '', name: '' })
  const [pendingDelete, setPendingDelete] = useState<RequiredDocument | null>(null)
  const [deleteError, setDeleteError] = useState<string | null>(null)

  const createMutation = useMutation({
    mutationFn: adminApi.createRequiredDocument,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'required-documents'] })
      queryClient.invalidateQueries({ queryKey: ['meta'] })
      setNewDoc({ code: '', name: '' })
    },
  })

  const toggleMutation = useMutation({
    mutationFn: (doc: { id: number; code: string; name: string; is_active?: boolean }) =>
      adminApi.updateRequiredDocument(doc.id, { code: doc.code, name: doc.name, is_active: !doc.is_active }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'required-documents'] })
      queryClient.invalidateQueries({ queryKey: ['meta'] })
    },
  })

  const deleteMutation = useMutation({
    mutationFn: adminApi.removeRequiredDocument,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'required-documents'] })
      queryClient.invalidateQueries({ queryKey: ['meta'] })
      setPendingDelete(null)
    },
    onError: (err) => setDeleteError(extractErrorMessage(err, 'Could not delete this document.')),
  })

  function openDeleteConfirm(doc: RequiredDocument) {
    setDeleteError(null)
    setPendingDelete(doc)
  }

  return (
    <Card>
      <CardHeader title="Required Post-Training Documents" subtitle="Tracked as Yes/No per trainee per training attended." />
      {isLoading && <LoadingBlock />}
      {data && (
        <div className="divide-y divide-slate-100">
          {data.map((doc) => (
            <div key={doc.id} className="flex items-center justify-between px-5 py-3 text-sm">
              <div>
                <p className="font-medium text-slate-800">{doc.name}</p>
                <p className="text-xs text-slate-400">{doc.code}</p>
              </div>
              <div className="flex items-center gap-2">
                <Badge tone={doc.is_active ? 'green' : 'slate'}>{doc.is_active ? 'Active' : 'Inactive'}</Badge>
                <Button size="sm" variant="secondary" onClick={() => toggleMutation.mutate(doc)}>
                  {doc.is_active ? 'Deactivate' : 'Activate'}
                </Button>
                <Button size="sm" variant="danger" onClick={() => openDeleteConfirm(doc)}>
                  Delete
                </Button>
              </div>
            </div>
          ))}
        </div>
      )}
      <div className="flex flex-wrap items-end gap-3 border-t border-slate-100 px-5 py-4">
        <Field label="Code">
          <Input value={newDoc.code} onChange={(e) => setNewDoc({ ...newDoc, code: e.target.value })} placeholder="e.g. certificate" className="w-40" />
        </Field>
        <Field label="Document Name">
          <Input value={newDoc.name} onChange={(e) => setNewDoc({ ...newDoc, name: e.target.value })} placeholder="e.g. Certificate of Completion" className="w-64" />
        </Field>
        <Button
          onClick={() => createMutation.mutate(newDoc)}
          disabled={!newDoc.code || !newDoc.name || createMutation.isPending}
        >
          Add Document
        </Button>
      </div>

      <Modal open={pendingDelete !== null} onClose={() => setPendingDelete(null)} title="Delete required document?">
        {pendingDelete && (
          <>
            <p className="text-sm text-slate-600">
              This permanently removes <strong>{pendingDelete.name}</strong> ({pendingDelete.code}). This can't be
              undone.
            </p>
            {deleteError ? (
              <div className="mt-3">
                <ErrorBlock message={deleteError} />
              </div>
            ) : (
              <p className="mt-2 text-sm text-slate-500">
                If it already has Yes/No records against any trainee, deletion will be refused — use{' '}
                <span className="font-medium text-slate-700">Deactivate</span> instead in that case, which keeps
                past compliance history intact and only stops it applying to future trainings.
              </p>
            )}
            <div className="mt-5 flex justify-end gap-2">
              <Button variant="secondary" size="sm" onClick={() => setPendingDelete(null)} disabled={deleteMutation.isPending}>
                Cancel
              </Button>
              <Button
                variant="danger"
                size="sm"
                onClick={() => deleteMutation.mutate(pendingDelete.id)}
                disabled={deleteMutation.isPending}
              >
                {deleteMutation.isPending ? 'Deleting…' : 'Delete'}
              </Button>
            </div>
          </>
        )}
      </Modal>
    </Card>
  )
}
