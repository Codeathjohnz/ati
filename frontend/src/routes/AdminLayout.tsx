import { Outlet } from 'react-router-dom'
import { AppShell } from '../components/AppShell'

const navItems = [
  { to: '/admin', label: 'Dashboard', icon: '📊' },
  { to: '/admin/users', label: 'User Accounts', icon: '👥' },
  { to: '/admin/settings', label: 'Settings', icon: '⚙️' },
  { to: '/admin/audit-logs', label: 'Audit Logs', icon: '📜' },
  { to: '/admin/reports', label: 'Reports', icon: '📁' },
]

export default function AdminLayout() {
  return (
    <AppShell navItems={navItems} badge="Administrator">
      <Outlet />
    </AppShell>
  )
}
