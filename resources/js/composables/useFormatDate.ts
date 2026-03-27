import { usePage } from "@inertiajs/vue3"

export function useFormatDate() {
    const page = usePage()

    function formatDate(dateString: string): string {
        const locale = (page.props as Record<string, unknown>).locale as string ?? "en"
        const date = new Date(dateString.substring(0, 10) + "T00:00:00")

        return new Intl.DateTimeFormat(locale, {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
        }).format(date)
    }

    return { formatDate }
}
