import { BrowserRouter as Router, Routes, Route } from 'react-router-dom'
import HomePage from './pages/HomePage'
import LoginPage from './pages/LoginPage'
import PetsPage from './pages/PetsPage'
import Navbar from './components/layout/Navbar'

function App() {
  return (
    <Router>
      <div className="min-h-screen bg-background">
        <Navbar />
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/pets" element={<PetsPage />} />
        </Routes>
      </div>
    </Router>
  )
}

export default App
