document.addEventListener('DOMContentLoaded', function() {
    const addDepartmentBtn = document.getElementById('addDepartmentBtn');
    const departmentModal = document.getElementById('departmentModal');
    const closeBtn = document.querySelector('.close');
    const cancelBtn = document.getElementById('cancelBtn');
    const departmentForm = document.getElementById('departmentForm');
    const departmentsGrid = document.getElementById('departmentsGrid');
    const loadingSpinner = document.getElementById('loadingSpinner');

    addDepartmentBtn.addEventListener('click', openAddModal);
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    departmentForm.addEventListener('submit', handleFormSubmit);

    window.addEventListener('click', function(event) {
        if (event.target === departmentModal) {
            closeModal();
        }
    });

    loadDepartments();

    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add New Department';
        departmentForm.reset();
        document.getElementById('departmentId').value = '';
        document.getElementById('isActive').checked = true;
        departmentModal.style.display = 'block';
    }

    function openEditModal(department) {
        document.getElementById('modalTitle').textContent = 'Edit Department';
        document.getElementById('departmentId').value = department.department_id;
        document.getElementById('departmentName').value = department.department_name;
        document.getElementById('description').value = department.description || '';
        document.getElementById('isActive').checked = department.is_active == 1;
        departmentModal.style.display = 'block';
    }

    function closeModal() {
        departmentModal.style.display = 'none';
        departmentForm.reset();
    }

    function showLoading() {
        loadingSpinner.style.display = 'flex';
    }

    function hideLoading() {
        loadingSpinner.style.display = 'none';
    }

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        
        const content = document.querySelector('.content');
        content.insertBefore(alertDiv, content.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }

    async function loadDepartments() {
        showLoading();
        try {
            const response = await fetch('api/department_handler.php?action=getAll');
            const data = await response.json();
            
            if (data.success) {
                displayDepartments(data.departments);
            } else {
                showAlert(data.message || 'Failed to load departments', 'error');
                departmentsGrid.innerHTML = '<p class="no-data">Failed to load departments</p>';
            }
        } catch (error) {
            console.error('Error loading departments:', error);
            showAlert('Error loading departments', 'error');
            departmentsGrid.innerHTML = '<p class="no-data">Error loading departments</p>';
        } finally {
            hideLoading();
        }
    }

    function displayDepartments(departments) {
        if (!departments || departments.length === 0) {
            departmentsGrid.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                    <i class="fas fa-building" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <p style="color: #6c757d; font-size: 1.1rem;">No departments found. Click "Add New Department" to get started.</p>
                </div>
            `;
            return;
        }

        departmentsGrid.innerHTML = departments.map(dept => createDepartmentCard(dept)).join('');

        document.querySelectorAll('.edit-dept-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const deptId = this.dataset.id;
                const department = departments.find(d => d.department_id == deptId);
                if (department) {
                    openEditModal(department);
                }
            });
        });

        document.querySelectorAll('.delete-dept-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const deptId = this.dataset.id;
                const deptName = this.dataset.name;
                deleteDepartment(deptId, deptName);
            });
        });

        document.querySelectorAll('.toggle-dept-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const deptId = this.dataset.id;
                const isActive = this.dataset.active === '1';
                toggleDepartmentStatus(deptId, !isActive);
            });
        });
    }

    function createDepartmentCard(dept) {
        const isActive = dept.is_active == 1;
        const doctors = dept.doctors || [];
        
        return `
            <div class="department-card ${isActive ? '' : 'inactive'}">
                <div class="department-header">
                    <div class="department-title">
                        <h3>${escapeHtml(dept.department_name)}</h3>
                        <div class="department-status">
                            <span class="status-badge ${isActive ? 'active' : 'inactive'}">
                                ${isActive ? 'Active' : 'Inactive'}
                            </span>
                        </div>
                    </div>
                    <div class="department-actions">
                        <button class="icon-btn edit edit-dept-btn" data-id="${dept.department_id}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="icon-btn toggle toggle-dept-btn" 
                                data-id="${dept.department_id}" 
                                data-active="${dept.is_active}"
                                title="${isActive ? 'Deactivate' : 'Activate'}">
                            <i class="fas fa-${isActive ? 'eye-slash' : 'eye'}"></i>
                        </button>
                        <button class="icon-btn delete delete-dept-btn" 
                                data-id="${dept.department_id}" 
                                data-name="${escapeHtml(dept.department_name)}"
                                title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                
                ${dept.description ? `
                    <div class="department-description">
                        <p>${escapeHtml(dept.description)}</p>
                    </div>
                ` : ''}
                
                <div class="department-doctors">
                    <h4>
                        <i class="fas fa-user-doctor"></i>
                        Doctors (${doctors.length})
                    </h4>
                    ${doctors.length > 0 ? `
                        <div class="doctors-list">
                            ${doctors.map(doctor => `
                                <div class="doctor-item">
                                    <div class="doctor-info">
                                        <div class="doctor-name">${escapeHtml(doctor.doctor_name)}</div>
                                        <div class="doctor-phone">
                                            <i class="fas fa-phone"></i>
                                            ${doctor.phone || 'No phone number'}
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    ` : `
                        <div class="no-doctors">
                            <i class="fas fa-info-circle"></i> No doctors assigned
                        </div>
                    `}
                </div>
            </div>
        `;
    }

    async function handleFormSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(departmentForm);
        const departmentId = document.getElementById('departmentId').value;
        
        formData.set('is_active', document.getElementById('isActive').checked ? '1' : '0');
        
        showLoading();
        
        try {
            const action = departmentId ? 'update' : 'create';
            const response = await fetch(`api/department_handler.php?action=${action}`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert(data.message, 'success');
                closeModal();
                loadDepartments();
            } else {
                showAlert(data.message || 'Operation failed', 'error');
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            showAlert('Error submitting form', 'error');
        } finally {
            hideLoading();
        }
    }

    async function deleteDepartment(deptId, deptName) {
        if (!confirm(`Are you sure you want to delete "${deptName}"?\n\nThis action cannot be undone.`)) {
            return;
        }
        
        showLoading();
        
        try {
            const formData = new FormData();
            formData.append('department_id', deptId);
            
            const response = await fetch('api/department_handler.php?action=delete', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert(data.message, 'success');
                loadDepartments();
            } else {
                showAlert(data.message || 'Failed to delete department', 'error');
            }
        } catch (error) {
            console.error('Error deleting department:', error);
            showAlert('Error deleting department', 'error');
        } finally {
            hideLoading();
        }
    }

    async function toggleDepartmentStatus(deptId, newStatus) {
        showLoading();
        
        try {
            const formData = new FormData();
            formData.append('department_id', deptId);
            formData.append('is_active', newStatus ? '1' : '0');
            
            const response = await fetch('api/department_handler.php?action=toggleStatus', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert(data.message, 'success');
                loadDepartments();
            } else {
                showAlert(data.message || 'Failed to update status', 'error');
            }
        } catch (error) {
            console.error('Error toggling status:', error);
            showAlert('Error toggling status', 'error');
        } finally {
            hideLoading();
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
