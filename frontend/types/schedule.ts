export interface ScheduleEvent {
  id: string
  title: string
  description?: string
  start: string
  end: string
  allDay?: boolean
  type?: 'interview' | 'meeting' | 'reminder' | 'deadline' | 'personal'
  location?: string
  color?: string
  extendedProps?: {
    type?: string
    location?: string
    description?: string
    candidature?: any
  }
}

export interface ScheduleEventInput {
  title: string
  description?: string
  startDate: string
  endDate: string
  type?: 'interview' | 'meeting' | 'reminder' | 'deadline' | 'personal'
  location?: string
  allDay?: boolean
  color?: string
}

export interface EventStats {
  total: number
  today: number
  upcoming: number
  byType: Record<string, number>
}
