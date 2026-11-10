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

/**
 * Opens the PDF in a new tab via a blob URL — the browser's own PDF viewer
 * then gives View, Print, and Download all at once with no extra UI needed.
 */
async function viewPdf(path: string) {
  const response = await apiClient.get(path, { responseType: 'blob' })
  const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }))
  window.open(url, '_blank')
}

export const reportsApi = {
  attendance: () => downloadBlob('/reports/attendance', 'attendance-report.csv'),
  attendancePdf: () => viewPdf('/reports/attendance/pdf'),
  documentCompliance: () => downloadBlob('/reports/document-compliance', 'document-compliance-report.csv'),
  documentCompliancePdf: () => viewPdf('/reports/document-compliance/pdf'),
  competencyGaps: () => downloadBlob('/reports/competency-gaps', 'competency-gap-report.csv'),
  competencyGapsPdf: () => viewPdf('/reports/competency-gaps/pdf'),
  demographics: () => downloadBlob('/reports/demographics', 'trainee-demographics-report.csv'),
  demographicsPdf: () => viewPdf('/reports/demographics/pdf'),
}
