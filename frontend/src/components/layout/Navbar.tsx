import { Link } from 'react-router-dom'

function Navbar() {
  return (
    <nav className="bg-primary text-primary-foreground shadow-lg">
      <div className="container mx-auto px-4">
        <div className="flex justify-between items-center h-16">
          <Link to="/" className="text-xl font-bold">
            PawPath
          </Link>
          <div className="space-x-4">
            <Link to="/pets" className="hover:text-opacity-80">
              Find Pets
            </Link>
            <Link to="/login" className="hover:text-opacity-80">
              Login
            </Link>
          </div>
        </div>
      </div>
    </nav>
  )
}

export default Navbar
