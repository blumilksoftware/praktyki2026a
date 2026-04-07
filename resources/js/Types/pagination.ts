export interface PaginatorMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number | null
  to: number | null
  sort?: string
  direction?: 'asc' | 'desc'
  search?: string
  players?: number | null
  date_from?: string | null
  date_to?: string | null
}
