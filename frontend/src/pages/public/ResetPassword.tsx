import { useState, type FormEvent } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { authApi } from '../../api/auth'
import { extractErrorMessage } from '../../api/client'
import { Button, ErrorBlock } from '../../components/ui'

export default function ResetPassword() {
  const [searchParams] = useSearchParams()
  const token = searchParams.get('token') ?? ''
  const email = searchParams.get('email') ?? ''

  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [done, setDone] = useState(false)

  const linkIsValid = Boolean(token && email)

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setError(null)
    setSubmitting(true)
    try {
      await authApi.resetPassword({ token, email, password, password_confirmation: passwordConfirmation })
      setDone(true)
    } catch (err) {
      setError(extractErrorMessage(err, 'Could not reset your password. The link may have expired.'))
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-slate-50 px-4">
      <div className="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <div className="mb-6 flex flex-col items-center text-center">
          <div className="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-brand-600 shadow-sm shadow-brand-600/30">
            <img src="/logo.png" alt="Digital Trainee Tracker logo" className="h-10 w-10 object-contain" />
          </div>
          <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-700">Digital Trainee Tracker</p>
          <h1 className="mt-2 text-xl font-semibold text-slate-900">Reset your password</h1>
          {email && <p className="mt-1 text-sm text-slate-500">for {email}</p>}
        </div>

        {!linkIsValid && (
          <ErrorBlock message="This reset link is missing some information. Please request a new one." />
        )}

        {linkIsValid && done && (
          <div className="rounded-lg border border-brand-200 bg-brand-50 p-4 text-center text-sm text-brand-800">
            Your password has been reset.{' '}
            <Link to="/login" className="font-semibold underline">
              Sign in now
            </Link>
            .
          </div>
        )}

        {linkIsValid && !done && (
          <form onSubmit={handleSubmit} className="space-y-4">
            {error && <ErrorBlock message={error} />}
            <div>
              <label htmlFor="reset-password" className="mb-1 block text-xs font-medium text-slate-600">
                New Password
              </label>
              <input
                id="reset-password"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                autoFocus
                required
                minLength={8}
                placeholder="••••••••"
                className="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
              />
            </div>
            <div>
              <label htmlFor="reset-password-confirmation" className="mb-1 block text-xs font-medium text-slate-600">
                Confirm New Password
              </label>
              <input
                id="reset-password-confirmation"
                type="password"
                value={passwordConfirmation}
                onChange={(e) => setPasswordConfirmation(e.target.value)}
                required
                minLength={8}
                placeholder="••••••••"
                className="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
              />
            </div>
            <Button type="submit" className="w-full justify-center" disabled={submitting}>
              {submitting ? 'Resetting…' : 'Reset Password'}
            </Button>
          </form>
        )}

        <p className="mt-6 text-center text-xs text-slate-400">
          <Link to="/login" className="font-medium text-brand-700 hover:underline">
            Back to Sign In
          </Link>
        </p>
      </div>
    </div>
  )
}
