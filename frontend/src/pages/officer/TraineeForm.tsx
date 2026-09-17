import { useEffect, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useNavigate, useParams } from 'react-router-dom'
import { traineesApi, type TraineePayload } from '../../api/trainees'
import { metaApi } from '../../api/meta'
import { extractErrorMessage } from '../../api/client'
import { Button, Card, CardHeader, ErrorBlock, Field, Input, LoadingBlock, PasswordInput, Select } from '../../components/ui'

const emptyForm: TraineePayload = {
  first_name: '',
  middle_name: '',
  last_name: '',
  suffix: '',
  sex: 'Male',
  birthdate: '',
  contact_number: '',
  email: '',
  address_line: '',
  barangay: '',
  city_municipality: '',
  province: '',
  lgu_income_class: '',
  educational_level: '',
  organization_affiliation: '',
  occupation: '',
  create_login: false,
  username: '',
  password: '',
}

export default function TraineeForm() {
  const { id } = useParams()
  const isEdit = !!id
  const navigate = useNavigate()
  const queryClient = useQueryClient()

  const { data: meta } = useQuery({ queryKey: ['meta'], queryFn: metaApi.get })
  const { data: existing, isLoading } = useQuery({
    queryKey: ['trainees', id],
    queryFn: () => traineesApi.get(Number(id)),
    enabled: isEdit,
  })

  const [form, setForm] = useState<TraineePayload>(emptyForm)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (existing) {
      setForm({
        first_name: existing.first_name,
        middle_name: existing.middle_name ?? '',
        last_name: existing.last_name,
        suffix: existing.suffix ?? '',
        sex: existing.sex,
        birthdate: existing.birthdate ?? '',
        contact_number: existing.contact_number ?? '',
        email: existing.email ?? '',
        address_line: existing.address_line ?? '',
        barangay: existing.barangay ?? '',
        city_municipality: existing.city_municipality,
        province: existing.province,
        lgu_income_class: existing.lgu_income_class ?? '',
        educational_level: existing.educational_level ?? '',
        organization_affiliation: existing.organization_affiliation ?? '',
        occupation: existing.occupation ?? '',
      })
    }
  }, [existing])

  const createMutation = useMutation({
    mutationFn: traineesApi.create,
    onSuccess: (trainee) => {
      queryClient.invalidateQueries({ queryKey: ['trainees'] })
      navigate(`/officer/trainees/${trainee.id}`)
    },
    onError: (err) => setError(extractErrorMessage(err)),
  })

  const updateMutation = useMutation({
    mutationFn: (payload: TraineePayload) => traineesApi.update(Number(id), payload),
    onSuccess: (trainee) => {
      queryClient.invalidateQueries({ queryKey: ['trainees'] })
      navigate(`/officer/trainees/${trainee.id}`)
    },
    onError: (err) => setError(extractErrorMessage(err)),
  })

  function set<K extends keyof TraineePayload>(key: K, value: TraineePayload[K]) {
    setForm((f) => ({ ...f, [key]: value }))
  }

  function handleSubmit() {
    setError(null)
    if (isEdit) updateMutation.mutate(form)
    else createMutation.mutate(form)
  }

  if (isEdit && isLoading) return <LoadingBlock />

  const saving = createMutation.isPending || updateMutation.isPending

  return (
    <div className="mx-auto max-w-3xl space-y-6">
      <div>
        <h1 className="text-xl font-semibold text-slate-900">{isEdit ? 'Edit Trainee Profile' : 'Register New Trainee'}</h1>
        <p className="text-sm text-slate-500">Comprehensive trainee profile: personal, demographic, and contact information.</p>
      </div>

      {error && <ErrorBlock message={error} />}

      <Card>
        <CardHeader title="Personal Information" />
        <div className="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
          <Field label="First Name"><Input value={form.first_name} onChange={(e) => set('first_name', e.target.value)} required /></Field>
          <Field label="Middle Name"><Input value={form.middle_name} onChange={(e) => set('middle_name', e.target.value)} /></Field>
          <Field label="Last Name"><Input value={form.last_name} onChange={(e) => set('last_name', e.target.value)} required /></Field>
          <Field label="Suffix"><Input value={form.suffix} onChange={(e) => set('suffix', e.target.value)} placeholder="Jr., Sr., III" /></Field>
          <Field label="Sex">
            <Select value={form.sex} onChange={(e) => set('sex', e.target.value)}>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </Select>
          </Field>
          <Field label="Birthdate"><Input type="date" value={form.birthdate} onChange={(e) => set('birthdate', e.target.value)} /></Field>
        </div>
      </Card>

      <Card>
        <CardHeader title="Contact Details" />
        <div className="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
          <Field label="Contact Number"><Input value={form.contact_number} onChange={(e) => set('contact_number', e.target.value)} /></Field>
          <Field label="Email"><Input type="email" value={form.email} onChange={(e) => set('email', e.target.value)} /></Field>
          <Field label="Address Line"><Input value={form.address_line} onChange={(e) => set('address_line', e.target.value)} /></Field>
          <Field label="Barangay"><Input value={form.barangay} onChange={(e) => set('barangay', e.target.value)} /></Field>
        </div>
      </Card>

      <Card>
        <CardHeader title="Demographic Information" />
        <div className="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
          <Field label="Province">
            <Input list="provinces" value={form.province} onChange={(e) => set('province', e.target.value)} required />
            <datalist id="provinces">
              {meta?.provinces.map((p) => <option key={p} value={p} />)}
            </datalist>
          </Field>
          <Field label="City/Municipality"><Input value={form.city_municipality} onChange={(e) => set('city_municipality', e.target.value)} required /></Field>
          <Field label="LGU Income Class">
            <Select value={form.lgu_income_class} onChange={(e) => set('lgu_income_class', e.target.value)}>
              <option value="">Select…</option>
              {meta?.income_classes.map((c) => <option key={c} value={c}>{c}</option>)}
            </Select>
          </Field>
          <Field label="Educational Level">
            <Select value={form.educational_level} onChange={(e) => set('educational_level', e.target.value)}>
              <option value="">Select…</option>
              {meta?.educational_levels.map((l) => <option key={l} value={l}>{l}</option>)}
            </Select>
          </Field>
          <Field label="Organization/Affiliation"><Input value={form.organization_affiliation} onChange={(e) => set('organization_affiliation', e.target.value)} /></Field>
          <Field label="Occupation"><Input value={form.occupation} onChange={(e) => set('occupation', e.target.value)} /></Field>
        </div>
      </Card>

      {!isEdit && (
        <Card>
          <CardHeader title="Login Account (optional)" subtitle="Create trainee portal access now, or leave unchecked and set it up later." />
          <div className="space-y-4 p-5">
            <label className="flex items-center gap-2 text-sm text-slate-700">
              <input type="checkbox" checked={!!form.create_login} onChange={(e) => set('create_login', e.target.checked)} />
              Create a login account for this trainee
            </label>
            {form.create_login && (
              <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Field label="Username"><Input value={form.username} onChange={(e) => set('username', e.target.value)} required /></Field>
                <Field label="Password"><PasswordInput value={form.password} onChange={(e) => set('password', e.target.value)} required /></Field>
              </div>
            )}
          </div>
        </Card>
      )}

      <div className="flex justify-end gap-3">
        <Button variant="secondary" onClick={() => navigate(-1)}>Cancel</Button>
        <Button onClick={handleSubmit} disabled={saving}>
          {saving ? 'Saving…' : isEdit ? 'Save Changes' : 'Register Trainee'}
        </Button>
      </div>
    </div>
  )
}
