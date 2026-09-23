import {
  Area,
  AreaChart,
  Bar,
  BarChart,
  CartesianGrid,
  Cell,
  LabelList,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts'
import { ink, status } from '../lib/chartColors'

const axisTick = { fill: ink.muted, fontSize: 12 }
const tooltipStyle = {
  background: '#ffffff',
  border: `1px solid ${ink.grid}`,
  borderRadius: 8,
  fontSize: 12,
  boxShadow: '0 4px 12px rgba(11,11,11,0.08)',
}

/**
 * Institution-wide competency coverage per chain level — a stacked horizontal
 * bar of Completed (status good) vs. Gap (status warning) counts, in the
 * paper's own fixed chain-level order. This is the one chart on the
 * dashboard that answers "which chain level most needs a follow-up
 * training" at a glance, matching the per-trainee rule in
 * CompetencyGapAnalysisService.
 */
export function GapCoverageChart({
  data,
}: {
  data: Array<{ chain_level: string; completed: number; gap: number }>
}) {
  const total = data.reduce((sum, d) => sum + d.completed + d.gap, 0)
  if (total === 0) {
    return <p className="px-5 py-10 text-center text-sm text-slate-400">No trainee or training data yet.</p>
  }

  return (
    <div>
      <div className="flex items-center gap-4 px-5 pt-1 text-xs text-slate-600">
        <Legend swatch={status.good} label="Complete" />
        <Legend swatch={status.warning} label="Gap" />
      </div>
      <ResponsiveContainer width="100%" height={230}>
        <BarChart data={data} layout="vertical" margin={{ top: 8, right: 28, bottom: 8, left: 8 }} barSize={18} barGap={2}>
          <CartesianGrid horizontal={false} stroke={ink.grid} />
          <XAxis type="number" tick={axisTick} axisLine={{ stroke: ink.baseline }} tickLine={false} allowDecimals={false} />
          <YAxis
            type="category"
            dataKey="chain_level"
            tick={axisTick}
            axisLine={{ stroke: ink.baseline }}
            tickLine={false}
            width={92}
          />
          <Tooltip
            cursor={{ fill: 'rgba(11,11,11,0.03)' }}
            contentStyle={tooltipStyle}
            formatter={(value, name) => [`${value} trainee${value === 1 ? '' : 's'}`, name]}
          />
          <Bar dataKey="completed" name="Complete" stackId="gap" fill={status.good} radius={[0, 0, 0, 0]}>
            <LabelList dataKey="completed" position="inside" fill="#ffffff" fontSize={11} formatter={(v) => (Number(v) > 0 ? String(v) : '')} />
          </Bar>
          <Bar dataKey="gap" name="Gap" stackId="gap" fill={status.warning} radius={[0, 4, 4, 0]}>
            <LabelList dataKey="gap" position="inside" fill="#ffffff" fontSize={11} formatter={(v) => (Number(v) > 0 ? String(v) : '')} />
          </Bar>
        </BarChart>
      </ResponsiveContainer>
    </div>
  )
}

/** Training attendances recorded per month, last 6 months — zero-filled. */
export function AttendanceTrendChart({ data }: { data: Array<{ month: string; total: number }> }) {
  const hasActivity = data.some((d) => d.total > 0)
  if (!hasActivity) {
    return <p className="px-5 py-10 text-center text-sm text-slate-400">No attendance recorded in the last 6 months.</p>
  }

  return (
    <ResponsiveContainer width="100%" height={220}>
      <AreaChart data={data} margin={{ top: 12, right: 16, bottom: 0, left: -16 }}>
        <defs>
          <linearGradient id="attendanceFill" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stopColor="#2a78d6" stopOpacity={0.35} />
            <stop offset="100%" stopColor="#2a78d6" stopOpacity={0.03} />
          </linearGradient>
        </defs>
        <CartesianGrid vertical={false} stroke={ink.grid} />
        <XAxis dataKey="month" tick={axisTick} axisLine={{ stroke: ink.baseline }} tickLine={false} />
        <YAxis tick={axisTick} axisLine={false} tickLine={false} allowDecimals={false} width={28} />
        <Tooltip
          contentStyle={tooltipStyle}
          formatter={(value) => [`${value} attendance${value === 1 ? '' : 's'}`, 'Attendances']}
          labelFormatter={(label) => label}
        />
        <Area
          type="monotone"
          dataKey="total"
          name="Attendances"
          stroke="#2a78d6"
          strokeWidth={2}
          fill="url(#attendanceFill)"
          dot={{ r: 3, fill: '#2a78d6', strokeWidth: 0 }}
          activeDot={{ r: 5 }}
        />
      </AreaChart>
    </ResponsiveContainer>
  )
}

/** A single-series horizontal bar list — used for volume-only comparisons across a few categories. */
export function CategoryBarList({
  items,
  color,
  formatValue,
  maxValue,
}: {
  items: Array<{ label: string; value: number; color?: string }>
  color?: string
  /** Customizes the value label (e.g. append "%") without changing bar-width math, which always stays raw-number-proportional. */
  formatValue?: (value: number) => string
  /** Fixes the bar-width scale (e.g. 100 for a percentage series) instead of scaling to the largest value present. */
  maxValue?: number
}) {
  const max = maxValue ?? Math.max(...items.map((i) => i.value), 1)

  if (items.length === 0) {
    return <p className="px-5 py-8 text-center text-sm text-slate-400">No data yet.</p>
  }

  return (
    <div className="space-y-3 p-5">
      {items.map((item) => (
        <div key={item.label}>
          <div className="mb-1 flex items-center justify-between text-xs">
            <span className="capitalize text-slate-600">{item.label.replace('_', ' ')}</span>
            <span className="font-semibold text-slate-900">{formatValue ? formatValue(item.value) : item.value}</span>
          </div>
          <div className="h-2 overflow-hidden rounded-full bg-slate-100">
            <div
              className="h-full rounded-full transition-[width]"
              style={{ width: `${(item.value / max) * 100}%`, backgroundColor: item.color ?? color ?? '#2a78d6' }}
            />
          </div>
        </div>
      ))}
    </div>
  )
}

/** A single 100%-width stacked bar for a 2-part-of-a-whole comparison (e.g. Yes/No, Male/Female). */
export function ProportionBar({ segments }: { segments: Array<{ label: string; value: number; color: string }> }) {
  const total = segments.reduce((sum, s) => sum + s.value, 0)

  if (total === 0) {
    return <p className="px-5 py-8 text-center text-sm text-slate-400">No data yet.</p>
  }

  return (
    <div className="p-5">
      <div className="flex h-3 w-full overflow-hidden rounded-full bg-slate-100">
        {segments.map((s, i) => (
          <div
            key={s.label}
            style={{ width: `${(s.value / total) * 100}%`, backgroundColor: s.color, marginLeft: i > 0 ? 2 : 0 }}
            className="h-full first:rounded-l-full last:rounded-r-full"
          />
        ))}
      </div>
      <div className="mt-3 flex flex-wrap gap-x-5 gap-y-1.5">
        {segments.map((s) => (
          <div key={s.label} className="flex items-center gap-1.5 text-xs text-slate-600">
            <span className="h-2.5 w-2.5 shrink-0 rounded-full" style={{ backgroundColor: s.color }} />
            <span>
              {s.label} <span className="font-semibold text-slate-900">{s.value}</span>{' '}
              <span className="text-slate-400">({total > 0 ? Math.round((s.value / total) * 100) : 0}%)</span>
            </span>
          </div>
        ))}
      </div>
    </div>
  )
}

function Legend({ swatch, label }: { swatch: string; label: string }) {
  return (
    <span className="flex items-center gap-1.5">
      <span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: swatch }} />
      {label}
    </span>
  )
}

// Re-exported so callers don't need to import Cell/recharts directly for
// simple one-off use, kept for potential future per-category coloring needs.
export { Cell }
