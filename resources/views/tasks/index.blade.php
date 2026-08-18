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
            <input class="task-name-input" type="text" name="name" value="{{ old('name') }}" placeholder="What needs doing?" required>
            <select class="task-project-select" name="project_id" aria-label="Project">
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
                <h2>{{ $filterTitle }}</h2>
                <p>{{ $filterDescription }}</p>
            </div>
            <span class="task-count">{{ $taskCount }}</span>
        </div>

        @foreach ($sections as $section)
            @include('tasks._list', [
                'title' => $section['title'],
                'projectId' => $section['project_id'],
                'tasks' => $section['tasks'],
                'projects' => $projects,
            ])
        @endforeach
    </section>
@endsection
