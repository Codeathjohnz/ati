import { apiClient } from './client'
import type { ActivityHistory, TraineeProfile } from '../types'

export const traineeSelfApi = {
  profile: () => apiClient.get<{ data: TraineeProfile }>('/trainee/me').then((r) => r.data.data),
  activityHistory: () => apiClient.get<ActivityHistory>('/trainee/me/activity-history').then((r) => r.data),
}
