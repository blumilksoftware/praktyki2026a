export {}

declare global {
    const route: typeof import('../../../vendor/tightenco/ziggy/src/js/index').route
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        route: typeof import('../../../vendor/tightenco/ziggy/src/js/index').route
    }
}
