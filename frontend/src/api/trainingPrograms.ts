import { apiClient } from './client'
import type { PaginatedResponse, TrainingProgram } from '../types'

export interface TrainingProgramFilters {
  chain_level?: string
  status?: string
  q?: string
  page?: number
  per_page?: number
}

export interface TrainingProgramPayload {
  name: string
  description?: string
  chain_level: string
  venue?: string
  start_date: string
  end_date?: string
  status?: string
}

export const trainingProgramsApi = {
  list: (filters: TrainingProgramFilters = {}) =>
    apiClient
      .get<PaginatedResponse<TrainingProgram>>('/training-programs', {
        params: Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== undefined && v !== '')),
      })
      .then((r) => r.data),
  upcoming: () =>
    apiClient.get<{ data: TrainingProgram[] }>('/training-programs-upcoming').then((r) => r.data.data),
  get: (id: number) => apiClient.get<{ data: TrainingProgram }>(`/training-programs/${id}`).then((r) => r.data.data),
  create: (payload: TrainingProgramPayload) =>
    apiClient.post<{ data: TrainingProgram }>('/training-programs', payload).then((r) => r.data.data),
  update: (id: number, payload: Partial<TrainingProgramPayload>) =>
    apiClient.put<{ data: TrainingProgram }>(`/training-programs/${id}`, payload).then((r) => r.data.data),
  remove: (id: number) => apiClient.delete(`/training-programs/${id}`),
}
