import { useState, type ReactNode } from 'react'
import { NavLink, useLocation } from 'react-router-dom'
import clsx from 'clsx'
import { useAuth } from '../context/AuthContext'
import { ErrorBoundary } from './ErrorBoundary'
import { Button, Modal } from './ui'

export interface NavItem {
  to: string
  label: string
  icon: ReactNode
}

const roleLabels: Record<string, string> = {
  admin: 'Administrator',
  project_officer: 'Project Officer',
  trainee: 'Trainee',
}

export function AppShell({ navItems, children, badge }: { navItems: NavItem[]; children: ReactNode; badge: string }) {
  const { user, logout } = useAuth()
  const [mobileOpen, setMobileOpen] = useState(false)
  const [confirmLogoutOpen, setConfirmLogoutOpen] = useState(false)
  const [loggingOut, setLoggingOut] = useState(false)
  const location = useLocation()

  const handleConfirmLogout = async () => {
    setLoggingOut(true)
    try {
      await logout()
    } finally {
      setLoggingOut(false)
      setConfirmLogoutOpen(false)
    }
  }

  return (
    <div className="flex min-h-screen bg-slate-50">
      {/* Sidebar */}
      <aside
        className={clsx(
          'fixed inset-y-0 left-0 z-40 w-64 transform border-r border-slate-200 bg-white transition-transform md:static md:translate-x-0',
          mobileOpen ? 'translate-x-0' : '-translate-x-full',
        )}
      >
        <div className="flex h-16 items-center gap-2 border-b border-slate-100 px-5">
          <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-sm font-bold text-white">DT</div>
          <div className="min-w-0">
            <p className="truncate text-sm font-semibold leading-tight text-slate-900">Digital Trainee Tracker</p>
            <p className="text-[11px] leading-tight text-slate-500">{badge}</p>
          </div>
        </div>
        <nav className="flex flex-col gap-1 p-3">
          {navItems.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              onClick={() => setMobileOpen(false)}
              className={({ isActive }) =>
                clsx(
                  'flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                  isActive ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
                )
              }
            >
              <span className="text-base">{item.icon}</span>
              {item.label}
            </NavLink>
          ))}
        </nav>
      </aside>

      {mobileOpen && (
        <div className="fixed inset-0 z-30 bg-slate-900/30 md:hidden" onClick={() => setMobileOpen(false)} />
      )}

      {/* Main */}
      <div className="flex min-w-0 flex-1 flex-col">
        <header className="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 md:px-6">
          <button className="rounded-md p-2 text-slate-500 hover:bg-slate-100 md:hidden" onClick={() => setMobileOpen(true)}>
            ☰
          </button>
          <div className="hidden md:block" />
          <div className="flex items-center gap-3">
            <div className="text-right">
              <p className="text-sm font-medium leading-tight text-slate-900">{user?.name}</p>
              <p className="text-xs leading-tight text-slate-500">{user ? roleLabels[user.role] : ''}</p>
            </div>
            <div className="flex h-9 w-9 items-center justify-center rounded-full bg-slate-200 text-sm font-semibold text-slate-600">
              {user?.name?.charAt(0).toUpperCase()}
            </div>
            <button
              onClick={() => setConfirmLogoutOpen(true)}
              className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50"
            >
              Log out
            </button>
          </div>
        </header>
        <main className="flex-1 overflow-x-hidden p-4 md:p-6">
          <ErrorBoundary key={location.pathname}>{children}</ErrorBoundary>
        </main>
      </div>

      <Modal open={confirmLogoutOpen} onClose={() => setConfirmLogoutOpen(false)} title="Log out?">
        <p className="text-sm text-slate-600">
          You'll need to sign in again to access your {user ? roleLabels[user.role] : ''} dashboard.
        </p>
        <div className="mt-5 flex justify-end gap-2">
          <Button variant="secondary" size="sm" onClick={() => setConfirmLogoutOpen(false)} disabled={loggingOut}>
            Cancel
          </Button>
          <Button variant="danger" size="sm" onClick={handleConfirmLogout} disabled={loggingOut}>
            {loggingOut ? 'Logging out…' : 'Log out'}
          </Button>
        </div>
      </Modal>
    </div>
  )
}
