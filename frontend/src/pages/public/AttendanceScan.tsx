import { useEffect, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { attendanceApi } from '../../api/attendance'
import { extractErrorMessage } from '../../api/client'
import { useAuth } from '../../context/AuthContext'
import { Button } from '../../components/ui'

export default function AttendanceScan() {
  const [searchParams] = useSearchParams()
  const token = searchParams.get('token') ?? ''
  const { user, isAuthenticated, isLoading } = useAuth()

  const [status, setStatus] = useState<'idle' | 'working' | 'done' | 'error'>('idle')
  const [message, setMessage] = useState<string | null>(null)
  const [programName, setProgramName] = useState<string | null>(null)

  useEffect(() => {
    if (isLoading || !isAuthenticated || user?.role !== 'trainee' || !token || status !== 'idle') return

    setStatus('working')
    attendanceApi
      .scan(token)
      .then((history) => {
        setProgramName(history.training_program?.name ?? null)
        setStatus('done')
      })
      .catch((err) => {
        setMessage(extractErrorMessage(err, 'Could not record your attendance. The code may have expired.'))
        setStatus('error')
      })
  }, [isLoading, isAuthenticated, user, token, status])

  return (
    <div className="flex min-h-screen items-center justify-center bg-slate-50 px-4">
      <div className="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-brand-600 mx-auto">
          <img src="/logo.png" alt="Digital Trainee Tracker logo" className="h-10 w-10 object-contain" />
        </div>
        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-700">Digital Trainee Tracker</p>
        <h1 className="mt-2 text-lg font-semibold text-slate-900">Training Attendance</h1>

        {!token && (
          <p className="mt-6 text-sm text-red-600">This link is missing an attendance code. Please scan the QR code again.</p>
        )}

        {token && isLoading && <p className="mt-6 text-sm text-slate-500">Checking your session…</p>}

        {token && !isLoading && !isAuthenticated && (
          <div className="mt-6 space-y-3">
            <p className="text-sm text-slate-600">Sign in as the trainee to record your attendance.</p>
            <Link to={`/login?next=${encodeURIComponent(`/attend?token=${token}`)}`}>
              <Button className="w-full justify-center">Sign In</Button>
            </Link>
          </div>
        )}

        {token && !isLoading && isAuthenticated && user?.role !== 'trainee' && (
          <p className="mt-6 text-sm text-red-600">Only trainee accounts can record attendance this way.</p>
        )}

        {token && !isLoading && isAuthenticated && user?.role === 'trainee' && status === 'working' && (
          <p className="mt-6 text-sm text-slate-500">Recording your attendance…</p>
        )}

        {status === 'done' && (
          <div className="mt-6 space-y-2">
            <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-2xl text-green-700">✓</div>
            <p className="text-base font-semibold text-green-700">Attendance recorded!</p>
            {programName && <p className="text-sm text-slate-600">{programName}</p>}
          </div>
        )}

        {status === 'error' && (
          <div className="mt-6 space-y-2">
            <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-2xl text-red-700">✕</div>
            <p className="text-sm text-red-600">{message}</p>
          </div>
        )}
      </div>
    </div>
  )
}
