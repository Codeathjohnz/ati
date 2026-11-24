import { useEffect, useMemo, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { useMutation, useQuery } from '@tanstack/react-query'
import { traineeSelfApi } from '../../api/traineeSelf'
import { rpEvaluationsApi } from '../../api/rpEvaluations'
import { extractErrorMessage } from '../../api/client'
import { LIKERT_CRITERIA, type TopicRating, type TopicMethodology, type TopicRelevance, type TopicTimeAllocation } from '../../types'
import { Button, Card, CardHeader, ErrorBlock, LoadingBlock, Textarea } from '../../components/ui'

const RELEVANCE_OPTIONS: TopicRelevance[] = ['Very Relevant', 'Somewhat relevant', 'Not at all relevant']
const TIME_OPTIONS: TopicTimeAllocation[] = ['Just right', 'Need improvement']
const METHODOLOGY_OPTIONS: TopicMethodology[] = ['Effective', 'Not Effective']

export default function EvaluationForm() {
  const { trainingHistoryId } = useParams<{ trainingHistoryId: string }>()
  const historyId = Number(trainingHistoryId)
  const navigate = useNavigate()

  const { data: activity, isLoading: loadingActivity } = useQuery({
    queryKey: ['trainee', 'me', 'activity-history'],
    queryFn: traineeSelfApi.activityHistory,
  })
  const { data: existing, isLoading: loadingExisting } = useQuery({
    queryKey: ['trainee', 'me', 'training-history', historyId, 'evaluation'],
    queryFn: () => rpEvaluationsApi.mine(historyId),
    enabled: !!historyId,
  })

  const history = activity?.training_history.find((h) => h.id === historyId)
  const topics = useMemo(() => history?.training_program?.topics ?? [], [history])

  const [topicRatings, setTopicRatings] = useState<Partial<TopicRating>[]>([])
  const [likert, setLikert] = useState<Record<string, number>>({})
  const [liked, setLiked] = useState('')
  const [disliked, setDisliked] = useState('')
  const [remarks, setRemarks] = useState('')
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    setTopicRatings(topics.map((t) => ({ topic: t.name })))
  }, [topics])

  const submitMutation = useMutation({
    mutationFn: () =>
      rpEvaluationsApi.submit(historyId, {
        topic_ratings: topicRatings as TopicRating[],
        clarity_objectives: likert.clarity_objectives,
        topic_organization: likert.topic_organization,
        clarity_presentation: likert.clarity_presentation,
        instructional_aids_quality: likert.instructional_aids_quality,
        teaching_ability: likert.teaching_ability,
        question_answering_ability: likert.question_answering_ability,
        participant_interest: likert.participant_interest,
        time_management: likert.time_management,
        topic_ending: likert.topic_ending,
        overall_satisfaction: likert.overall_satisfaction,
        liked_about_rp: liked || null,
        disliked_about_rp: disliked || null,
        other_remarks: remarks || null,
      }),
    onSuccess: () => navigate('/trainee/training-history'),
    onError: (err) => setError(extractErrorMessage(err, 'Could not submit your evaluation.')),
  })

  if (loadingActivity || loadingExisting) return <LoadingBlock />
  if (!history) return <ErrorBlock message="That training record could not be found." />

  if (existing) {
    return (
      <Card className="p-6 text-center">
        <p className="text-base font-semibold text-brand-700">You've already submitted this evaluation.</p>
        <p className="mt-1 text-sm text-slate-500">Thank you for your feedback on {history.training_program?.name}.</p>
      </Card>
    )
  }

  const topicsComplete = topicRatings.every((t) => t.relevance && t.time_allocation && t.methodology)
  const likertComplete = LIKERT_CRITERIA.every((c) => likert[c.key] != null)
  const canSubmit = topicsComplete && likertComplete && !submitMutation.isPending

  function updateTopic(i: number, patch: Partial<TopicRating>) {
    setTopicRatings((rows) => rows.map((r, idx) => (idx === i ? { ...r, ...patch } : r)))
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-semibold text-slate-900">Resource Person Evaluation Form</h1>
        <p className="text-sm text-slate-500">
          {history.training_program?.name}
          {history.training_program?.resource_person_name && <> &middot; Resource Person: {history.training_program.resource_person_name}</>}
          {history.date_attended && <> &middot; {history.date_attended}</>}
        </p>
      </div>

      {error && <ErrorBlock message={error} />}

      {topics.length > 0 && (
        <Card>
          <CardHeader
            title="Topics Discussed"
            subtitle="Relevance, time allocation, and effectiveness of the methodology used by the resource person."
          />
          <div className="divide-y divide-slate-100">
            {topics.map((topic, i) => (
              <div key={topic.name} className="space-y-3 px-5 py-4">
                <p className="text-sm font-medium text-slate-800">
                  {topic.name}
                  {topic.duration && <span className="ml-2 text-xs text-slate-400">({topic.duration})</span>}
                </p>

                <RadioRow name={`topic-${i}-relevance`} label="Relevance" options={RELEVANCE_OPTIONS} value={topicRatings[i]?.relevance} onChange={(v) => updateTopic(i, { relevance: v })} />
                <RadioRow name={`topic-${i}-time`} label="Time Allocation" options={TIME_OPTIONS} value={topicRatings[i]?.time_allocation} onChange={(v) => updateTopic(i, { time_allocation: v })} />
                {topicRatings[i]?.time_allocation === 'Need improvement' && (
                  <input
                    className="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm"
                    placeholder="Suggested time allocation"
                    value={topicRatings[i]?.suggested_time ?? ''}
                    onChange={(e) => updateTopic(i, { suggested_time: e.target.value })}
                  />
                )}
                <RadioRow name={`topic-${i}-methodology`} label="Methodology Used" options={METHODOLOGY_OPTIONS} value={topicRatings[i]?.methodology} onChange={(v) => updateTopic(i, { methodology: v })} />
                {topicRatings[i]?.methodology === 'Not Effective' && (
                  <input
                    className="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm"
                    placeholder="Suggested methodology"
                    value={topicRatings[i]?.suggested_methodology ?? ''}
                    onChange={(e) => updateTopic(i, { suggested_methodology: e.target.value })}
                  />
                )}
              </div>
            ))}
          </div>
        </Card>
      )}

      <Card>
        <CardHeader title="Level of Satisfaction" subtitle="Rating Guide: 1 - Poor, 2 - Fair, 3 - Satisfactory, 4 - Very Satisfactory, 5 - Excellent" />
        <div className="divide-y divide-slate-100">
          {LIKERT_CRITERIA.map((c) => (
            <div key={c.key} className="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
              <span className="text-sm text-slate-700">{c.label}</span>
              <div className="flex gap-2">
                {[1, 2, 3, 4, 5].map((n) => (
                  <label key={n} className="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full border border-slate-300 text-sm has-[:checked]:border-brand-600 has-[:checked]:bg-brand-600 has-[:checked]:text-white">
                    <input
                      type="radio"
                      name={c.key}
                      className="sr-only"
                      checked={likert[c.key] === n}
                      onChange={() => setLikert({ ...likert, [c.key]: n })}
                    />
                    {n}
                  </label>
                ))}
              </div>
            </div>
          ))}
        </div>
      </Card>

      <Card className="space-y-4 p-5">
        <div>
          <label className="mb-1 block text-sm font-medium text-slate-700">What did you like about the resource person?</label>
          <Textarea rows={2} value={liked} onChange={(e) => setLiked(e.target.value)} />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-slate-700">What did you not like about the resource person?</label>
          <Textarea rows={2} value={disliked} onChange={(e) => setDisliked(e.target.value)} />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium text-slate-700">Other remarks</label>
          <Textarea rows={2} value={remarks} onChange={(e) => setRemarks(e.target.value)} />
        </div>
      </Card>

      <Button className="w-full justify-center" onClick={() => submitMutation.mutate()} disabled={!canSubmit}>
        {submitMutation.isPending ? 'Submitting…' : 'Submit Evaluation'}
      </Button>
    </div>
  )
}

function RadioRow<T extends string>({ name, label, options, value, onChange }: { name: string; label: string; options: T[]; value: T | undefined; onChange: (v: T) => void }) {
  return (
    <div className="flex flex-wrap items-center gap-x-4 gap-y-1">
      <span className="w-36 shrink-0 text-xs font-medium text-slate-500">{label}</span>
      {options.map((opt) => (
        <label key={opt} className="flex cursor-pointer items-center gap-1.5 text-sm text-slate-700">
          <input type="radio" name={name} checked={value === opt} onChange={() => onChange(opt)} className="text-brand-600 focus:ring-brand-500" />
          {opt}
        </label>
      ))}
    </div>
  )
}
