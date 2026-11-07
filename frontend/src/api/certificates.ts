import { apiClient } from './client'

export interface CertificateChainLevel {
  chain_level: string
  date_attended: string | null
}

export interface Certificate {
  id: number
  trainee_id: number
  certificate_number: string
  issued_at: string
  chain_levels: CertificateChainLevel[]
}

export interface VerificationResult {
  valid: boolean
  certificate_number?: string
  trainee_name?: string
  issued_at?: string
  chain_levels?: CertificateChainLevel[]
}

async function downloadBlob(path: string, filenameHint: string) {
  const response = await apiClient.get(path, { responseType: 'blob' })
  const url = URL.createObjectURL(response.data as Blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filenameHint
  link.click()
  URL.revokeObjectURL(url)
}

export const certificatesApi = {
  /** Trainee: their own certificate — 404 (via extractErrorMessage) until they've closed every chain level. */
  mine: () => apiClient.get<Certificate>('/trainee/me/certificate').then((r) => r.data),
  downloadMine: () => downloadBlob('/trainee/me/certificate/download', 'certificate.pdf'),

  /** Officer/Admin: a specific trainee's certificate. */
  forTrainee: (traineeId: number) => apiClient.get<Certificate>(`/trainees/${traineeId}/certificate`).then((r) => r.data),
  download: (traineeId: number) => downloadBlob(`/trainees/${traineeId}/certificate/download`, 'certificate.pdf'),

  /** Public — no auth required; what the QR code on a certificate points at. */
  verify: (certificateNumber: string) =>
    apiClient
      .get<VerificationResult>(`/verify/${encodeURIComponent(certificateNumber)}`)
      .then((r) => r.data)
      .catch((err) => {
        if (err?.response?.status === 404) return { valid: false } as VerificationResult
        throw err
      }),
}
