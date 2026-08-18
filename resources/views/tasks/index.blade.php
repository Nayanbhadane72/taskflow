@extends('layouts.app')

@section('content')
    <header class="page-header">
        <div>
            <p class="eyebrow">Taskflow</p>
            <h1>Keep work moving.</h1>
            <p class="lede">A simple list for the things that need doing next.</p>
        </div>
        <form class="filter" method="GET" action="{{ route('tasks.index') }}">
            <label for="project">Show tasks</label>
            <select id="project" name="project" onchange="this.form.submit()">
                <option value="all" @selected($filter === 'all')>All tasks</option>
                <option value="unassigned" @selected($filter === 'unassigned')>Unassigned</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" @selected((string) $filter === (string) $project->id)>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </header>

    <section class="card new-task">
        <h2>Add a task</h2>
        <form method="POST" action="{{ route('tasks.store') }}" class="task-form">
            @csrf
            <input type="text" name="name" value="{{ old('name') }}" placeholder="What needs doing?" required>
            <select name="project_id" aria-label="Project">
                <option value="">No project</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit">Add task</button>
        </form>
    </section>

    <section class="card task-card">
        <div class="list-heading">
            <div>
                <h2>{{ $filter === 'all' ? 'All tasks' : ($filter === 'unassigned' ? 'Unassigned' : $projects->firstWhere('id', (int) $filter)?->name) }}</h2>
                <p>
                    @if ($reorderable = ($filter === 'unassigned' || is_numeric($filter)))
                        Drag a task to change its priority.
                    @else
                        Choose one project to reorder its tasks.
                    @endif
                </p>
            </div>
            <span class="task-count">{{ $tasks->count() }}</span>
        </div>

        <div
            class="task-list-wrap"
            data-reorderable="{{ $reorderable ? 'true' : 'false' }}"
        >
            <ul
                class="task-list"
                data-reorder-url="{{ route('tasks.reorder') }}"
                data-project-id="{{ $filter === 'unassigned' ? '' : (is_numeric($filter) ? $filter : '') }}"
            >
                @forelse ($tasks as $task)
                    <li class="task-row" draggable="{{ $reorderable ? 'true' : 'false' }}" data-task-id="{{ $task->id }}">
                        <span class="drag-handle" aria-hidden="true">::</span>
                        <span class="priority">{{ $task->priority }}</span>
                        <form method="POST" action="{{ route('tasks.update', $task) }}" class="edit-form">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $task->name }}" aria-label="Task name">
                            <select name="project_id" aria-label="Project">
                                <option value="">No project</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" @selected($task->project_id === $project->id)>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="button-muted">Save</button>
                        </form>
                        <form method="POST" action="{{ route('tasks.destroy', $task) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icon-button" aria-label="Delete {{ $task->name }}">×</button>
                        </form>
                    </li>
                @empty
                    <li class="empty-state">No tasks here yet.</li>
                @endforelse
            </ul>
            <p class="reorder-message" role="status" aria-live="polite"></p>
        </div>
    </section>
@endsection
