export type Role = 'admin' | 'project_officer' | 'trainee'

export interface User {
  id: number
  name: string
  username: string
  email: string
  role: Role
  is_active: boolean
  trainee_profile?: TraineeProfile | null
  trainee_profile_id?: number | null
  created_at?: string
}

export type Sex = 'Male' | 'Female'

export type ChainLevel = 'Production' | 'Post-Harvest' | 'Processing' | 'Marketing' | 'Consumption'

export type EducationalLevel = 'Elementary' | 'High School' | 'Vocational' | 'College' | 'Post Graduate'

export type IncomeClass = '1st Class' | '2nd Class' | '3rd Class' | '4th Class' | '5th Class' | '6th Class'

export type TrainingStatus = 'upcoming' | 'ongoing' | 'completed' | 'cancelled'

export interface TraineeProfile {
  id: number
  user_id: number | null
  first_name: string
  middle_name: string | null
  last_name: string
  suffix: string | null
  full_name: string
  sex: Sex
  birthdate: string | null
  age: number | null
  contact_number: string | null
  email: string | null
  address_line: string | null
  barangay: string | null
  city_municipality: string
  province: string
  region: string | null
  lgu_income_class: IncomeClass | null
  educational_level: EducationalLevel | null
  organization_affiliation: string | null
  occupation: string | null
  year_last_attended?: string | null
  training_count?: number
  has_login: boolean
  created_at?: string
  updated_at?: string
}

export interface TrainingProgram {
  id: number
  name: string
  description: string | null
  chain_level: ChainLevel
  venue: string | null
  start_date: string
  end_date: string | null
  status: TrainingStatus
  created_by: number | null
  attendee_count?: number
  created_at?: string
}

export interface TrainingHistoryRecord {
  id: number
  trainee_id: number
  training_program_id: number
  training_program?: TrainingProgram
  chain_level: ChainLevel
  date_attended: string
  remarks: string | null
  created_at?: string
}

export interface RequiredDocument {
  id: number
  code: string
  name: string
  is_active?: boolean
  sort_order?: number
}

export type ReviewStatus = 'pending' | 'approved' | 'needs_revision'

export interface DocumentStatusEntry {
  required_document_id: number
  code: string
  name: string
  submitted: boolean
  date_submitted: string | null
  review_status: ReviewStatus
  remarks: string | null
  has_attachment: boolean
  attachment_name: string | null
}

export interface TrainingComplianceEntry {
  training_history_id: number
  training_program_id: number
  training_program_name?: string
  date_attended?: string
  documents: DocumentStatusEntry[]
  overall_status: 'Passed' | 'Incomplete'
}

export interface DocumentComplianceSummary {
  per_training: TrainingComplianceEntry[]
  overall_status: 'Passed' | 'Incomplete' | 'No Records'
}

export interface ChainLevelGap {
  chain_level: ChainLevel
  required: boolean
  completed: boolean
  status: 'Complete' | 'Incomplete (Gap)'
}

export interface CompetencyGapSummary {
  chain_levels: ChainLevelGap[]
  gap_count: number
  gaps: ChainLevel[]
  has_gaps: boolean
}

export type ConfirmationStatus = 'pending' | 'confirmed' | 'declined'

export interface TrainingConfirmation {
  id: number
  trainee_id: number
  training_program_id: number
  status: ConfirmationStatus
  confirmed_at: string | null
  training_program?: TrainingProgram
  trainee?: TraineeProfile
}

export interface ActivityLogEntry {
  id: number
  user_id: number | null
  action: string
  subject_type: string | null
  subject_id: number | null
  description: string | null
  meta: Record<string, unknown> | null
  created_at: string
  user?: { id: number; name: string; role: Role }
}

export interface ActivityHistory {
  trainee: TraineeProfile
  training_history: TrainingHistoryRecord[]
  document_compliance: DocumentComplianceSummary
  competency_gaps: CompetencyGapSummary
  confirmations: TrainingConfirmation[]
  activity_log: ActivityLogEntry[]
}

export interface MetaData {
  chain_levels: ChainLevel[]
  educational_levels: EducationalLevel[]
  income_classes: IncomeClass[]
  training_statuses: TrainingStatus[]
  required_documents: RequiredDocument[]
  provinces: string[]
}

export interface Location {
  id: number
  region: string
  province: string
  city_municipality: string
  lgu_income_class: IncomeClass | null
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    total: number
    per_page?: number
  }
  links?: Record<string, string | null>
}

export interface DashboardStats {
  total_trainees: number
  total_training_programs: number
  total_attendances: number
  document_compliance: {
    submitted: number
    missing: number
    submission_rate: number
  }
  trainings_by_chain_level: Record<string, number>
  trainings_by_status: Record<string, number>
  trainees_by_sex: Record<string, number>
  users_by_role: Record<string, number>
  recent_trainees: Array<{ id: number; first_name: string; last_name: string; created_at: string }>
  competency_gap_coverage: Array<{ chain_level: ChainLevel; completed: number; gap: number }>
  attendance_trend: Array<{ month: string; total: number }>
  trainees_by_province: Record<string, number>
  province_completion_ranking: Array<{
    province: string
    total_trainees: number
    completed_trainees: number
    completion_rate: number
  }>
}

export type TestType = 'pre_test' | 'post_test'

export type QuestionType = 'multiple_choice' | 'fill_blank'

export interface TestQuestion {
  id?: number
  type: QuestionType
  question_text: string
  options: string[] | null
  correct_answer?: string
  points: number
  sort_order?: number
}

export interface TrainingProgramTest {
  id: number
  training_program_id: number
  type: TestType
  title: string
  instructions: string | null
  passing_score: number
  questions: TestQuestion[]
}

export interface TestAttemptResult {
  id: number
  raw_score: number
  max_score: number
  percentage: number
  passed: boolean
  attempted_at: string
  answers: Record<string, string>
}
