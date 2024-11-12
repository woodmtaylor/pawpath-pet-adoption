import { Link } from 'react-router-dom'
import { useAuth } from '@/contexts/AuthContext'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
  DropdownMenuSeparator,
  DropdownMenuLabel,
} from '@/components/ui/dropdown-menu'
import { 
  User, 
  LogOut, 
  Settings, 
  Shield, 
  Home, 
  PawPrint,
  Heart,
  ClipboardList
} from 'lucide-react'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'

function Navbar() {
  const { isAuthenticated, user, logout } = useAuth()

  const handleLogout = () => {
    logout()
  }

  const getInitials = (username: string) => {
    return username
      .split(' ')
      .map(word => word[0])
      .join('')
      .toUpperCase()
      .slice(0, 2);
  }

  const renderRoleBasedLinks = () => {
    if (!user) return null;

    switch (user.role) {
      case 'admin':
        return (
          <>
            <DropdownMenuSeparator />
            <DropdownMenuLabel>Admin</DropdownMenuLabel>
            <DropdownMenuItem asChild>
              <Link to="/admin">
                <Shield className="mr-2 h-4 w-4" />
                Admin Dashboard
              </Link>
            </DropdownMenuItem>
            <DropdownMenuItem asChild>
              <Link to="/admin/users">
                <User className="mr-2 h-4 w-4" />
                User Management
              </Link>
            </DropdownMenuItem>
          </>
        );
      case 'shelter_staff':
        return (
          <>
            <DropdownMenuSeparator />
            <DropdownMenuLabel>Shelter</DropdownMenuLabel>
            <DropdownMenuItem asChild>
              <Link to="/shelter">
                <Home className="mr-2 h-4 w-4" />
                Shelter Dashboard
              </Link>
            </DropdownMenuItem>
            <DropdownMenuItem asChild>
              <Link to="/shelter/pets">
                <PawPrint className="mr-2 h-4 w-4" />
                Manage Pets
              </Link>
            </DropdownMenuItem>
          </>
        );
      default:
        return null;
    }
  };

  return (
    <nav className="bg-primary text-primary-foreground shadow-lg">
      <div className="container mx-auto px-4">
        <div className="flex justify-between items-center h-16">
          <Link to="/" className="text-xl font-bold">
            PawPath
          </Link>
          
          <div className="space-x-4 flex items-center">
            <Link to="/pets" className="hover:text-opacity-80">
              Find Pets
            </Link>
            
            {isAuthenticated ? (
              <>
                <Link to="/quiz" className="hover:text-opacity-80">
                  Take Quiz
                </Link>
                
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button 
                      variant="ghost" 
                      className="hover:text-opacity-80 relative flex items-center"
                    >
                      <Avatar className="h-8 w-8 mr-2">
                        <AvatarFallback>
                          {user?.username ? getInitials(user.username) : 'U'}
                        </AvatarFallback>
                      </Avatar>
                      <span>{user?.username}</span>
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" className="w-56">
                    <DropdownMenuLabel>My Account</DropdownMenuLabel>
                    <DropdownMenuItem asChild>
                      <Link to="/profile">
                        <User className="mr-2 h-4 w-4" />
                        Profile
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                      <Link to="/profile/applications">
                        <ClipboardList className="mr-2 h-4 w-4" />
                        My Applications
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                      <Link to="/profile/favorites">
                        <Heart className="mr-2 h-4 w-4" />
                        Saved Pets
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                      <Link to="/profile/settings">
                        <Settings className="mr-2 h-4 w-4" />
                        Settings
                      </Link>
                    </DropdownMenuItem>
                    
                    {renderRoleBasedLinks()}
                    
                    <DropdownMenuSeparator />
                    <DropdownMenuItem 
                      onClick={handleLogout}
                      className="text-red-600 cursor-pointer"
                    >
                      <LogOut className="mr-2 h-4 w-4" />
                      Logout
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </>
            ) : (
              <>
                <Link to="/login" className="hover:text-opacity-80">
                  Login
                </Link>
                <Link to="/register" className="hover:text-opacity-80">
                  Register
                </Link>
              </>
            )}
          </div>
        </div>
      </div>
    </nav>
  );
}

export default Navbar;
