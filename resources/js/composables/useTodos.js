import { useForm, router } from '@inertiajs/vue3'
import { useToast } from './useToast'

export function useTodos(endpoints) {
    const { push } = useToast()

    const form = useForm({ title: '', due_date: '', priority: 'normal' })

    function create(payload) {
        form.transform(() => payload ?? form.data())
            .post(endpoints.store, {
                preserveScroll: true,
                onSuccess: () => { push('Todo created'); form.reset('title', 'due_date', 'priority') },
                onError: () => push('Failed to create todo')
            })
    }

    function toggle(id, done) {
        router.patch(`${endpoints.toggle}/${id}`, { done }, {
            preserveScroll: true,
            only: ['todos', 'flash'],
            onSuccess: () => push(done ? 'Marked as done' : 'Marked as active'),
            onError: () => push('Failed to update status')
        })
    }

    function update(id, payload) {
        router.patch(`${endpoints.update}/${id}`, payload, {
            preserveScroll: true,
            only: ['todos', 'flash'],
            onSuccess: () => push('Todo updated'),
            onError: () => push('Failed to update todo')
        })
    }

    function destroy(id) {
        router.delete(`${endpoints.destroy}/${id}`, {
            preserveScroll: true,
            only: ['todos', 'flash'],
            onSuccess: () => push('Todo deleted'),
            onError: () => push('Failed to delete todo')
        })
    }

    function filter(params) {
        router.get(endpoints.index, params, {
            preserveScroll: true,
            preserveState: true,
            only: ['todos'],
            replace: true
        })
    }

    return { form, create, toggle, update, destroy, filter }
}
