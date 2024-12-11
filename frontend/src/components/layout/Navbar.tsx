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
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { useState, useEffect } from 'react';
import Logo from '@/assets/pawpath-logo.svg';

const getFullImageUrl = (url: string | null | undefined) => {
    if (!url) return null;
    return url.startsWith('http') ? url : `${window.location.origin}${url}`;
};

function Navbar() {
  const { isAuthenticated, user, logout } = useAuth()
  const [profileImage, setProfileImage] = useState<string | null>(null);

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

  const avatar = user?.profile_image ? (
    <AvatarImage 
      src={user.profile_image}
      alt={user.username}
      className="aspect-square h-full w-full"
    />
  ) : (
    <AvatarFallback>
      {user?.username ? getInitials(user.username) : 'U'}
    </AvatarFallback>
  );

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
          <Link to="/" className="text-xl font-bold flex items-center gap-2">
            <img src={Logo} alt="PawPath" className="h-24 w-auto" />
            <span className="sr-only">PawPath</span>
          </Link>

          <div className="space-x-4 flex items-center">
            <Link to="/pets" className="hover:text-opacity-80">
              Find Pets
            </Link>

            <Link to="/blog" className="hover:text-opacity-80">
              Blog
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
                      className="relative flex items-center gap-2"
                    >
                      <Avatar className="h-8 w-8">
                        {avatar}
                      </Avatar>
                      <span className="ml-2">{user?.username}</span>
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" className="w-56">
                    <DropdownMenuLabel>My Account</DropdownMenuLabel>
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
