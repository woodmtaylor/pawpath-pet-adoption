import { atom } from 'jotai'

interface User {
  user_id: number
  username: string
  email: string
}

// Create the atoms and export them
export const userAtom = atom<User | null>(null)
export const isAuthenticatedAtom = atom((get) => get(userAtom) !== null)
