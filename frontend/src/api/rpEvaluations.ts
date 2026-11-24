import { apiClient } from './client'
import type { RpEvaluation, RpEvaluationPayload } from '../types'

export const rpEvaluationsApi = {
  mine: (trainingHistoryId: number) =>
    apiClient
      .get<{ data: RpEvaluation | null }>(`/trainee/me/training-history/${trainingHistoryId}/evaluation`)
      .then((r) => r.data.data),
  submit: (trainingHistoryId: number, payload: RpEvaluationPayload) =>
    apiClient
      .post<{ data: RpEvaluation }>(`/trainee/me/training-history/${trainingHistoryId}/evaluation`, payload)
      .then((r) => r.data.data),
}
