import { Navigate, useLocation } from 'react-router-dom'
import { useAtomValue } from 'jotai'
import { isAuthenticatedAtom } from '../../stores/auth'

interface ProtectedRouteProps {
  children: React.ReactNode
}

function ProtectedRoute({ children }: ProtectedRouteProps) {
  const isAuthenticated = useAtomValue(isAuthenticatedAtom)
  const location = useLocation()

  if (!isAuthenticated) {
    // Redirect to login page but save the attempted url
    return <Navigate to="/login" state={{ from: location }} replace />
  }

  return <>{children}</>
}

export default ProtectedRoute
