import { useState } from 'react'
import { reportsApi } from '../../api/reports'
import { Button, Card, CardHeader } from '../../components/ui'

const REPORTS: Array<{ key: keyof typeof reportsApi; title: string; description: string }> = [
  { key: 'attendance', title: 'Training Attendance Report', description: 'Every trainee attendance record with chain level and date.' },
  { key: 'documentCompliance', title: 'Document Compliance Report', description: 'Overall Passed/Incomplete status per trainee across all trainings.' },
  { key: 'competencyGaps', title: 'Competency Gap Report', description: 'Incomplete chain levels per trainee for follow-up planning.' },
  { key: 'demographics', title: 'Trainee Demographics Report', description: 'Sex, age, location, income class, and education breakdown.' },
]

export default function Reports() {
  const [downloading, setDownloading] = useState<string | null>(null)

  async function handleDownload(key: keyof typeof reportsApi) {
    setDownloading(key)
    try {
      await reportsApi[key]()
    } finally {
      setDownloading(null)
    }
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-semibold text-slate-900">Data Export &amp; Reports</h1>
        <p className="text-sm text-slate-500">Generate CSV analytics for administrative planning and program evaluation.</p>
      </div>

      <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
        {REPORTS.map((report) => (
          <Card key={report.key}>
            <CardHeader title={report.title} subtitle={report.description} />
            <div className="p-5">
              <Button onClick={() => handleDownload(report.key)} disabled={downloading === report.key}>
                {downloading === report.key ? 'Preparing…' : 'Download CSV'}
              </Button>
            </div>
          </Card>
        ))}
      </div>
    </div>
  )
}
