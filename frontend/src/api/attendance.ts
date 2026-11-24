import { apiClient } from './client'
import type { TrainingHistoryRecord } from '../types'

export interface AttendanceQr {
  qr_data_uri: string
  attend_url: string
  expires_at: string
}

export const attendanceApi = {
  /** Officer/Admin: generate a scannable attendance QR for a training program. */
  generateQr: (trainingProgramId: number, hours = 24) =>
    apiClient
      .get<AttendanceQr>(`/training-programs/${trainingProgramId}/attendance-qr`, { params: { hours } })
      .then((r) => r.data),

  /** Trainee: redeem a scanned QR code's token, recording their own attendance. */
  scan: (token: string) =>
    apiClient.post<{ data: TrainingHistoryRecord }>('/trainee/me/attend', { token }).then((r) => r.data.data),
}
