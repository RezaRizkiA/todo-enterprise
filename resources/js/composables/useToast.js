import { ref } from 'vue'
const _toasts = ref([])

export function useToast() {
    function push(message, { duration = 3000 } = {}) {
        const id = Math.random().toString(36).slice(2)
        _toasts.value.push({ id, message, expiresAt: duration ? Date.now() + duration : null })
    }
    function remove(id) {
        _toasts.value = _toasts.value.filter(t => t.id !== id)
    }
    return { toasts: _toasts, push, remove }
}
