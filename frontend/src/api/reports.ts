import { apiClient } from './client'

async function downloadBlob(path: string, filename: string) {
  const response = await apiClient.get(path, { responseType: 'blob' })
  const url = window.URL.createObjectURL(new Blob([response.data]))
  const link = document.createElement('a')
  link.href = url
  link.setAttribute('download', filename)
  document.body.appendChild(link)
  link.click()
  link.remove()
  window.URL.revokeObjectURL(url)
}

export const reportsApi = {
  attendance: () => downloadBlob('/reports/attendance', 'attendance-report.csv'),
  documentCompliance: () => downloadBlob('/reports/document-compliance', 'document-compliance-report.csv'),
  competencyGaps: () => downloadBlob('/reports/competency-gaps', 'competency-gap-report.csv'),
  demographics: () => downloadBlob('/reports/demographics', 'trainee-demographics-report.csv'),
}
