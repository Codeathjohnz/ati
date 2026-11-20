import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { helpApi } from '../../api/help'
import { extractErrorMessage } from '../../api/client'
import { Button, Card, ErrorBlock, LoadingBlock } from '../../components/ui'

export default function HelpCenter() {
  const { data, isLoading, error } = useQuery({ queryKey: ['help', 'manual'], queryFn: helpApi.manual })
  const [downloading, setDownloading] = useState(false)

  async function handleDownload() {
    setDownloading(true)
    try {
      await helpApi.downloadManualPdf()
    } finally {
      setDownloading(false)
    }
  }

  if (isLoading) return <LoadingBlock />
  if (error || !data) return <ErrorBlock message={extractErrorMessage(error, 'Could not load the user manual.')} />

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-xl font-semibold text-slate-900">{data.title}</h1>
          <p className="text-sm text-slate-500">Read it here, or download a printable PDF copy.</p>
        </div>
        <Button onClick={handleDownload} disabled={downloading}>
          {downloading ? 'Preparing…' : '⬇️ Download PDF'}
        </Button>
      </div>

      <Card className="p-6">
        <div dangerouslySetInnerHTML={{ __html: data.html }} />
      </Card>
    </div>
  )
}
