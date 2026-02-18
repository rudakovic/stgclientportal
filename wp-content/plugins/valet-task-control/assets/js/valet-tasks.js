function valetTaskToggle(el) {
    const taskId = el.dataset.taskId;
    const postId = el.dataset.postId;
    const completed = el.checked ? 1 : 0;
    const taskList = document.getElementById('valet-project-task-list');
    const spinner =  document.getElementById('valet-project-task-list-spinner');

    taskList.style.display = 'none';
    spinner.style.display = 'block';

    const formData = new FormData();
    formData.append('action', 'valet_update_task_status');
    formData.append('task_id', taskId);
    formData.append('completed', completed);
    formData.append('post_id', postId);
    formData.append('nonce', valetTasks.nonce);

    fetch(valetTasks.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                el.checked = !el.checked;
                taskList.style.display = 'block';
                spinner.style.display = 'none';
                alert('Error updating task');
            }
            taskList.style.display = 'block';
            spinner.style.display = 'none';
        });
}