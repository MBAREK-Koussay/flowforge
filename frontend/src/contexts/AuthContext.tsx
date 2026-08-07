import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { api, apiError, tokenStore } from '@/lib/api';
import type { LoginResponse, User } from '@/types/models';

interface AuthContextValue {
  user: User | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<User>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  const fetchMe = useCallback(async () => {
    const token = tokenStore.get();
    if (!token) {
      setLoading(false);
      return;
    }
    try {
      const { data } = await api.get<{ success: boolean; data: User }>('/auth/me');
      setUser(data.data);
    } catch {
      tokenStore.clear();
      setUser(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    void fetchMe();
  }, [fetchMe]);

  const login = useCallback(async (email: string, password: string): Promise<User> => {
    const { data } = await api.post<{ success: boolean; data: LoginResponse }>('/auth/login', {
      email,
      password,
    });
    tokenStore.set(data.data.token);
    setUser(data.data.user);
    return data.data.user;
  }, []);

  const logout = useCallback(async () => {
    try {
      await api.post('/auth/logout');
    } catch {
      // ignore server errors on logout; local session is cleared regardless
    }
    tokenStore.clear();
    setUser(null);
  }, []);

  const value = useMemo(
    () => ({ user, loading, login, logout }),
    [user, loading, login, logout]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}

export { apiError };