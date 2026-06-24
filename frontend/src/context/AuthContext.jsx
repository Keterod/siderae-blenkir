import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { getMe, login as loginRequest, logout as logoutRequest } from '../lib/api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [authUser, setAuthUser] = useState(null);
  const [roles, setRoles] = useState([]);
  const [permissions, setPermissions] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);

  const loadSession = useCallback(async () => {
    try {
      setError(null);
      const response = await getMe();
      setAuthUser(response.usuario ?? null);
      setRoles(response.roles ?? []);
      setPermissions(response.permisos ?? []);
    } catch (err) {
      setAuthUser(null);
      setRoles([]);
      setPermissions([]);
      if (err.status && err.status !== 401) {
        setError('No se pudo validar la sesión.');
      }
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    loadSession();
  }, [loadSession]);

  const login = useCallback(async ({ email, password }) => {
    setIsLoading(true);
    setError(null);

    try {
      await loginRequest({ email, password });
      // En entornos de prueba E2E el navegador puede aplicar la cookie de sesión
      // (HttpOnly) de forma asíncrona tras la respuesta de /login. Esta pausa corta
      // asegura que /api/me la reciba en el siguiente request.
      await new Promise((resolve) => setTimeout(resolve, 1000));
      await loadSession();
      return true;
    } catch (err) {
      if (err.status === 422) {
        setError('Credenciales inválidas.');
      } else {
        setError('No se pudo iniciar sesión.');
      }
      setIsLoading(false);
      return false;
    }
  }, [loadSession]);

  const logout = useCallback(async () => {
    setIsLoading(true);
    setError(null);

    try {
      await logoutRequest();
    } finally {
      setAuthUser(null);
      setRoles([]);
      setPermissions([]);
      setIsLoading(false);
    }
  }, []);

  const value = useMemo(() => ({
    authUser,
    roles,
    permissions,
    isLoading,
    error,
    login,
    logout,
  }), [authUser, roles, permissions, isLoading, error, login, logout]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);

  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }

  return context;
}
