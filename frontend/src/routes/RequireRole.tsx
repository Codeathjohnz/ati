import type { ReactNode } from 'react'
import { Navigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import type { Role } from '../types'
import { LoadingBlock } from '../components/ui'

export function RequireRole({ roles, children }: { roles: Role[]; children: ReactNode }) {
  const { user, isLoading, isAuthenticated } = useAuth()

  if (isLoading) return <LoadingBlock label="Checking your session…" />
  if (!isAuthenticated || !user) return <Navigate to="/login" replace />
  if (!roles.includes(user.role)) return <Navigate to="/" replace />

  return <>{children}</>
}
