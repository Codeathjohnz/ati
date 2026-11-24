import { useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { trainingRegistrationsApi } from '../../api/trainingRegistrations'
import { extractErrorMessage } from '../../api/client'
import { Badge, Button, Card, CardHeader, ErrorBlock, LoadingBlock } from '../../components/ui'

export default function AvailableTrainings() {
  const queryClient = useQueryClient()
  const [error, setError] = useState<string | null>(null)
  const { data, isLoading } = useQuery({ queryKey: ['trainee', 'me', 'training-programs-posted'], queryFn: trainingRegistrationsApi.posted })

  const registerMutation = useMutation({
    mutationFn: trainingRegistrationsApi.register,
    onSuccess: () => {
      setError(null)
      queryClient.invalidateQueries({ queryKey: ['trainee', 'me', 'training-programs-posted'] })
    },
    onError: (err) => setError(extractErrorMessage(err, 'Could not register for this training.')),
  })

  const withdrawMutation = useMutation({
    mutationFn: trainingRegistrationsApi.withdraw,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['trainee', 'me', 'training-programs-posted'] }),
  })

  if (isLoading) return <LoadingBlock />

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-semibold text-slate-900">Available Trainings</h1>
        <p className="text-sm text-slate-500">
          Trainings your Project Officer has opened for registration. Register if you're interested in attending.
        </p>
      </div>

      {error && <ErrorBlock message={error} />}

      <Card>
        <CardHeader title="Posted Trainings" />
        <div className="divide-y divide-slate-100">
          {data?.map((program) => (
            <div key={program.id} className="flex flex-wrap items-center justify-between gap-3 px-5 py-4 text-sm">
              <div>
                <p className="font-medium text-slate-800">{program.name}</p>
                <p className="text-xs text-slate-500">
                  {program.start_date} &middot; {program.venue ?? 'Venue TBA'} &middot; {program.chain_levels.join(', ')}
                </p>
              </div>
              {program.is_registered ? (
                <div className="flex items-center gap-2">
                  <Badge tone="green">Registered</Badge>
                  <Button
                    size="sm"
                    variant="secondary"
                    onClick={() => withdrawMutation.mutate(program.id)}
                    disabled={withdrawMutation.isPending}
                  >
                    Withdraw
                  </Button>
                </div>
              ) : (
                <Button size="sm" onClick={() => registerMutation.mutate(program.id)} disabled={registerMutation.isPending}>
                  Register
                </Button>
              )}
            </div>
          ))}
          {data?.length === 0 && (
            <p className="px-5 py-10 text-center text-sm text-slate-400">No trainings are open for registration right now.</p>
          )}
        </div>
      </Card>
    </div>
  )
}
