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
import FavoritesPage from './pages/profile/FavoritesPage';

// Admin Pages
import AdminDashboard from './pages/admin/AdminDashboard';
import UserManagement from './pages/admin/UserManagement';
import ShelterManagement from './pages/admin/ShelterManagement';

// Shelter Pages
import ShelterDashboard from './pages/shelter/ShelterDashboard';
import ShelterPetManagement from './pages/shelter/ShelterManagement';
import NewShelterPage from './pages/admin/NewShelterPage';

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
            <Route path="/unauthorized" element={<UnauthorizedPage />} />

            {/* Protected Routes */}
            <Route 
              path="/pets" 
              element={
                <ProtectedRoute>
                  <PetsPage />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/pets/:id" 
              element={
                <ProtectedRoute>
                  <PetDetailPage />
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
                    <Route path="/shelters/new" element={<NewShelterPage />} />
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
                    <Route path="/pets" element={<ShelterPetManagement />} />
                  </Routes>
                </RoleProtectedRoute>
              }
            />

            {/* Profile Routes */}
            <Route 
              path="/profile/*" 
              element={
                <ProtectedRoute>
                  <Routes>
                    <Route path="/" element={<ProfilePage />} />
                    <Route path="/settings" element={<ProfileSettings />} />
                    <Route path="/applications" element={<ApplicationsPage />} />
                    <Route path="/favorites" element={<FavoritesPage />} />
                  </Routes>
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

            <Route 
              path="/adopt/:id" 
              element={
                <ProtectedRoute>
                  <AdoptionFormPage />
                </ProtectedRoute>
              }
            />
          </Routes>
        </div>
      </Router>
    </AuthProvider>
  );
}

export default App;
