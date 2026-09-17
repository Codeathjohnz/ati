import { Outlet } from 'react-router-dom'
import { AppShell } from '../components/AppShell'

const navItems = [
  { to: '/officer', label: 'Dashboard', icon: '📊' },
  { to: '/officer/trainees', label: 'Trainees', icon: '🧑‍🌾' },
  { to: '/officer/training-programs', label: 'Training Programs', icon: '📅' },
  { to: '/officer/competency-gaps', label: 'Competency Gaps', icon: '🎯' },
  { to: '/officer/upcoming-confirmations', label: 'Upcoming Confirmations', icon: '✅' },
  { to: '/officer/reports', label: 'Reports', icon: '📁' },
]

export default function OfficerLayout() {
  return (
    <AppShell navItems={navItems} badge="Project Officer">
      <Outlet />
    </AppShell>
  )
}
