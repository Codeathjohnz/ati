import { useQuery } from '@tanstack/react-query'
import { adminApi } from '../../api/admin'
import { Card, CardHeader, ErrorBlock, LoadingBlock, StatTile } from '../../components/ui'
import { AttendanceTrendChart, CategoryBarList, GapCoverageChart, ProportionBar } from '../../components/charts'
import { categorical, chainLevelOrder, programStatusColor, roleColor, sexColor, status } from '../../lib/chartColors'
import { extractErrorMessage } from '../../api/client'

export default function Dashboard({ title = 'Dashboard' }: { title?: string }) {
  const { data, isLoading, error } = useQuery({ queryKey: ['dashboard'], queryFn: adminApi.dashboard })

  if (isLoading) return <LoadingBlock label="Loading dashboard…" />
  if (error || !data) return <ErrorBlock message={extractErrorMessage(error, 'Could not load dashboard data.')} />

  // Chain-level rows in the paper's fixed order, zero-filled so a level with
  // no attendances yet still appears instead of silently disappearing.
  const chainLevelVolume = chainLevelOrder.map((level) => ({
    label: level,
    value: data.trainings_by_chain_level[level] ?? 0,
    color: categorical.blue,
  }))

  const programStatusItems = Object.entries(data.trainings_by_status).map(([label, value]) => ({
    label,
    value,
    color: programStatusColor[label] ?? categorical.blue,
  }))

  const roleItems = Object.entries(data.users_by_role).map(([label, value]) => ({
    label,
    value,
    color: roleColor[label] ?? categorical.blue,
  }))

  const sexSegments = Object.entries(data.trainees_by_sex).map(([label, value]) => ({
    label,
    value,
    color: sexColor[label] ?? categorical.blue,
  }))

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-semibold text-slate-900">{title}</h1>
        <p className="text-sm text-slate-500">Real-time training attendance and document compliance across ATI-Caraga.</p>
      </div>

      <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
        <StatTile label="Total Trainees" value={data.total_trainees} />
        <StatTile label="Training Programs" value={data.total_training_programs} />
        <StatTile label="Total Attendances" value={data.total_attendances} />
        <StatTile
          label="Document Submission Rate"
          value={`${data.document_compliance.submission_rate}%`}
          hint={`${data.document_compliance.submitted} submitted / ${data.document_compliance.missing} missing`}
        />
      </div>

      <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <Card>
          <CardHeader
            title="Competency Gap Coverage"
            subtitle="Trainees who have vs. haven't completed each chain level, institution-wide."
          />
          <GapCoverageChart data={data.competency_gap_coverage} />
        </Card>

        <Card>
          <CardHeader title="Training Attendance Trend" subtitle="Attendance records logged per month, last 6 months." />
          <div className="px-2 pb-4 pt-2">
            <AttendanceTrendChart data={data.attendance_trend} />
          </div>
        </Card>
      </div>

      <div className="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <Card>
          <CardHeader title="Trainings by Chain Level" subtitle="Attendance volume per chain level." />
          <CategoryBarList items={chainLevelVolume} />
        </Card>

        <Card>
          <CardHeader title="Training Programs by Status" />
          <CategoryBarList items={programStatusItems} />
        </Card>

        <Card>
          <CardHeader title="Users by Role" />
          <CategoryBarList items={roleItems} />
        </Card>
      </div>

      <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <Card>
          <CardHeader title="Document Compliance" subtitle="Every required document tracked, across all trainings." />
          <ProportionBar
            segments={[
              { label: 'Submitted', value: data.document_compliance.submitted, color: status.good },
              { label: 'Missing', value: data.document_compliance.missing, color: status.critical },
            ]}
          />
        </Card>

        <Card>
          <CardHeader title="Trainee Demographics — Sex" />
          <ProportionBar segments={sexSegments} />
        </Card>
      </div>

      <Card>
        <CardHeader title="Recently Registered Trainees" />
        <div className="divide-y divide-slate-100">
          {data.recent_trainees.length === 0 && <p className="px-5 py-6 text-sm text-slate-400">No trainees registered yet.</p>}
          {data.recent_trainees.map((t) => (
            <div key={t.id} className="flex items-center justify-between px-5 py-3 text-sm">
              <span className="font-medium text-slate-800">
                {t.first_name} {t.last_name}
              </span>
              <span className="text-slate-400">{new Date(t.created_at).toLocaleDateString()}</span>
            </div>
          ))}
        </div>
      </Card>
    </div>
  )
}
