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

export interface ResetPasswordPayload {
  token: string
  email: string
  password: string
  password_confirmation: string
}

export const authApi = {
  login: (payload: LoginPayload) => apiClient.post<LoginResponse>('/auth/login', payload).then((r) => r.data),
  google: (credential: string) => apiClient.post<LoginResponse>('/auth/google', { credential }).then((r) => r.data),
  logout: () => apiClient.post('/auth/logout'),
  me: () => apiClient.get<{ user: User }>('/auth/me').then((r) => r.data.user),
  forgotPassword: (email: string) =>
    apiClient.post<{ message: string }>('/auth/forgot-password', { email }).then((r) => r.data),
  resetPassword: (payload: ResetPasswordPayload) =>
    apiClient.post<{ message: string }>('/auth/reset-password', payload).then((r) => r.data),
}
