import { atom } from 'jotai';

export interface User {
  user_id: number;
  username: string;
  email: string;
  role: 'admin' | 'shelter_staff' | 'adopter';
  profile_image?: string | null;
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
  try {
    const userStr = localStorage.getItem('user');
    if (!userStr) return null;
    const user = JSON.parse(userStr);
    return user;
  } catch (e) {
    console.error('Error parsing stored user:', e);
    return null;
  }
};

export const setStoredUser = (user: User) => {
  try {
    localStorage.setItem('user', JSON.stringify(user));
  } catch (e) {
    console.error('Error storing user:', e);
  }
};

export const removeStoredUser = () => {
  localStorage.removeItem('user');
};
