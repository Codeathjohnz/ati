import { Component, type ErrorInfo, type ReactNode } from 'react'
import { Button, Card } from './ui'

interface Props {
  children: ReactNode
}

interface State {
  error: Error | null
}

/**
 * Catches rendering errors in whatever page is currently mounted so a single
 * broken screen shows a recoverable message instead of a blank white page —
 * the sidebar (rendered outside this boundary, in AppShell) stays usable so
 * the user can navigate away without reloading.
 */
export class ErrorBoundary extends Component<Props, State> {
  state: State = { error: null }

  static getDerivedStateFromError(error: Error): State {
    return { error }
  }

  componentDidCatch(error: Error, info: ErrorInfo) {
    console.error('Unhandled error in page:', error, info.componentStack)
  }

  render() {
    if (this.state.error) {
      return (
        <Card className="mx-auto max-w-lg px-6 py-8 text-center">
          <p className="text-sm font-semibold text-slate-900">Something went wrong on this page.</p>
          <p className="mt-1.5 text-sm text-slate-500">
            {this.state.error.message || 'An unexpected error occurred.'}
          </p>
          <Button className="mt-5" onClick={() => window.location.reload()}>
            Reload page
          </Button>
        </Card>
      )
    }

    return this.props.children
  }
}
