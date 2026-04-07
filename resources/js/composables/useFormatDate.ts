export function useFormatDate() {
  function formatDate(dateString: string): string {
    const date = new Date(dateString.substring(0, 10) + 'T00:00:00')
    const dd = String(date.getDate()).padStart(2, '0')
    const mm = String(date.getMonth() + 1).padStart(2, '0')
    const yyyy = date.getFullYear()
    return `${dd}.${mm}.${yyyy}`
  }

  return { formatDate }
}
