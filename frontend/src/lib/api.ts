import axios, { AxiosError } from 'axios';
import type { ApiEnvelope } from '@/types/models';

const TOKEN_KEY = 'flowforge_token';

export const tokenStore = {
  get: () => localStorage.getItem(TOKEN_KEY),
  set: (token: string) => localStorage.setItem(TOKEN_KEY, token),
  clear: () => localStorage.removeItem(TOKEN_KEY),
};

export const api = axios.create({
  baseURL: '/api/v1',
  headers: { 'Content-Type': 'application/json' },
});

api.interceptors.request.use((config) => {
  const token = tokenStore.get();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export function apiError(error: unknown): string {
  if (axios.isAxiosError(error)) {
    const e = error as AxiosError<ApiEnvelope<null>>;
    return e.response?.data?.message ?? e.message ?? 'Request failed';
  }
  return 'Request failed';
}

export default api;