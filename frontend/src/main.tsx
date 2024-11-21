import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App'  // Remove the .tsx extension
import './App.css'
import { Toaster } from "@/components/ui/toaster"

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <App />
    <Toaster />
  </React.StrictMode>
)
