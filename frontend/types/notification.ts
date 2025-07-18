export interface Notification {
  id: number
  title: string
  message: string
  type: 'reminder' | 'interview' | 'info' | 'warning' | 'success'
  isRead: boolean
  user?: {
    id: number
    email: string
    firstName: string
    lastName: string
  }
  scheduledFor?: string | null
  createdAt: string
  updatedAt: string
}

export interface NotificationFormData {
  title: string
  message: string
  type: 'reminder' | 'interview' | 'info' | 'warning' | 'success'
  scheduledFor?: string | null
}

export interface NotificationStats {
  total: number
  unread: number
  byType: {
    reminder: number
    interview: number
    info: number
    warning: number
    success: number
  }
} 