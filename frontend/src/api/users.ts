import { apiClient } from './client'
import type { PaginatedResponse, Role, User } from '../types'

export interface UserFilters {
  role?: Role
  q?: string
  page?: number
  per_page?: number
}

export interface UserPayload {
  name: string
  username: string
  email: string
  role: Role
  password?: string
  is_active?: boolean
}

export const usersApi = {
  list: (filters: UserFilters = {}) =>
    apiClient
      .get<PaginatedResponse<User>>('/admin/users', {
        params: Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== undefined && v !== '')),
      })
      .then((r) => r.data),
  create: (payload: UserPayload) => apiClient.post<{ data: User }>('/admin/users', payload).then((r) => r.data.data),
  update: (id: number, payload: Partial<UserPayload>) =>
    apiClient.put<{ data: User }>(`/admin/users/${id}`, payload).then((r) => r.data.data),
  activate: (id: number) => apiClient.post<{ data: User }>(`/admin/users/${id}/activate`).then((r) => r.data.data),
  deactivate: (id: number) => apiClient.post<{ data: User }>(`/admin/users/${id}/deactivate`).then((r) => r.data.data),
  remove: (id: number) => apiClient.delete(`/admin/users/${id}`),
}
