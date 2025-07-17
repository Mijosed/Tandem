export type ApplicationStatus = 'pending' | 'followed_up' | 'interview' | 'rejected' | 'accepted'

export interface Job {
  id: number
  title: string
  company: string
  description?: string
  location?: string
  salary?: string
  contractType?: string
  createdAt?: string
  updatedAt?: string
}

export interface User {
  id: number
  email: string
  firstName?: string
  lastName?: string
}

export interface Application {
  id?: number
  job?: Job
  appliedAt: string
  coverLetter?: string
  resumePath?: string
  status: ApplicationStatus
  user?: User
  updatedAt?: string
  
  // Propriétés calculées pour l'interface
  position?: string
  company?: string
  applicationDate?: string
  interviewDate?: string
  notes?: string
}

export interface ApplicationStats {
  total: number
  pending: number
  followed_up: number
  interview: number
  accepted: number
  rejected: number
}
