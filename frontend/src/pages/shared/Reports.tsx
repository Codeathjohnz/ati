import { useState } from 'react'
import { reportsApi } from '../../api/reports'
import { Button, Card, CardHeader } from '../../components/ui'

const REPORTS: Array<{ key: keyof typeof reportsApi; pdfKey: keyof typeof reportsApi; title: string; description: string }> = [
  {
    key: 'attendance',
    pdfKey: 'attendancePdf',
    title: 'Training Attendance Report',
    description: 'Every trainee attendance record with chain level and date.',
  },
  {
    key: 'documentCompliance',
    pdfKey: 'documentCompliancePdf',
    title: 'Document Compliance Report',
    description: 'Overall Passed/Incomplete status per trainee across all trainings.',
  },
  {
    key: 'competencyGaps',
    pdfKey: 'competencyGapsPdf',
    title: 'Competency Gap Report',
    description: 'Incomplete chain levels per trainee for follow-up planning.',
  },
  {
    key: 'demographics',
    pdfKey: 'demographicsPdf',
    title: 'Trainee Demographics Report',
    description: 'Sex, age, location, income class, and education breakdown.',
  },
]

export default function Reports() {
  const [busy, setBusy] = useState<string | null>(null)

  async function run(key: keyof typeof reportsApi) {
    setBusy(key)
    try {
      await reportsApi[key]()
    } finally {
      setBusy(null)
    }
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-semibold text-slate-900">Data Export &amp; Reports</h1>
        <p className="text-sm text-slate-500">View, print, or export analytics for administrative planning and program evaluation.</p>
      </div>

      <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
        {REPORTS.map((report) => (
          <Card key={report.key}>
            <CardHeader title={report.title} subtitle={report.description} />
            <div className="flex flex-wrap gap-2 p-5">
              <Button onClick={() => run(report.pdfKey)} disabled={busy === report.pdfKey}>
                {busy === report.pdfKey ? 'Preparing…' : '👁️ View / Print'}
              </Button>
              <Button variant="secondary" onClick={() => run(report.key)} disabled={busy === report.key}>
                {busy === report.key ? 'Preparing…' : 'Download CSV'}
              </Button>
            </div>
          </Card>
        ))}
      </div>
    </div>
  )
}
