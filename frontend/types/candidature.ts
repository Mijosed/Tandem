export type CandidatureStatus = 'a_faire' | 'en_attente' | 'relance' | 'entretien' | 'accepte' | 'refuse'

export interface Candidature {
  id?: number
  titrePoste: string
  entreprise: string
  statut: CandidatureStatus
  dateDepot: string
  dateEntretien?: string
  notes?: string
  utilisateur?: string | { id: number }
  dateCreation?: string
}

export interface CandidatureStats {
  total: number
  aFaire: number
  enAttente: number
  relance: number
  entretien: number
  accepte: number
  refuse: number
  tauxReussite: number
}

export interface CandidatureFormData {
  titrePoste: string
  entreprise: string
  statut: CandidatureStatus
  dateDepot: string
  dateEntretien?: string
  notes?: string
}
