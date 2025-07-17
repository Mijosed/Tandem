/**
 * Composable pour les appels API vers le backend Symfony
 */
export const useApi = () => {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase

  // Headers par défaut pour API Platform
  const defaultHeaders = {
    'Accept': 'application/ld+json',
    'Content-Type': 'application/ld+json',
  }

  // Fonction générique pour les appels API
  const apiCall = async <T>(endpoint: string, options: any = {}) => {
    const url = endpoint.startsWith('/') ? endpoint : `/${endpoint}`
    
    return await $fetch<T>(`${apiBase}${url}`, {
      headers: {
        ...defaultHeaders,
        ...options.headers,
      },
      ...options,
    })
  }

  // Méthodes HTTP spécifiques
  const get = <T>(endpoint: string, options: any = {}) => {
    return apiCall<T>(endpoint, { method: 'GET', ...options })
  }

  const post = <T>(endpoint: string, data: any, options: any = {}) => {
    return apiCall<T>(endpoint, { method: 'POST', body: data, ...options })
  }

  const put = <T>(endpoint: string, data: any, options: any = {}) => {
    return apiCall<T>(endpoint, { method: 'PUT', body: data, ...options })
  }

  const patch = <T>(endpoint: string, data: any, options: any = {}) => {
    return apiCall<T>(endpoint, { method: 'PATCH', body: data, ...options })
  }

  const del = <T>(endpoint: string, options: any = {}) => {
    return apiCall<T>(endpoint, { method: 'DELETE', ...options })
  }

  return {
    apiCall,
    get,
    post,
    put,
    patch,
    delete: del,
  }
}
