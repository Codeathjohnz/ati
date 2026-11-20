import { apiClient } from './client'

export interface ManualResponse {
  title: string
  html: string
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

export const helpApi = {
  manual: () => apiClient.get<ManualResponse>('/help/manual').then((r) => r.data),
  downloadManualPdf: () => downloadBlob('/help/manual/pdf', 'user-manual.pdf'),
}
