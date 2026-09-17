import { apiClient } from './client'
import type { Location, MetaData } from '../types'

export const metaApi = {
  get: () => apiClient.get<MetaData>('/meta').then((r) => r.data),
  locations: () => apiClient.get<Location[]>('/meta/locations').then((r) => r.data),
}
