import { atom } from 'jotai';

export interface User {
  user_id: number;
  username: string;
  email: string;
  role: 'admin' | 'shelter_staff' | 'adopter';
}

// Create atoms for user and loading state
export const userAtom = atom<User | null>(null);
export const authLoadingAtom = atom<boolean>(true);

// Token management functions
export const getStoredToken = () => localStorage.getItem('token');
export const setStoredToken = (token: string) => localStorage.setItem('token', token);
export const removeStoredToken = () => localStorage.removeItem('token');

// User management functions
export const getStoredUser = (): User | null => {
  const userStr = localStorage.getItem('user');
  return userStr ? JSON.parse(userStr) : null;
};

export const setStoredUser = (user: User) => {
  localStorage.setItem('user', JSON.stringify(user));
};

export const removeStoredUser = () => {
  localStorage.removeItem('user');
};
