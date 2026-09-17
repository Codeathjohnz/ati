import { Navigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { LoadingBlock } from '../components/ui'

/** Sends an authenticated user to their role's home page. */
export default function RoleRedirect() {
  const { user, isLoading } = useAuth()

  if (isLoading) return <LoadingBlock label="Loading…" />
  if (!user) return <Navigate to="/login" replace />

  const destinations: Record<string, string> = {
    admin: '/admin',
    project_officer: '/officer',
    trainee: '/trainee',
  }

  return <Navigate to={destinations[user.role] ?? '/login'} replace />
}
