import { BrowserRouter, Route, Routes } from 'react-router-dom'
import { RequireRole } from './RequireRole'
import RoleRedirect from './RoleRedirect'
import AdminLayout from './AdminLayout'
import OfficerLayout from './OfficerLayout'
import TraineeLayout from './TraineeLayout'

import Login from '../pages/auth/Login'

import Dashboard from '../pages/shared/Dashboard'
import Reports from '../pages/shared/Reports'

import AdminUsers from '../pages/admin/Users'
import AdminSettings from '../pages/admin/Settings'
import AdminAuditLogs from '../pages/admin/AuditLogs'
import AdminStorageMonitor from '../pages/admin/StorageMonitor'

import TraineesList from '../pages/officer/TraineesList'
import TraineeForm from '../pages/officer/TraineeForm'
import TraineeDetail from '../pages/officer/TraineeDetail'
import TrainingPrograms from '../pages/officer/TrainingPrograms'
import OfficerCompetencyGaps from '../pages/officer/CompetencyGaps'
import UpcomingConfirmations from '../pages/officer/UpcomingConfirmations'

import TraineeDashboard from '../pages/trainee/TraineeDashboard'
import MyTrainingHistory from '../pages/trainee/MyTrainingHistory'
import MyDocuments from '../pages/trainee/MyDocuments'
import UpcomingTraining from '../pages/trainee/UpcomingTraining'
import MyActivityHistory from '../pages/trainee/MyActivityHistory'
import TakeTest from '../pages/trainee/TakeTest'

export default function AppRouter() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="/" element={<RoleRedirect />} />

        {/* Administrator Module */}
        <Route
          path="/admin"
          element={
            <RequireRole roles={['admin']}>
              <AdminLayout />
            </RequireRole>
          }
        >
          <Route index element={<Dashboard title="Administrator Dashboard" />} />
          <Route path="users" element={<AdminUsers />} />
          <Route path="settings" element={<AdminSettings />} />
          <Route path="audit-logs" element={<AdminAuditLogs />} />
          <Route path="storage" element={<AdminStorageMonitor />} />
          <Route path="reports" element={<Reports />} />
        </Route>

        {/* Project Officer Module */}
        <Route
          path="/officer"
          element={
            <RequireRole roles={['admin', 'project_officer']}>
              <OfficerLayout />
            </RequireRole>
          }
        >
          <Route index element={<Dashboard title="Project Officer Dashboard" />} />
          <Route path="trainees" element={<TraineesList />} />
          <Route path="trainees/new" element={<TraineeForm />} />
          <Route path="trainees/:id" element={<TraineeDetail />} />
          <Route path="trainees/:id/edit" element={<TraineeForm />} />
          <Route path="training-programs" element={<TrainingPrograms />} />
          <Route path="competency-gaps" element={<OfficerCompetencyGaps />} />
          <Route path="upcoming-confirmations" element={<UpcomingConfirmations />} />
          <Route path="reports" element={<Reports />} />
        </Route>

        {/* Trainee Module */}
        <Route
          path="/trainee"
          element={
            <RequireRole roles={['trainee']}>
              <TraineeLayout />
            </RequireRole>
          }
        >
          <Route index element={<TraineeDashboard />} />
          <Route path="training-history" element={<MyTrainingHistory />} />
          <Route path="documents" element={<MyDocuments />} />
          <Route path="training-history/:trainingHistoryId/tests/:type" element={<TakeTest />} />
          <Route path="upcoming-training" element={<UpcomingTraining />} />
          <Route path="activity-history" element={<MyActivityHistory />} />
        </Route>

        <Route path="*" element={<RoleRedirect />} />
      </Routes>
    </BrowserRouter>
  )
}
