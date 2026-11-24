import { useEffect, useState } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

const TIPS: Record<string, string[]> = {
  '/trainee/documents': [
    'Each row is one training you attended — the badge shows whether that document still needs review.',
    'Click Upload to attach a file, or "Replace upload" if you already submitted one and need to fix it.',
  ],
  '/trainee/upcoming-training': [
    'Confirm or Decline each scheduled training as soon as you know your plans.',
    'Already confirmed but something came up? Use "Cancel (emergency)" to change your answer.',
  ],
  '/trainee/activity-history': [
    'This page shows your full record: documents, confirmations, and competency gaps in one place.',
  ],
  '/trainee/training-history': [
    'A Certificate button appears next to each training once you\'ve registered, attended, passed its tests, and submitted its evaluation.',
  ],
  '/trainee/available-trainings': [
    'Browse trainings your Project Officer has opened for registration and register your interest.',
  ],
  '/trainee': [
    'Your stat tiles summarize trainings attended, document compliance, and competency gaps at a glance.',
  ],
  '/officer/trainees': [
    'Click "+ Add Trainee" to register someone new, or click any row to open their full profile.',
  ],
  '/officer/training-programs': [
    'A program can cover more than one chain level — pick every level it actually teaches.',
    'Open it for trainee registration, and generate an Attendance QR for check-in day.',
  ],
  '/officer/competency-gaps': [
    'This shows every trainee\'s progress across the five chain levels at a glance.',
  ],
  '/officer/upcoming-confirmations': [
    'See who has confirmed, declined, or not yet responded for each scheduled training.',
  ],
  '/officer/reports': [
    'View / Print opens a PDF in a new tab — use your browser\'s own print or save icons from there.',
    'Download CSV gives you the raw data for spreadsheets.',
  ],
  '/admin/users': [
    'Deactivate an account instead of deleting it if you just need to suspend access temporarily.',
  ],
  '/admin/settings': [
    'Required Post-Training Documents can be toggled Active/Inactive without deleting them.',
  ],
  '/admin/storage': ['This shows live server disk usage — not sample data.'],
}

const DEFAULT_TIPS = ['Click the Help menu in the sidebar any time for the full step-by-step manual.']

function helpPathFor(role?: string) {
  if (role === 'admin') return '/admin/help'
  if (role === 'project_officer') return '/officer/help'
  return '/trainee/help'
}

function tipsFor(pathname: string): string[] {
  const match = Object.keys(TIPS)
    .filter((key) => pathname === key || pathname.startsWith(key + '/'))
    .sort((a, b) => b.length - a.length)[0]
  return match ? TIPS[match] : DEFAULT_TIPS
}

export function HelpBubble() {
  const [open, setOpen] = useState(false)
  const location = useLocation()
  const { user } = useAuth()
  const tips = tipsFor(location.pathname)

  useEffect(() => {
    if (!user) return
    try {
      const key = `help_bubble_seen_${user.id}`
      if (!localStorage.getItem(key)) {
        setOpen(true)
        localStorage.setItem(key, '1')
      }
    } catch {
      // Storage unavailable (private mode, blocked, etc.) — just skip auto-opening.
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [user?.id])

  return (
    <div className="fixed bottom-5 right-5 z-50">
      {open && (
        <div className="mb-3 w-72 rounded-xl border border-slate-200 bg-white p-4 text-left shadow-lg">
          <p className="text-xs font-semibold uppercase tracking-wide text-brand-700">Tips for this page</p>
          <ul className="mt-2 space-y-2 text-sm text-slate-600">
            {tips.map((tip, i) => (
              <li key={i} className="flex gap-1.5">
                <span className="text-brand-600">•</span>
                <span>{tip}</span>
              </li>
            ))}
          </ul>
          <Link
            to={helpPathFor(user?.role)}
            onClick={() => setOpen(false)}
            className="mt-3 block text-sm font-medium text-brand-700 hover:underline"
          >
            View full Help Center →
          </Link>
        </div>
      )}
      <button
        onClick={() => setOpen((v) => !v)}
        aria-label={open ? 'Close help' : 'Open help'}
        className="flex h-12 w-12 items-center justify-center rounded-full bg-brand-600 text-xl font-bold text-white shadow-lg hover:bg-brand-700"
      >
        {open ? '✕' : '?'}
      </button>
    </div>
  )
}
