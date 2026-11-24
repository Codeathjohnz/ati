import { apiClient } from './client'
import type { TrainingProgram, TrainingRegistration } from '../types'

export interface PostedTraining extends TrainingProgram {
  is_registered: boolean
}

export const trainingRegistrationsApi = {
  /** Trainee: every posted training, flagged with whether they've already registered. */
  posted: () => apiClient.get<{ data: PostedTraining[] }>('/trainee/me/training-programs-posted').then((r) => r.data.data),
  register: (trainingProgramId: number) =>
    apiClient.post<{ data: TrainingRegistration }>(`/trainee/me/training-programs/${trainingProgramId}/register`).then((r) => r.data.data),
  withdraw: (trainingProgramId: number) => apiClient.delete(`/trainee/me/training-programs/${trainingProgramId}/register`),

  /** Officer/Admin: who has registered for a given training. */
  forTrainingProgram: (trainingProgramId: number) =>
    apiClient.get<{ data: TrainingRegistration[] }>(`/training-programs/${trainingProgramId}/registrations`).then((r) => r.data.data),
}
