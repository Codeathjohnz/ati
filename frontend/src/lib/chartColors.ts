// Validated palette (see the dataviz skill's references/palette.md) — light-mode
// only, matching the rest of this app, which has no dark theme. Categorical hues
// are assigned in a FIXED order per context and never cycled; status colors are
// reserved for genuine state (good/warning/critical) and never reused as a
// generic 4th/5th series color.

export const categorical = {
  blue: '#2a78d6',
  orange: '#eb6834',
  aqua: '#1baf7a',
  yellow: '#eda100',
} as const

export const status = {
  good: '#0ca30c',
  warning: '#fab219',
  critical: '#d03b3b',
} as const

export const sequential = {
  line: '#2a78d6',
  fillFrom: '#9ec5f4',
  fillTo: '#cde2fb',
} as const

export const ink = {
  primary: '#0b0b0b',
  secondary: '#52514e',
  muted: '#898781',
  grid: '#e1e0d9',
  baseline: '#c3c2b7',
} as const

// Fixed, never-cycled category -> color assignments used across the dashboard.
export const chainLevelOrder = ['Production', 'Post-Harvest', 'Processing', 'Marketing', 'Consumption'] as const

export const programStatusColor: Record<string, string> = {
  upcoming: categorical.blue,
  ongoing: categorical.orange,
  completed: categorical.aqua,
  cancelled: categorical.yellow,
}

export const roleColor: Record<string, string> = {
  admin: categorical.blue,
  project_officer: categorical.orange,
  trainee: categorical.aqua,
}

export const sexColor: Record<string, string> = {
  Male: categorical.blue,
  Female: categorical.orange,
}
