import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import { InertiaProgress } from '@inertiajs/progress'

InertiaProgress.init()

const pages = import.meta.glob('./Pages/**/*.vue')

createInertiaApp({
    resolve: async name => {
        const page = pages[`./Pages/${name}.vue`]
        if (!page) {
            throw new Error(`Page not found: ${name}`)
        }
        return (await page()).default
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)
    },
})
