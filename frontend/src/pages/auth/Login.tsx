import { useEffect, useState, type FormEvent } from 'react'
import { Navigate } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext'
import { extractErrorMessage } from '../../api/client'
import { Button, ErrorBlock } from '../../components/ui'

const REMEMBER_KEY = 'ati_remember_username'

const CHAIN_LEVELS: Array<{ name: string; blurb: string }> = [
  { name: 'Production', blurb: 'Building strong foundations in farm productivity' },
  { name: 'Post-Harvest', blurb: 'Reducing losses, preserving quality after the harvest' },
  { name: 'Processing', blurb: 'Turning raw harvest into market-ready goods' },
  { name: 'Marketing', blurb: 'Connecting produce to profitable markets' },
  { name: 'Consumption', blurb: 'Promoting food security and healthy choices' },
]

const FEATURES = [
  { label: 'Trainee Profiling', icon: <UsersIcon /> },
  { label: 'Document Tracking', icon: <DocIcon /> },
  { label: 'Competency Gaps', icon: <ChartIcon /> },
]

const PARTICLES = [
  { left: '8%', size: 14, duration: 13, delay: 0 },
  { left: '20%', size: 10, duration: 16, delay: 2.5 },
  { left: '34%', size: 16, duration: 12, delay: 5 },
  { left: '52%', size: 11, duration: 15, delay: 1.5 },
  { left: '67%', size: 13, duration: 14, delay: 4 },
  { left: '80%', size: 9, duration: 17, delay: 6.5 },
  { left: '92%', size: 15, duration: 13.5, delay: 3 },
]

export default function Login() {
  const { login, isAuthenticated, isLoading } = useAuth()
  const [username, setUsername] = useState(() => localStorage.getItem(REMEMBER_KEY) ?? '')
  const [password, setPassword] = useState('')
  const [remember, setRemember] = useState(() => Boolean(localStorage.getItem(REMEMBER_KEY)))
  const [showPassword, setShowPassword] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [submitting, setSubmitting] = useState(false)
  const [levelIndex, setLevelIndex] = useState(0)

  useEffect(() => {
    const timer = setInterval(() => setLevelIndex((i) => (i + 1) % CHAIN_LEVELS.length), 3200)
    return () => clearInterval(timer)
  }, [])

  if (!isLoading && isAuthenticated) return <Navigate to="/" replace />

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setError(null)
    setSubmitting(true)
    try {
      await login({ username, password })
      if (remember) localStorage.setItem(REMEMBER_KEY, username)
      else localStorage.removeItem(REMEMBER_KEY)
    } catch (err) {
      setError(extractErrorMessage(err, 'Invalid username or password.'))
    } finally {
      setSubmitting(false)
    }
  }

  const level = CHAIN_LEVELS[levelIndex]

  return (
    <div className="flex min-h-screen bg-white">
      {/* Hero panel */}
      <div className="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 px-10 py-10 text-white lg:flex xl:w-[56%] xl:px-16">
        {/* glow */}
        <div className="anim-glow pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-gold-400/40 blur-3xl" />
        <div className="pointer-events-none absolute -bottom-32 -left-16 h-72 w-72 rounded-full bg-brand-400/20 blur-3xl" />

        {/* drifting particles */}
        <div className="pointer-events-none absolute inset-0 overflow-hidden">
          {PARTICLES.map((p, i) => (
            <span
              key={i}
              className="anim-drift absolute bottom-0"
              style={{
                left: p.left,
                animationDuration: `${p.duration}s`,
                animationDelay: `${p.delay}s`,
              }}
            >
              <LeafIcon size={p.size} />
            </span>
          ))}
        </div>

        {/* terraced field illustration */}
        <svg
          className="pointer-events-none absolute inset-x-0 bottom-0 h-56 w-full opacity-90"
          viewBox="0 0 800 220"
          preserveAspectRatio="none"
          fill="none"
        >
          <path d="M0 140 Q200 90 400 130 T800 110 V220 H0 Z" fill="#14532d" fillOpacity="0.55" />
          <path d="M0 175 Q220 130 420 165 T800 150 V220 H0 Z" fill="#15803d" fillOpacity="0.6" />
          <path d="M0 205 Q240 175 460 195 T800 185 V220 H0 Z" fill="#16a34a" fillOpacity="0.55" />
        </svg>

        {/* content */}
        <div className="relative z-10 flex items-center gap-3 anim-fade-up" style={{ animationDelay: '0.05s' }}>
          <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 text-base font-bold backdrop-blur-sm ring-1 ring-white/20">
            AT
          </div>
          <div>
            <p className="text-sm font-semibold leading-tight tracking-wide">ATI-CARAGA</p>
            <p className="text-[11px] leading-tight text-white/70">Agricultural Training Institute</p>
          </div>
        </div>

        <div className="relative z-10">
          <h1
            className="anim-fade-up text-5xl font-semibold leading-[1.08] xl:text-6xl"
            style={{ fontFamily: 'var(--font-display)', animationDelay: '0.15s' }}
          >
            Cultivating
            <br />
            <span className="italic text-gold-300">Skilled Farmers.</span>
          </h1>
          <p className="anim-fade-up mt-5 max-w-md text-[15px] leading-relaxed text-white/75" style={{ animationDelay: '0.28s' }}>
            One platform to profile trainees, track training documents, and close competency gaps across every
            chain level in Caraga.
          </p>

          {/* cycling chain-level strip */}
          <div className="mt-8 h-16 border-l-2 border-gold-400/70 pl-4">
            <div key={levelIndex} className="anim-fade-up" style={{ animationDuration: '0.5s' }}>
              <p className="text-xs font-semibold uppercase tracking-[0.2em] text-gold-300">
                Chain Level · {String(levelIndex + 1).padStart(2, '0')}/{CHAIN_LEVELS.length}
              </p>
              <p className="mt-1 text-lg font-medium">{level.name}</p>
              <p className="text-sm text-white/65">{level.blurb}</p>
            </div>
          </div>
        </div>

        <div className="relative z-10 flex flex-wrap gap-3 anim-fade-up" style={{ animationDelay: '0.4s' }}>
          {FEATURES.map((f) => (
            <span
              key={f.label}
              className="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1.5 text-xs font-medium text-white/85 ring-1 ring-white/15 backdrop-blur-sm"
            >
              {f.icon}
              {f.label}
            </span>
          ))}
        </div>
      </div>

      {/* Form panel */}
      <div className="flex w-full flex-col items-center justify-center bg-slate-50 px-4 py-10 sm:px-8 lg:w-1/2 lg:bg-white xl:w-[44%]">
        <div className="w-full max-w-sm anim-fade-up" style={{ animationDelay: '0.1s' }}>
          <div className="mb-7 flex flex-col items-center text-center">
            <div className="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-brand-600 text-lg font-bold text-white shadow-sm shadow-brand-600/30">
              AT
            </div>
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-700">ATI-Caraga System</p>
            <h2 className="mt-2 text-2xl font-semibold text-slate-900" style={{ fontFamily: 'var(--font-display)' }}>
              Welcome Back
            </h2>
            <p className="mt-1 text-sm text-slate-500">Sign in to continue to your training portal</p>
          </div>

          <form onSubmit={handleSubmit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            {error && <ErrorBlock message={error} />}

            <div>
              <label htmlFor="login-username" className="mb-1 block text-xs font-medium text-slate-600">
                Username or Email
              </label>
              <div className="relative">
                <span className="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-slate-400">
                  <MailIcon />
                </span>
                <input
                  id="login-username"
                  value={username}
                  onChange={(e) => setUsername(e.target.value)}
                  autoFocus
                  required
                  placeholder="e.g. admin"
                  className="w-full rounded-lg border border-slate-300 py-2.5 pl-9 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                />
              </div>
            </div>

            <div>
              <label htmlFor="login-password" className="mb-1 block text-xs font-medium text-slate-600">
                Password
              </label>
              <div className="relative">
                <span className="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-slate-400">
                  <LockIcon />
                </span>
                <input
                  id="login-password"
                  type={showPassword ? 'text' : 'password'}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                  placeholder="••••••••"
                  className="w-full rounded-lg border border-slate-300 py-2.5 pl-9 pr-10 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword((v) => !v)}
                  className="absolute inset-y-0 right-0 flex w-9 items-center justify-center text-slate-400 hover:text-slate-600 focus:outline-none focus-visible:text-brand-600"
                  aria-label={showPassword ? 'Hide password' : 'Show password'}
                  aria-pressed={showPassword}
                >
                  {showPassword ? <EyeOffIcon /> : <EyeIcon />}
                </button>
              </div>
            </div>

            <label className="flex cursor-pointer items-center gap-2 text-xs text-slate-600">
              <input
                type="checkbox"
                checked={remember}
                onChange={(e) => setRemember(e.target.checked)}
                className="h-3.5 w-3.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
              />
              Remember my username
            </label>

            <Button type="submit" className="w-full justify-center gap-2 py-2.5" disabled={submitting}>
              {submitting ? (
                'Signing in…'
              ) : (
                <>
                  Sign In
                  <ArrowIcon />
                </>
              )}
            </Button>
          </form>

          <p className="mt-6 text-center text-xs text-slate-400">
            Need an account? Contact your Project Officer or System Administrator.
          </p>
          <p className="mt-2 text-center text-xs text-slate-400">
            Agricultural Training Institute (ATI) &mdash; Caraga Region
          </p>
        </div>
      </div>
    </div>
  )
}

function MailIcon() {
  return (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      <rect x="3" y="5" width="18" height="14" rx="2" />
      <path d="m3 7 9 6 9-6" />
    </svg>
  )
}

function LockIcon() {
  return (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      <rect x="4" y="10" width="16" height="10" rx="2" />
      <path d="M8 10V7a4 4 0 1 1 8 0v3" />
    </svg>
  )
}

function EyeIcon() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
      <circle cx="12" cy="12" r="3" />
    </svg>
  )
}

function EyeOffIcon() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
      <circle cx="12" cy="12" r="3" />
      <line x1="3" y1="21" x2="21" y2="3" />
    </svg>
  )
}

function ArrowIcon() {
  return (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M5 12h14" />
      <path d="m13 6 6 6-6 6" />
    </svg>
  )
}

function UsersIcon() {
  return (
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
      <circle cx="9" cy="7" r="4" />
      <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
      <path d="M16 3.13a4 4 0 0 1 0 7.75" />
    </svg>
  )
}

function DocIcon() {
  return (
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" />
      <path d="M14 2v6h6" />
      <path d="M9 15h6M9 11h2" />
    </svg>
  )
}

function ChartIcon() {
  return (
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M3 3v18h18" />
      <path d="M18 17V9M13 17V5M8 17v-4" />
    </svg>
  )
}

function LeafIcon({ size = 14 }: { size?: number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" className="text-gold-300/80">
      <path d="M11 20A7 7 0 0 1 4 13c0-6 7-11 15-11-1 8-6 15-11 18Z" />
      <path d="M4 20c4-4 7-7 15-15" />
    </svg>
  )
}
