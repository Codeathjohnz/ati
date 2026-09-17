import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { traineeSelfApi } from '../../api/traineeSelf'
import { documentsApi } from '../../api/documents'
import { competencyGapsApi } from '../../api/competencyGaps'
import { extractErrorMessage } from '../../api/client'
import { Card, CardHeader, ComplianceBadge, ErrorBlock, LoadingBlock, StatTile } from '../../components/ui'

export default function TraineeDashboard() {
  const { data: profile, isLoading, error } = useQuery({ queryKey: ['trainee', 'me'], queryFn: traineeSelfApi.profile })
  const { data: docs } = useQuery({ queryKey: ['trainee', 'me', 'documents'], queryFn: documentsApi.myCompliance })
  const { data: gaps } = useQuery({ queryKey: ['trainee', 'me', 'gaps'], queryFn: competencyGapsApi.mine })

  if (isLoading) return <LoadingBlock />
  if (error || !profile) return <ErrorBlock message={extractErrorMessage(error, 'Could not load your profile.')} />

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-semibold text-slate-900">Welcome, {profile.first_name}</h1>
        <p className="text-sm text-slate-500">Your training history, document status, and competency progress at a glance.</p>
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <StatTile label="Trainings Attended" value={profile.training_count ?? 0} />
        <StatTile label="Document Compliance" value={docs ? <ComplianceBadge status={docs.overall_status} /> : '—'} />
        <StatTile
          label="Competency Gaps"
          value={gaps ? gaps.gap_count : '—'}
          hint={gaps ? (gaps.has_gaps ? 'Chain levels remaining' : 'All chain levels complete') : undefined}
        />
      </div>

      <Card>
        <CardHeader title="Quick Links" />
        <div className="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2">
          <QuickLink to="/trainee/training-history" label="View Training History" />
          <QuickLink to="/trainee/documents" label="Check Document Status" />
          <QuickLink to="/trainee/upcoming-training" label="Confirm Upcoming Training" />
          <QuickLink to="/trainee/activity-history" label="View Full Activity Record" />
        </div>
      </Card>
    </div>
  )
}

function QuickLink({ to, label }: { to: string; label: string }) {
  return (
    <Link to={to} className="rounded-lg border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 hover:border-brand-300 hover:bg-brand-50">
      {label} →
    </Link>
  )
}
