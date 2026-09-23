import { apiClient } from './client'
import type { DocumentComplianceSummary, ReviewStatus, TrainingComplianceEntry } from '../types'

export const documentsApi = {
  forTrainee: (traineeId: number) =>
    apiClient.get<DocumentComplianceSummary>(`/trainees/${traineeId}/documents`).then((r) => r.data),
  forTrainingHistory: (trainingHistoryId: number) =>
    apiClient.get<TrainingComplianceEntry>(`/training-history/${trainingHistoryId}/documents`).then((r) => r.data),
  /** Officer's manual attest / un-attest toggle — kept for paperwork handled outside the system. */
  update: (trainingHistoryId: number, requiredDocumentId: number, submitted: boolean, dateSubmitted?: string) =>
    apiClient
      .put(`/training-history/${trainingHistoryId}/documents/${requiredDocumentId}`, {
        submitted,
        date_submitted: dateSubmitted,
      })
      .then((r) => r.data),
  /** Officer explicitly approves or sends back a trainee's own upload/test result. */
  review: (trainingHistoryId: number, requiredDocumentId: number, reviewStatus: ReviewStatus, remarks?: string) =>
    apiClient
      .put(`/training-history/${trainingHistoryId}/documents/${requiredDocumentId}`, {
        review_status: reviewStatus,
        remarks,
      })
      .then((r) => r.data),
  /** Fetches the attachment as a blob (auth header attached by the client interceptor) and saves it. */
  download: async (trainingHistoryId: number, requiredDocumentId: number, scope: 'me' | 'officer' = 'officer') => {
    const path =
      scope === 'me'
        ? `/trainee/me/training-history/${trainingHistoryId}/documents/${requiredDocumentId}/download`
        : `/training-history/${trainingHistoryId}/documents/${requiredDocumentId}/download`
    const response = await apiClient.get(path, { responseType: 'blob' })
    const disposition = response.headers['content-disposition'] as string | undefined
    const match = disposition?.match(/filename="?([^"]+)"?/)
    const filename = match?.[1] ?? 'document'

    const url = URL.createObjectURL(response.data as Blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    link.click()
    URL.revokeObjectURL(url)
  },
  myCompliance: () => apiClient.get<DocumentComplianceSummary>('/trainee/me/documents').then((r) => r.data),
  myUpload: (trainingHistoryId: number, requiredDocumentId: number, file: File) => {
    const formData = new FormData()
    formData.append('file', file)
    return apiClient
      .post(`/trainee/me/training-history/${trainingHistoryId}/documents/${requiredDocumentId}/upload`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      .then((r) => r.data)
  },
}
