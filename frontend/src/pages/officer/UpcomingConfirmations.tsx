import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { trainingProgramsApi } from '../../api/trainingPrograms'
import { confirmationsApi } from '../../api/confirmations'
import { Badge, Card, LoadingBlock, Select } from '../../components/ui'

export default function UpcomingConfirmations() {
  const { data: upcoming, isLoading: loadingPrograms } = useQuery({
    queryKey: ['training-programs', 'upcoming'],
    queryFn: trainingProgramsApi.upcoming,
  })
  const [selectedId, setSelectedId] = useState<number | null>(null)

  const activeId = selectedId ?? upcoming?.[0]?.id ?? null

  const { data: roster, isLoading: loadingRoster } = useQuery({
    queryKey: ['training-programs', activeId, 'confirmations'],
    queryFn: () => confirmationsApi.forTrainingProgram(activeId!),
    enabled: !!activeId,
  })

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-semibold text-slate-900">Upcoming Training Confirmations</h1>
        <p className="text-sm text-slate-500">See which trainees have confirmed participation in scheduled activities.</p>
      </div>

      {loadingPrograms ? (
        <LoadingBlock />
      ) : !upcoming || upcoming.length === 0 ? (
        <Card className="px-5 py-10 text-center text-sm text-slate-400">No upcoming or ongoing training programs scheduled.</Card>
      ) : (
        <Card>
          <div className="border-b border-slate-100 p-4">
            <Select value={activeId ?? ''} onChange={(e) => setSelectedId(Number(e.target.value))} className="max-w-md">
              {upcoming.map((p) => (
                <option key={p.id} value={p.id}>{p.name} — {p.start_date}</option>
              ))}
            </Select>
          </div>

          {loadingRoster ? (
            <LoadingBlock />
          ) : (
            <div className="divide-y divide-slate-100">
              {roster?.map((c) => (
                <div key={c.id} className="flex items-center justify-between px-5 py-3 text-sm">
                  <span className="font-medium text-slate-800">{c.trainee?.full_name}</span>
                  <Badge tone={c.status === 'confirmed' ? 'green' : c.status === 'declined' ? 'red' : 'amber'}>{c.status}</Badge>
                </div>
              ))}
              {roster?.length === 0 && <p className="px-5 py-10 text-center text-sm text-slate-400">No trainees have a confirmation record for this training yet.</p>}
            </div>
          )}
        </Card>
      )}
    </div>
  )
}
