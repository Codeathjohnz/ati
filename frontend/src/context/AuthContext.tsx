import { createContext, useCallback, useContext, useEffect, useState, type ReactNode } from 'react'
import { authApi, type LoginPayload } from '../api/auth'
import { getStoredToken, registerUnauthorizedHandler, setStoredToken } from '../api/client'
import type { User } from '../types'

interface AuthContextValue {
  user: User | null
  isLoading: boolean
  isAuthenticated: boolean
  login: (payload: LoginPayload) => Promise<User>
  logout: () => Promise<void>
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [isLoading, setIsLoading] = useState(true)

  const clearSession = useCallback(() => {
    setStoredToken(null)
    setUser(null)
  }, [])

  useEffect(() => {
    registerUnauthorizedHandler(clearSession)
  }, [clearSession])

  useEffect(() => {
    const token = getStoredToken()
    if (!token) {
      setIsLoading(false)
      return
    }

    authApi
      .me()
      .then(setUser)
      .catch(() => setStoredToken(null))
      .finally(() => setIsLoading(false))
  }, [])

  const login = useCallback(async (payload: LoginPayload) => {
    const { token, user: loggedInUser } = await authApi.login(payload)
    setStoredToken(token)
    setUser(loggedInUser)
    return loggedInUser
  }, [])

  const logout = useCallback(async () => {
    try {
      await authApi.logout()
    } finally {
      clearSession()
    }
  }, [clearSession])

  return (
    <AuthContext.Provider value={{ user, isLoading, isAuthenticated: !!user, login, logout }}>
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within an AuthProvider')
  return ctx
}
