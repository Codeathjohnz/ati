import { apiClient } from './client'

export interface CertificateChainLevel {
  chain_level: string
  date_attended: string | null
}

export interface Certificate {
  id: number
  trainee_id: number
  training_program_id: number | null
  certificate_number: string
  issued_at: string
  chain_levels: CertificateChainLevel[]
}

export interface VerificationResult {
  valid: boolean
  certificate_number?: string
  trainee_name?: string
  training_program_name?: string
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
  /**
   * A trainee earns one certificate per training program, once they've
   * registered, attended, passed its pre/post-tests (if published), and
   * submitted its evaluation — not a single all-chain-levels certificate.
   */
  mine: (trainingProgramId: number) =>
    apiClient.get<Certificate>(`/trainee/me/training-programs/${trainingProgramId}/certificate`).then((r) => r.data),
  downloadMine: (trainingProgramId: number) =>
    downloadBlob(`/trainee/me/training-programs/${trainingProgramId}/certificate/download`, 'certificate.pdf'),

  /** Officer/Admin: a specific trainee's certificate for a specific training program. */
  forTrainee: (traineeId: number, trainingProgramId: number) =>
    apiClient.get<Certificate>(`/trainees/${traineeId}/training-programs/${trainingProgramId}/certificate`).then((r) => r.data),
  download: (traineeId: number, trainingProgramId: number) =>
    downloadBlob(`/trainees/${traineeId}/training-programs/${trainingProgramId}/certificate/download`, 'certificate.pdf'),

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
