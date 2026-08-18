import './bootstrap';
import '../css/app.css';

const list = document.querySelector('.task-list');
const listWrap = document.querySelector('.task-list-wrap');

if (list && listWrap?.dataset.reorderable === 'true') {
    let dragged;
    let previousOrder;
    let dropped = false;

    const message = (text, error = false) => {
        const element = document.querySelector('.reorder-message');
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
        dragged.classList.add('is-dragging');
    });

    list.addEventListener('dragover', (event) => {
        event.preventDefault();
        const target = event.target.closest('.task-row');

        if (!target || target === dragged) {
            return;
        }

        const afterTarget = event.clientY > target.getBoundingClientRect().top + target.offsetHeight / 2;
        list.insertBefore(dragged, afterTarget ? target.nextSibling : target);
    });

    list.addEventListener('drop', (event) => {
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
}
