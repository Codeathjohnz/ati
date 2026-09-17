import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { confirmationsApi } from '../../api/confirmations'
import { extractErrorMessage } from '../../api/client'
import { Badge, Button, Card, CardHeader, ErrorBlock, LoadingBlock } from '../../components/ui'

export default function UpcomingTraining() {
  const queryClient = useQueryClient()
  const { data, isLoading, error } = useQuery({ queryKey: ['trainee', 'me', 'confirmations'], queryFn: confirmationsApi.mine })

  const respondMutation = useMutation({
    mutationFn: ({ id, status }: { id: number; status: 'confirmed' | 'declined' }) => confirmationsApi.myRespond(id, status),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['trainee', 'me', 'confirmations'] }),
  })

  if (isLoading) return <LoadingBlock />
  if (error || !data) return <ErrorBlock message={extractErrorMessage(error)} />

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-semibold text-slate-900">Upcoming Training Confirmation</h1>
        <p className="text-sm text-slate-500">Confirm your participation in scheduled training activities.</p>
      </div>

      <Card>
        <CardHeader title="Scheduled Trainings" />
        <div className="divide-y divide-slate-100">
          {data.map((c) => (
            <div key={c.id} className="flex flex-wrap items-center justify-between gap-3 px-5 py-4 text-sm">
              <div>
                <p className="font-medium text-slate-800">{c.training_program?.name}</p>
                <p className="text-xs text-slate-500">
                  {c.training_program?.start_date} &middot; {c.training_program?.venue ?? 'Venue TBA'} &middot; {c.training_program?.chain_level}
                </p>
              </div>
              <div className="flex items-center gap-2">
                {c.status === 'pending' ? (
                  <>
                    <Button size="sm" onClick={() => respondMutation.mutate({ id: c.id, status: 'confirmed' })}>Confirm</Button>
                    <Button size="sm" variant="secondary" onClick={() => respondMutation.mutate({ id: c.id, status: 'declined' })}>Decline</Button>
                  </>
                ) : (
                  <Badge tone={c.status === 'confirmed' ? 'green' : 'red'}>{c.status}</Badge>
                )}
              </div>
            </div>
          ))}
          {data.length === 0 && <p className="px-5 py-10 text-center text-sm text-slate-400">No upcoming training activities scheduled right now.</p>}
        </div>
      </Card>
    </div>
  )
}
