import { apiClient } from './client'
import type { DocumentComplianceSummary, TrainingComplianceEntry } from '../types'

export const documentsApi = {
  forTrainee: (traineeId: number) =>
    apiClient.get<DocumentComplianceSummary>(`/trainees/${traineeId}/documents`).then((r) => r.data),
  forTrainingHistory: (trainingHistoryId: number) =>
    apiClient.get<TrainingComplianceEntry>(`/training-history/${trainingHistoryId}/documents`).then((r) => r.data),
  update: (trainingHistoryId: number, requiredDocumentId: number, submitted: boolean, dateSubmitted?: string) =>
    apiClient
      .put(`/training-history/${trainingHistoryId}/documents/${requiredDocumentId}`, {
        submitted,
        date_submitted: dateSubmitted,
      })
      .then((r) => r.data),
  myCompliance: () => apiClient.get<DocumentComplianceSummary>('/trainee/me/documents').then((r) => r.data),
}
