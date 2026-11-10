import { useState, type FormEvent } from 'react'
import { Link } from 'react-router-dom'
import { authApi } from '../../api/auth'
import { extractErrorMessage } from '../../api/client'
import { Button, ErrorBlock } from '../../components/ui'

export default function ForgotPassword() {
  const [email, setEmail] = useState('')
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [sent, setSent] = useState(false)

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setError(null)
    setSubmitting(true)
    try {
      await authApi.forgotPassword(email)
      setSent(true)
    } catch (err) {
      setError(extractErrorMessage(err, 'Something went wrong. Please try again.'))
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
          <h1 className="mt-2 text-xl font-semibold text-slate-900">Forgot your password?</h1>
          <p className="mt-1 text-sm text-slate-500">
            Enter the email on your account and we'll send you a link to reset it.
          </p>
        </div>

        {sent ? (
          <div className="rounded-lg border border-brand-200 bg-brand-50 p-4 text-center text-sm text-brand-800">
            If that email matches an account, a reset link is on its way. Check your inbox (and spam folder).
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="space-y-4">
            {error && <ErrorBlock message={error} />}
            <div>
              <label htmlFor="forgot-email" className="mb-1 block text-xs font-medium text-slate-600">
                Email
              </label>
              <input
                id="forgot-email"
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                autoFocus
                required
                placeholder="you@example.com"
                className="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
              />
            </div>
            <Button type="submit" className="w-full justify-center" disabled={submitting}>
              {submitting ? 'Sending…' : 'Send Reset Link'}
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
