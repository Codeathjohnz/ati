import { apiClient } from './client'
import type { ActivityLogEntry, DashboardStats, RequiredDocument } from '../types'

export const adminApi = {
  dashboard: () => apiClient.get<DashboardStats>('/dashboard').then((r) => r.data),
  settings: () => apiClient.get<Record<string, string>>('/admin/settings').then((r) => r.data),
  updateSettings: (settings: Record<string, string>) =>
    apiClient.put<Record<string, string>>('/admin/settings', { settings }).then((r) => r.data),
  auditLogs: (params: { page?: number; per_page?: number; action?: string } = {}) =>
    apiClient
      .get<{ data: ActivityLogEntry[]; meta: { current_page: number; last_page: number; total: number } }>(
        '/admin/audit-logs',
        { params },
      )
      .then((r) => r.data),
  requiredDocuments: () => apiClient.get<RequiredDocument[]>('/admin/required-documents').then((r) => r.data),
  createRequiredDocument: (payload: { code: string; name: string; sort_order?: number }) =>
    apiClient.post<RequiredDocument>('/admin/required-documents', payload).then((r) => r.data),
  updateRequiredDocument: (id: number, payload: { code: string; name: string; is_active?: boolean; sort_order?: number }) =>
    apiClient.put<RequiredDocument>(`/admin/required-documents/${id}`, payload).then((r) => r.data),
  removeRequiredDocument: (id: number) => apiClient.delete(`/admin/required-documents/${id}`),
  storageUsage: () => apiClient.get<StorageUsage>('/admin/storage').then((r) => r.data),
}

export interface StorageConsumer {
  label: string
  path: string
  size_bytes: number
  percent_of_app: number
}

export interface StorageUsage {
  generated_at: string
  filesystem: {
    path: string
    total_bytes: number | null
    used_bytes: number | null
    free_bytes: number | null
    used_percent: number | null
  }
  app_total_bytes: number
  top_consumers: StorageConsumer[]
  other_bytes: number
}
