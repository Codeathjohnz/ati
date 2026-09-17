import { apiClient } from './client'
import type { User } from '../types'

export interface LoginPayload {
  username: string
  password: string
}

export interface LoginResponse {
  token: string
  user: User
}

export const authApi = {
  login: (payload: LoginPayload) => apiClient.post<LoginResponse>('/auth/login', payload).then((r) => r.data),
  logout: () => apiClient.post('/auth/logout'),
  me: () => apiClient.get<{ user: User }>('/auth/me').then((r) => r.data.user),
}
