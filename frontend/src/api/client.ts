import axios from 'axios'

const TOKEN_STORAGE_KEY = 'ati_caraga_token'

export const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api/v1',
  headers: {
    Accept: 'application/json',
  },
})

export function getStoredToken(): string | null {
  return localStorage.getItem(TOKEN_STORAGE_KEY)
}

export function setStoredToken(token: string | null) {
  if (token) {
    localStorage.setItem(TOKEN_STORAGE_KEY, token)
  } else {
    localStorage.removeItem(TOKEN_STORAGE_KEY)
  }
}

apiClient.interceptors.request.use((config) => {
  const token = getStoredToken()
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Central 401 handling: clear the stale token and let the AuthContext's
// bootstrap check redirect to /login on next render.
export function registerUnauthorizedHandler(onUnauthorized: () => void) {
  apiClient.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error?.response?.status === 401) {
        setStoredToken(null)
        onUnauthorized()
      }
      return Promise.reject(error)
    },
  )
}

export interface ApiErrorShape {
  message: string
  errors?: Record<string, string[]>
}

export function extractErrorMessage(error: unknown, fallback = 'Something went wrong.'): string {
  if (axios.isAxiosError(error)) {
    const data = error.response?.data as ApiErrorShape | undefined
    if (data?.errors) {
      const first = Object.values(data.errors)[0]?.[0]
      if (first) return first
    }
    if (data?.message) return data.message
  }
  return fallback
}
