declare function route(name: string, params?: Record<string, unknown> | number | string, absolute?: boolean): string
declare function route(): { current: (name: string) => boolean }
