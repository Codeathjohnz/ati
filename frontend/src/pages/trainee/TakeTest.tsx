import { useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { traineeTestApi } from '../../api/tests'
import { extractErrorMessage } from '../../api/client'
import { Badge, Button, Card, CardHeader, ErrorBlock, Field, Input, LoadingBlock } from '../../components/ui'
import type { TestType } from '../../types'

export default function TakeTest() {
  const { trainingHistoryId, type } = useParams<{ trainingHistoryId: string; type: TestType }>()
  const historyId = Number(trainingHistoryId)
  const queryClient = useQueryClient()

  const { data, isLoading, error } = useQuery({
    queryKey: ['trainee', 'me', 'tests', historyId, type],
    queryFn: () => traineeTestApi.show(historyId, type as TestType),
    enabled: Number.isFinite(historyId) && !!type,
  })

  const [answers, setAnswers] = useState<Record<number, string>>({})
  const [retaking, setRetaking] = useState(false)
  const [result, setResult] = useState<{ percentage: number; passed: boolean; raw_score: number; max_score: number } | null>(null)
  const [submitError, setSubmitError] = useState<string | null>(null)

  const attemptMutation = useMutation({
    mutationFn: () => traineeTestApi.attempt(historyId, type as TestType, answers),
    onSuccess: (res) => {
      setSubmitError(null)
      setResult(res.attempt)
      setRetaking(false)
      queryClient.invalidateQueries({ queryKey: ['trainee', 'me', 'tests', historyId, type] })
      queryClient.invalidateQueries({ queryKey: ['trainee', 'me', 'documents'] })
    },
    onError: (err) => setSubmitError(extractErrorMessage(err, 'Could not submit your answers.')),
  })

  if (isLoading) return <LoadingBlock />
  if (error || !data) return <ErrorBlock message={extractErrorMessage(error)} />

  if (!data.exists) {
    return (
      <Card className="px-5 py-10 text-center text-sm text-slate-500">
        <p>No test has been set up for this training yet.</p>
        <Link to="/trainee/documents" className="mt-3 inline-block text-brand-600 hover:underline">
          Back to My Documents
        </Link>
      </Card>
    )
  }

  if (data.published === false || !data.test) {
    return (
      <Card className="px-5 py-10 text-center text-sm text-slate-500">
        <p>Your Project Officer is still preparing this test — check back once it's published.</p>
        <Link to="/trainee/documents" className="mt-3 inline-block text-brand-600 hover:underline">
          Back to My Documents
        </Link>
      </Card>
    )
  }

  const shownResult = result ?? (!retaking ? data.latest_attempt : null)
  const showForm = !shownResult || retaking

  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <div>
        <Link to="/trainee/documents" className="text-sm text-brand-600 hover:underline">
          ← Back to My Documents
        </Link>
      </div>

      <Card>
        <CardHeader title={data.test.title} subtitle={data.test.instructions ?? undefined} />

        {shownResult && !retaking && (
          <div className="space-y-4 p-5">
            <div className="flex items-center justify-between rounded-lg border border-slate-200 p-4">
              <div>
                <p className="text-sm text-slate-500">Your latest score</p>
                <p className="text-2xl font-semibold text-slate-900">
                  {shownResult.percentage}% ({shownResult.raw_score}/{shownResult.max_score})
                </p>
              </div>
              <Badge tone={shownResult.passed ? 'green' : 'red'}>
                {shownResult.passed ? 'Passed' : `Below passing score (${data.test.passing_score}%)`}
              </Badge>
            </div>
            <Button variant="secondary" onClick={() => { setRetaking(true); setResult(null); setAnswers({}) }}>
              Retake Test
            </Button>
          </div>
        )}

        {showForm && (
          <div className="space-y-5 p-5">
            {submitError && <ErrorBlock message={submitError} />}
            {data.test.questions.map((q, i) => (
              <div key={q.id}>
                <p className="mb-2 text-sm font-medium text-slate-800">
                  {i + 1}. {q.question_text}
                </p>
                {q.type === 'multiple_choice' ? (
                  <div className="space-y-1.5">
                    {(q.options ?? []).map((opt) => (
                      <label key={opt} className="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                        <input
                          type="radio"
                          name={`q-${q.id}`}
                          checked={answers[q.id!] === opt}
                          onChange={() => setAnswers((a) => ({ ...a, [q.id!]: opt }))}
                        />
                        {opt}
                      </label>
                    ))}
                  </div>
                ) : (
                  <Field label="Your answer">
                    <Input value={answers[q.id!] ?? ''} onChange={(e) => setAnswers((a) => ({ ...a, [q.id!]: e.target.value }))} />
                  </Field>
                )}
              </div>
            ))}
            <Button onClick={() => attemptMutation.mutate()} disabled={attemptMutation.isPending} className="w-full">
              {attemptMutation.isPending ? 'Submitting…' : 'Submit Test'}
            </Button>
          </div>
        )}
      </Card>
    </div>
  )
}
