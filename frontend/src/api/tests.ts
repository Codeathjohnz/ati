import { apiClient } from './client'
import type { TestAttemptResult, TestQuestion, TestType, TrainingProgramTest } from '../types'

export interface TestPayload {
  title: string
  instructions?: string
  passing_score: number
  questions: TestQuestion[]
}

export const trainingProgramTestsApi = {
  /** Officer: fetch a program's Pre-Test/Post-Test, including the answer key. */
  show: (trainingProgramId: number, type: TestType) =>
    apiClient
      .get<{ exists: boolean; test?: TrainingProgramTest }>(`/training-programs/${trainingProgramId}/tests/${type}`)
      .then((r) => r.data),
  upsert: (trainingProgramId: number, type: TestType, payload: TestPayload) =>
    apiClient.put<TrainingProgramTest>(`/training-programs/${trainingProgramId}/tests/${type}`, payload).then((r) => r.data),
  remove: (trainingProgramId: number, type: TestType) => apiClient.delete(`/training-programs/${trainingProgramId}/tests/${type}`),
}

export const traineeTestApi = {
  /** Trainee: fetch the test for one of their own training attendances — never includes the answer key. */
  show: (trainingHistoryId: number, type: TestType) =>
    apiClient
      .get<{
        exists: boolean
        test?: { id: number; type: TestType; title: string; instructions: string | null; passing_score: number; questions: TestQuestion[] }
        latest_attempt?: TestAttemptResult | null
      }>(`/trainee/me/training-history/${trainingHistoryId}/tests/${type}`)
      .then((r) => r.data),
  attempt: (trainingHistoryId: number, type: TestType, answers: Record<number, string>) =>
    apiClient
      .post<{ attempt: TestAttemptResult; passing_score: number }>(
        `/trainee/me/training-history/${trainingHistoryId}/tests/${type}/attempt`,
        { answers },
      )
      .then((r) => r.data),
}
