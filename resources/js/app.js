import '../css/app.css';

document.querySelectorAll('.task-list[data-reorderable="true"]').forEach((list) => {
    const listWrap = list.closest('.task-list-wrap');
    let dragged;
    let previousOrder;
    let dropped = false;

    const message = (text, error = false) => {
        const element = listWrap.querySelector('.reorder-message');
        element.textContent = text;
        element.classList.toggle('is-error', error);
    };

    const renderPriorities = () => {
        list.querySelectorAll('.task-row').forEach((row, index) => {
            row.querySelector('.priority').textContent = index + 1;
        });
    };

    const saveOrder = async () => {
        const taskIds = [...list.querySelectorAll('.task-row')].map((row) => row.dataset.taskId);
        const projectId = list.dataset.projectId || null;

        try {
            const response = await fetch(list.dataset.reorderUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ project_id: projectId, task_ids: taskIds }),
            });

            if (!response.ok) {
                throw new Error('The task order could not be saved.');
            }

            message('Order saved.');
        } catch (error) {
            list.replaceChildren(...previousOrder);
            renderPriorities();
            message(error.message, true);
        }
    };

    list.addEventListener('dragstart', (event) => {
        dragged = event.target.closest('.task-row');
        previousOrder = [...list.children];
        dropped = false;
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', dragged.dataset.taskId);
        dragged.classList.add('is-dragging');
    });

    list.addEventListener('dragover', (event) => {
        if (!dragged || !list.contains(dragged)) {
            event.dataTransfer.dropEffect = 'none';
            return;
        }

        event.preventDefault();
        const target = event.target.closest('.task-row');

        if (!target || target === dragged) {
            return;
        }

        const afterTarget = event.clientY > target.getBoundingClientRect().top + target.offsetHeight / 2;
        list.insertBefore(dragged, afterTarget ? target.nextSibling : target);
    });

    list.addEventListener('drop', (event) => {
        if (!dragged || !list.contains(dragged)) {
            return;
        }

        event.preventDefault();
        dropped = true;
        renderPriorities();
        saveOrder();
    });

    list.addEventListener('dragend', () => {
        dragged?.classList.remove('is-dragging');

        if (!dropped) {
            list.replaceChildren(...previousOrder);
        }
    });
});
