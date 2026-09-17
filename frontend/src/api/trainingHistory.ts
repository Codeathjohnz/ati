import { apiClient } from './client'
import type { TrainingHistoryRecord } from '../types'

export interface TrainingHistoryPayload {
  training_program_id: number
  date_attended: string
  remarks?: string
}

export const trainingHistoryApi = {
  list: (traineeId: number) =>
    apiClient.get<{ data: TrainingHistoryRecord[] }>(`/trainees/${traineeId}/training-history`).then((r) => r.data.data),
  create: (traineeId: number, payload: TrainingHistoryPayload) =>
    apiClient
      .post<{ data: TrainingHistoryRecord }>(`/trainees/${traineeId}/training-history`, payload)
      .then((r) => r.data.data),
  update: (historyId: number, payload: Partial<TrainingHistoryPayload>) =>
    apiClient.put<{ data: TrainingHistoryRecord }>(`/training-history/${historyId}`, payload).then((r) => r.data.data),
  remove: (historyId: number) => apiClient.delete(`/training-history/${historyId}`),
}
