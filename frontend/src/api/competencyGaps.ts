import { apiClient } from './client'
import type { CompetencyGapSummary, TraineeProfile } from '../types'

export interface CompetencyGapListRow {
  trainee: TraineeProfile
  gap_count: number
  gaps: string[]
}

export interface CompetencyGapListFilters {
  province?: string
  city_municipality?: string
  sex?: string
  chain_level?: string
  page?: number
  per_page?: number
}

export const competencyGapsApi = {
  forTrainee: (traineeId: number) =>
    apiClient.get<CompetencyGapSummary>(`/trainees/${traineeId}/competency-gaps`).then((r) => r.data),
  list: (filters: CompetencyGapListFilters = {}) =>
    apiClient
      .get<{ data: CompetencyGapListRow[]; meta: { current_page: number; last_page: number; total: number } }>(
        '/competency-gaps',
        { params: Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== undefined && v !== '')) },
      )
      .then((r) => r.data),
  mine: () => apiClient.get<CompetencyGapSummary>('/trainee/me/competency-gaps').then((r) => r.data),
}
