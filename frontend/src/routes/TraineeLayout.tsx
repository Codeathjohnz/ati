import { Outlet } from 'react-router-dom'
import { AppShell } from '../components/AppShell'

const navItems = [
  { to: '/trainee', label: 'Dashboard', icon: '🏠' },
  { to: '/trainee/training-history', label: 'Training History', icon: '📚' },
  { to: '/trainee/documents', label: 'My Documents', icon: '📄' },
  { to: '/trainee/upcoming-training', label: 'Upcoming Training', icon: '✅' },
  { to: '/trainee/activity-history', label: 'Activity History', icon: '🕒' },
]

export default function TraineeLayout() {
  return (
    <AppShell navItems={navItems} badge="Trainee">
      <Outlet />
    </AppShell>
  )
}
