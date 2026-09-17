import { useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { usersApi, type UserPayload } from '../../api/users'
import { extractErrorMessage } from '../../api/client'
import type { Role, User } from '../../types'
import {
  Badge,
  Button,
  Card,
  ErrorBlock,
  Field,
  Input,
  LoadingBlock,
  Modal,
  Pagination,
  PasswordInput,
  Select,
} from '../../components/ui'

const roleLabels: Record<Role, string> = {
  admin: 'Administrator',
  project_officer: 'Project Officer',
  trainee: 'Trainee',
}

const emptyForm: UserPayload = { name: '', username: '', email: '', role: 'project_officer', password: '' }

export default function AdminUsers() {
  const queryClient = useQueryClient()
  const [page, setPage] = useState(1)
  const [roleFilter, setRoleFilter] = useState<string>('')
  const [q, setQ] = useState('')
  const [modalOpen, setModalOpen] = useState(false)
  const [editing, setEditing] = useState<User | null>(null)
  const [form, setForm] = useState<UserPayload>(emptyForm)
  const [formError, setFormError] = useState<string | null>(null)

  const { data, isLoading, error } = useQuery({
    queryKey: ['admin', 'users', { page, roleFilter, q }],
    queryFn: () => usersApi.list({ page, role: (roleFilter || undefined) as Role | undefined, q: q || undefined }),
  })

  const createMutation = useMutation({
    mutationFn: usersApi.create,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'users'] })
      closeModal()
    },
    onError: (err) => setFormError(extractErrorMessage(err)),
  })

  const updateMutation = useMutation({
    mutationFn: ({ id, payload }: { id: number; payload: Partial<UserPayload> }) => usersApi.update(id, payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin', 'users'] })
      closeModal()
    },
    onError: (err) => setFormError(extractErrorMessage(err)),
  })

  const toggleActiveMutation = useMutation({
    mutationFn: (u: User) => (u.is_active ? usersApi.deactivate(u.id) : usersApi.activate(u.id)),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin', 'users'] }),
  })

  function openCreate() {
    setEditing(null)
    setForm(emptyForm)
    setFormError(null)
    setModalOpen(true)
  }

  function openEdit(u: User) {
    setEditing(u)
    setForm({ name: u.name, username: u.username, email: u.email, role: u.role, password: '' })
    setFormError(null)
    setModalOpen(true)
  }

  function closeModal() {
    setModalOpen(false)
  }

  function handleSubmit() {
    setFormError(null)
    if (editing) {
      const payload: Partial<UserPayload> = { ...form }
      if (!payload.password) delete payload.password
      updateMutation.mutate({ id: editing.id, payload })
    } else {
      createMutation.mutate(form)
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-xl font-semibold text-slate-900">User Accounts</h1>
          <p className="text-sm text-slate-500">Manage Project Officer and Trainee login accounts.</p>
        </div>
        <Button onClick={openCreate}>+ New Account</Button>
      </div>

      <Card>
        <div className="flex flex-wrap items-center gap-3 border-b border-slate-100 px-5 py-3">
          <Input placeholder="Search name, username, email…" value={q} onChange={(e) => { setQ(e.target.value); setPage(1) }} className="max-w-xs" />
          <Select value={roleFilter} onChange={(e) => { setRoleFilter(e.target.value); setPage(1) }} className="max-w-[200px]">
            <option value="">All Roles</option>
            <option value="admin">Administrator</option>
            <option value="project_officer">Project Officer</option>
            <option value="trainee">Trainee</option>
          </Select>
        </div>

        {isLoading && <LoadingBlock />}
        {error && <div className="p-5"><ErrorBlock message={extractErrorMessage(error)} /></div>}

        {data && (
          <>
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm">
                <thead className="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                  <tr>
                    <th className="px-5 py-2.5">Name</th>
                    <th className="px-5 py-2.5">Username</th>
                    <th className="px-5 py-2.5">Email</th>
                    <th className="px-5 py-2.5">Role</th>
                    <th className="px-5 py-2.5">Status</th>
                    <th className="px-5 py-2.5" />
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {data.data.map((u) => (
                    <tr key={u.id}>
                      <td className="px-5 py-3 font-medium text-slate-800">{u.name}</td>
                      <td className="px-5 py-3 text-slate-600">{u.username}</td>
                      <td className="px-5 py-3 text-slate-600">{u.email}</td>
                      <td className="px-5 py-3 text-slate-600">{roleLabels[u.role]}</td>
                      <td className="px-5 py-3">
                        <Badge tone={u.is_active ? 'green' : 'red'}>{u.is_active ? 'Active' : 'Deactivated'}</Badge>
                      </td>
                      <td className="px-5 py-3 text-right">
                        <div className="flex justify-end gap-2">
                          <Button size="sm" variant="secondary" onClick={() => openEdit(u)}>Edit</Button>
                          <Button
                            size="sm"
                            variant={u.is_active ? 'danger' : 'secondary'}
                            onClick={() => toggleActiveMutation.mutate(u)}
                          >
                            {u.is_active ? 'Deactivate' : 'Activate'}
                          </Button>
                        </div>
                      </td>
                    </tr>
                  ))}
                  {data.data.length === 0 && (
                    <tr>
                      <td colSpan={6} className="px-5 py-10 text-center text-sm text-slate-400">No accounts found.</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
            <Pagination page={data.meta.current_page} lastPage={data.meta.last_page} onChange={setPage} />
          </>
        )}
      </Card>

      <Modal open={modalOpen} onClose={closeModal} title={editing ? 'Edit Account' : 'New Account'}>
        <div className="space-y-4">
          {formError && <ErrorBlock message={formError} />}
          <Field label="Full Name">
            <Input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
          </Field>
          <Field label="Username">
            <Input value={form.username} onChange={(e) => setForm({ ...form, username: e.target.value })} required />
          </Field>
          <Field label="Email">
            <Input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required />
          </Field>
          <Field label="Role">
            <Select value={form.role} onChange={(e) => setForm({ ...form, role: e.target.value as Role })}>
              <option value="admin">Administrator</option>
              <option value="project_officer">Project Officer</option>
              <option value="trainee">Trainee</option>
            </Select>
          </Field>
          <Field label={editing ? 'New Password (leave blank to keep current)' : 'Password'}>
            <PasswordInput
              value={form.password}
              onChange={(e) => setForm({ ...form, password: e.target.value })}
              required={!editing}
            />
          </Field>
          <Button
            className="w-full"
            onClick={handleSubmit}
            disabled={createMutation.isPending || updateMutation.isPending}
          >
            {editing ? 'Save Changes' : 'Create Account'}
          </Button>
        </div>
      </Modal>
    </div>
  )
}
