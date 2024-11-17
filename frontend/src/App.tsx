import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AuthProvider } from '@/contexts/AuthContext';
import { ProtectedRoute } from '@/components/auth/ProtectedRoute';
import { RoleProtectedRoute } from '@/components/auth/RoleProtectedRoute';
import Navbar from './components/layout/Navbar';

// Page Imports
import HomePage from './pages/HomePage';
import LoginPage from './pages/LoginPage';
import PetsPage from './pages/PetsPage';
import QuizPage from './pages/QuizPage';
import QuizResultsPage from './pages/QuizResultsPage';
import RegisterPage from './pages/RegisterPage';
import PetDetailPage from './pages/PetDetailPage';
import ProfilePage from './pages/ProfilePage';
import ProfileSettings from './pages/profile/ProfileSettings';
import UnauthorizedPage from './pages/UnauthorizedPage';
import AdoptionFormPage from './pages/AdoptionFormPage';
import ApplicationsPage from './pages/profile/ApplicationsPage';

// Admin Pages
import AdminDashboard from './pages/admin/AdminDashboard';
import UserManagement from './pages/admin/UserManagement';

// Shelter Pages
import ShelterDashboard from './pages/shelter/ShelterDashboard';
import ShelterManagement from './pages/shelter/ShelterManagement';

function App() {
  return (
    <AuthProvider>
      <Router>
        <div className="min-h-screen bg-background">
          <Navbar />
          <Routes>
            {/* Public Routes */}
            <Route path="/" element={<HomePage />} />
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
            <Route path="/pets" element={<PetsPage />} />
            <Route path="/pets/:id" element={<PetDetailPage />} />
            <Route path="/unauthorized" element={<UnauthorizedPage />} />

            {/* Protected Routes (Any authenticated user) */}
            <Route 
              path="/profile" 
              element={
                <ProtectedRoute>
                  <ProfilePage />
                </ProtectedRoute>
              } 
            />

            <Route 
              path="/adopt/:id" 
              element={
                <ProtectedRoute>
                  <AdoptionFormPage />
                </ProtectedRoute>
              } 
            />
            
            <Route 
              path="/profile/settings" 
              element={
                <ProtectedRoute>
                  <ProfileSettings />
                </ProtectedRoute>
              } 
            />

            <Route 
              path="/profile/applications" 
              element={
                <ProtectedRoute>
                  <ApplicationsPage />
                </ProtectedRoute>
              } 
            />
            
            <Route 
              path="/quiz" 
              element={
                <ProtectedRoute>
                  <QuizPage />
                </ProtectedRoute>
              } 
            />
            
            <Route 
              path="/quiz/results" 
              element={
                <ProtectedRoute>
                  <QuizResultsPage />
                </ProtectedRoute>
              } 
            />

            {/* Admin Routes */}
            <Route 
              path="/admin/*" 
              element={
                <RoleProtectedRoute requiredRole="admin">
                  <Routes>
                    <Route path="/" element={<AdminDashboard />} />
                    <Route path="/users" element={<UserManagement />} />
                    <Route path="/shelters" element={<ShelterManagement />} />
                  </Routes>
                </RoleProtectedRoute>
              }
            />

            {/* Shelter Staff Routes */}
            <Route 
              path="/shelter/*" 
              element={
                <RoleProtectedRoute requiredRole="shelter_staff">
                  <Routes>
                    <Route path="/" element={<ShelterDashboard />} />
                    <Route path="/pets" element={<ShelterManagement />} />
                  </Routes>
                </RoleProtectedRoute>
              }
            />
          </Routes>
        </div>
      </Router>
    </AuthProvider>
  );
}

export default App;
