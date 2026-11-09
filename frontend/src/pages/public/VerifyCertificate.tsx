import { useParams } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { certificatesApi } from '../../api/certificates'
import { LoadingBlock } from '../../components/ui'

export default function VerifyCertificate() {
  const { certificateNumber } = useParams<{ certificateNumber: string }>()

  const { data, isLoading } = useQuery({
    queryKey: ['verify', certificateNumber],
    queryFn: () => certificatesApi.verify(certificateNumber ?? ''),
    enabled: !!certificateNumber,
  })

  return (
    <div className="flex min-h-screen items-center justify-center bg-slate-50 px-4">
      <div className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-brand-600 mx-auto">
          <img src="/logo.png" alt="Digital Trainee Tracker logo" className="h-10 w-10 object-contain" />
        </div>
        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-700">Digital Trainee Tracker</p>
        <h1 className="mt-2 text-lg font-semibold text-slate-900">Certificate Verification</h1>

        <div className="mt-6">
          {isLoading && <LoadingBlock label="Checking certificate…" />}

          {!isLoading && data?.valid && (
            <div className="space-y-4">
              <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-2xl text-green-700">✓</div>
              <p className="text-base font-semibold text-green-700">This certificate is genuine</p>
              <div className="rounded-lg border border-slate-200 bg-slate-50 p-4 text-left text-sm">
                <Row label="Issued to" value={data.trainee_name} />
                <Row label="Certificate No." value={data.certificate_number} />
                <Row label="Issued" value={data.issued_at} />
                <Row
                  label="Program"
                  value="Empowered & Bankable Farmer — all 5 agricultural value-chain levels completed"
                />
              </div>
              {data.chain_levels && data.chain_levels.length > 0 && (
                <div className="rounded-lg border border-slate-200 text-left text-sm">
                  {data.chain_levels.map((l) => (
                    <div key={l.chain_level} className="flex justify-between border-b border-slate-100 px-4 py-2 last:border-0">
                      <span className="text-slate-600">{l.chain_level}</span>
                      <span className="text-slate-800">{l.date_attended}</span>
                    </div>
                  ))}
                </div>
              )}
            </div>
          )}

          {!isLoading && data && !data.valid && (
            <div className="space-y-3">
              <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-2xl text-red-700">✕</div>
              <p className="text-base font-semibold text-red-700">Certificate not found</p>
              <p className="text-sm text-slate-500">
                No valid certificate matches "{certificateNumber}". Double-check the number, or it may not exist in our records.
              </p>
            </div>
          )}
        </div>

        <p className="mt-8 text-xs text-slate-400">Agricultural Training Institute (ATI) &mdash; Caraga Region</p>
      </div>
    </div>
  )
}

function Row({ label, value }: { label: string; value?: string }) {
  return (
    <div className="flex items-start justify-between gap-3 border-b border-slate-200 py-2 last:border-0">
      <span className="shrink-0 text-slate-500">{label}</span>
      <span className="text-right font-medium text-slate-800">{value}</span>
    </div>
  )
}
