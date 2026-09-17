import { apiClient } from './client'
import type { ActivityHistory, PaginatedResponse, TraineeProfile } from '../types'

export interface TraineeFilters {
  q?: string
  sex?: string
  province?: string
  city_municipality?: string
  lgu_income_class?: string
  educational_level?: string
  organization_affiliation?: string
  age_min?: number | string
  age_max?: number | string
  year_last_attended?: number | string
  page?: number
  per_page?: number
}

export interface TraineePayload {
  first_name: string
  middle_name?: string
  last_name: string
  suffix?: string
  sex: string
  birthdate?: string
  contact_number?: string
  email?: string
  address_line?: string
  barangay?: string
  city_municipality: string
  province: string
  region?: string
  lgu_income_class?: string
  educational_level?: string
  organization_affiliation?: string
  occupation?: string
  create_login?: boolean
  username?: string
  password?: string
}

function cleanParams(filters: TraineeFilters) {
  return Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== undefined && v !== ''))
}

export const traineesApi = {
  list: (filters: TraineeFilters = {}) =>
    apiClient
      .get<PaginatedResponse<TraineeProfile>>('/trainees', { params: cleanParams(filters) })
      .then((r) => r.data),
  get: (id: number) => apiClient.get<{ data: TraineeProfile }>(`/trainees/${id}`).then((r) => r.data.data),
  create: (payload: TraineePayload) =>
    apiClient.post<{ data: TraineeProfile }>('/trainees', payload).then((r) => r.data.data),
  update: (id: number, payload: Partial<TraineePayload>) =>
    apiClient.put<{ data: TraineeProfile }>(`/trainees/${id}`, payload).then((r) => r.data.data),
  remove: (id: number) => apiClient.delete(`/trainees/${id}`),
  linkAccount: (id: number, payload: { username: string; email?: string; password: string }) =>
    apiClient.post<{ data: TraineeProfile }>(`/trainees/${id}/link-account`, payload).then((r) => r.data.data),
  activityHistory: (id: number) => apiClient.get<ActivityHistory>(`/trainees/${id}/activity-history`).then((r) => r.data),
  activityHistoryPdf: (id: number) =>
    apiClient.get(`/trainees/${id}/activity-history/print`, { responseType: 'blob' }).then((r) => r.data as Blob),
}
