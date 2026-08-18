<section class="scope-section">
    <div class="scope-heading">
        <h3>{{ $title }}</h3>
        <span class="scope-count">{{ $tasks->count() }}</span>
    </div>

    <div class="task-list-wrap">
        <ul
            class="task-list"
            data-reorderable="true"
            data-reorder-url="{{ route('tasks.reorder') }}"
            data-project-id="{{ $projectId ?? '' }}"
        >
            @forelse ($tasks as $task)
                <li class="task-row" draggable="true" data-task-id="{{ $task->id }}">
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
