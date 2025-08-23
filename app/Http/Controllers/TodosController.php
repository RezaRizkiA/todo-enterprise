<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TodosController extends Controller
{
    public function index(Request $request)
    {
        $query = Todo::query()->where('user_id', auth()->id());

        // filter status
        if ($request->status === 'done') $query->where('done', true);
        if ($request->status === 'active') $query->where('done', false);

        // search
        if ($request->q) $query->where('title', 'like', '%' . $request->q . '%');

        return Inertia::render('Todos/Index', [
            'todos' => $query->latest()->paginate(10)->withQueryString(),
            'filters' => $request->only('q', 'status'),
            'routes' => [
                'index'   => route('todos.index'),
                'store'   => route('todos.store'),
                'update'  => url('/todos/update'),   // ✅ cukup base path
                'toggle'  => url('/todos/toggle'),   // ✅
                'destroy' => url('/todos/destroy'),  // ✅
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);
        Todo::create([
            'user_id' => auth()->id(),
            'title'   => $request->title,
        ]);
        return back()->with('success', 'Todo created!');
    }

    public function update(Request $request, $id)
    {
        $todo = Todo::where('user_id', auth()->id())->findOrFail($id);
        $request->validate(['title' => 'required|string|max:255']);
        $todo->update(['title' => $request->title]);
        return back()->with('success', 'Todo updated!');
    }

    public function toggle(Request $request, $id)
    {
        $todo = Todo::where('user_id', auth()->id())->findOrFail($id);
        $todo->update(['done' => $request->boolean('done')]);
        return back()->with('success', 'Todo toggled!');
    }

    public function destroy($id)
    {
        $todo = Todo::where('user_id', auth()->id())->findOrFail($id);
        $todo->delete();
        return back()->with('success', 'Todo deleted!');
    }
}
