import { useEffect, useRef } from 'react'

declare global {
  interface Window {
    google?: {
      accounts: {
        id: {
          initialize: (config: { client_id: string; callback: (response: { credential: string }) => void }) => void
          renderButton: (parent: HTMLElement, options: Record<string, unknown>) => void
        }
      }
    }
  }
}

/**
 * Renders Google's own "Sign in with Google" button via Google Identity
 * Services (loaded in index.html). Silently renders nothing if
 * VITE_GOOGLE_CLIENT_ID hasn't been configured for this deployment yet.
 */
export function GoogleSignInButton({ onCredential }: { onCredential: (credential: string) => void }) {
  const containerRef = useRef<HTMLDivElement>(null)
  const clientId = import.meta.env.VITE_GOOGLE_CLIENT_ID as string | undefined

  useEffect(() => {
    if (!clientId || !containerRef.current) return

    let cancelled = false
    let pollTimer: ReturnType<typeof setInterval> | undefined

    function render() {
      if (cancelled || !window.google || !containerRef.current) return
      window.google.accounts.id.initialize({
        client_id: clientId as string,
        callback: (response) => onCredential(response.credential),
      })
      window.google.accounts.id.renderButton(containerRef.current, {
        type: 'standard',
        theme: 'outline',
        size: 'large',
        width: 320,
        text: 'continue_with',
      })
    }

    if (window.google) {
      render()
    } else {
      pollTimer = setInterval(() => {
        if (window.google) {
          clearInterval(pollTimer)
          render()
        }
      }, 100)
    }

    return () => {
      cancelled = true
      if (pollTimer) clearInterval(pollTimer)
    }
  }, [clientId, onCredential])

  if (!clientId) return null

  return <div ref={containerRef} className="flex justify-center" />
}
