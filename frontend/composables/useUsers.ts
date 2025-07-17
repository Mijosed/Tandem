export const useUsers = () => {
  const { get, post, put, patch, delete: del } = useApi()

  // Types pour les utilisateurs
  interface User {
    '@id': string
    '@type': string
    '@context': string
    id: number
    email: string
    roles: string[]
    // Ajoutez d'autres propriétés selon votre entité User
  }

  interface UserCollection {
    '@context': string
    '@id': string
    '@type': string
    'hydra:member': User[]
    'hydra:totalItems': number
    'hydra:view'?: any
    'hydra:search'?: any
  }

  // Récupérer tous les utilisateurs
  const getUsers = async (): Promise<UserCollection> => {
    return await get<UserCollection>('/users')
  }

  // Récupérer un utilisateur par ID
  const getUser = async (id: number): Promise<User> => {
    return await get<User>(`/users/${id}`)
  }

  // Créer un nouvel utilisateur
  const createUser = async (userData: Partial<User>): Promise<User> => {
    return await post<User>('/users', userData)
  }

  // Mettre à jour un utilisateur
  const updateUser = async (id: number, userData: Partial<User>): Promise<User> => {
    return await put<User>(`/users/${id}`, userData)
  }

  // Supprimer un utilisateur
  const deleteUser = async (id: number): Promise<void> => {
    return await del<void>(`/users/${id}`)
  }

  return {
    getUsers,
    getUser,
    createUser,
    updateUser,
    deleteUser,
  }
} 