import { Route, Routes } from 'react-router-dom';
import { RoleProtectedRoute } from '@/components/auth/RoleProtectedRoute';
import ShelterDashboard from '@/pages/shelter/ShelterDashboard';
import ShelterPetManagement from '@/pages/shelter/ShelterManagement';
import NewPetPage from '@/pages/shelter/NewPetPage';

export function ShelterRoutes() {
  return (
    <RoleProtectedRoute requiredRole="shelter_staff">
      <Routes>
        <Route path="/" element={<ShelterDashboard />} />
        <Route path="/pets" element={<ShelterPetManagement />} />
        <Route path="/pets/new" element={<NewPetPage />} />
      </Routes>
    </RoleProtectedRoute>
  );
}
