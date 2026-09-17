import { apiClient } from './client'
import type { TrainingConfirmation } from '../types'

export const confirmationsApi = {
  forTrainee: (traineeId: number) =>
    apiClient.get<{ data: TrainingConfirmation[] }>(`/trainees/${traineeId}/confirmations`).then((r) => r.data.data),
  forTrainingProgram: (trainingProgramId: number) =>
    apiClient
      .get<{ data: TrainingConfirmation[] }>(`/training-programs/${trainingProgramId}/confirmations`)
      .then((r) => r.data.data),
  respond: (confirmationId: number, status: 'confirmed' | 'declined') =>
    apiClient
      .post<{ data: TrainingConfirmation }>(`/confirmations/${confirmationId}/respond`, { status })
      .then((r) => r.data.data),
  mine: () => apiClient.get<{ data: TrainingConfirmation[] }>('/trainee/me/confirmations').then((r) => r.data.data),
  myRespond: (confirmationId: number, status: 'confirmed' | 'declined') =>
    apiClient
      .post<{ data: TrainingConfirmation }>(`/trainee/me/confirmations/${confirmationId}/respond`, { status })
      .then((r) => r.data.data),
}
