// FeatureBoard - Frontend Application Logic (Tabbed Single-Page Dashboard)

// State
let state = {
  projects: [],
  selectedProjectId: null,
  features: [],
  stats: {
    total: 0,
    backlog: 0,
    planned: 0,
    in_progress: 0,
    released: 0
  },
  filters: {
    search: '',
    status: 'all',
    priority: 'all'
  }
};

// DOM Elements
const elements = {
  // Project Tabs Selectors
  projectTabsList: document.getElementById('project-tabs-list'),

  // Statistics
  statTotal: document.getElementById('stat-total'),
  statBacklog: document.getElementById('stat-backlog'),
  statPlanned: document.getElementById('stat-planned'),
  statInProgress: document.getElementById('stat-in-progress'),
  statReleased: document.getElementById('stat-released'),
  
  // Controls
  searchInput: document.getElementById('search-input'),
  filterStatus: document.getElementById('filter-status'),
  filterPriority: document.getElementById('filter-priority'),
  addFeatureBtn: document.getElementById('add-feature-btn'),
  
  // Board Columns
  colBacklog: document.getElementById('col-backlog'),
  colPlanned: document.getElementById('col-planned'),
  colInProgress: document.getElementById('col-in_progress'),
  colReleased: document.getElementById('col-released'),
  cardsBacklog: document.getElementById('cards-backlog'),
  cardsPlanned: document.getElementById('cards-planned'),
  cardsInProgress: document.getElementById('cards-in_progress'),
  cardsReleased: document.getElementById('cards-released'),
  
  countBacklog: document.getElementById('count-backlog'),
  countPlanned: document.getElementById('count-planned'),
  countInProgress: document.getElementById('count-in_progress'),
  countReleased: document.getElementById('count-released'),
  
  // Inspector Drawer Modal
  modalBackdrop: document.getElementById('feature-modal-backdrop'),
  modal: document.getElementById('feature-modal'),
  modalTitle: document.getElementById('modal-title'),
  modalForm: document.getElementById('feature-form'),
  featureId: document.getElementById('feature-id'),
  inputProject: document.getElementById('input-project'),
  newProjectFields: document.getElementById('new-project-fields'),
  inputNewProjectName: document.getElementById('input-new-project-name'),
  inputNewProjectCategory: document.getElementById('input-new-project-category'),
  inputNewProjectDescription: document.getElementById('input-new-project-description'),
  inputTitle: document.getElementById('input-title'),
  inputDescription: document.getElementById('input-description'),
  inputPriority: document.getElementById('input-priority'),
  inputStatus: document.getElementById('input-status'),
  cancelBtn: document.getElementById('cancel-btn'),
  closeModalBtn: document.getElementById('close-modal-btn'),
  deleteFeatureBtn: document.getElementById('delete-feature-btn'),
  
  // Toast container
  toastContainer: document.getElementById('toast-container')
};

// Init Application
document.addEventListener('DOMContentLoaded', () => {
  setupEventListeners();
  initProjects();
});

// Event Listeners Setup
function setupEventListeners() {
  // Toolbar Search and Filters
  elements.searchInput.addEventListener('input', (e) => {
    state.filters.search = e.target.value.toLowerCase().trim();
    renderBoard();
  });

  elements.filterStatus.addEventListener('change', (e) => {
    state.filters.status = e.target.value;
    renderBoard();
  });

  elements.filterPriority.addEventListener('change', (e) => {
    state.filters.priority = e.target.value;
    renderBoard();
  });

  // Modal Drawer Open/Close
  elements.addFeatureBtn.addEventListener('click', () => openModal());
  elements.cancelBtn.addEventListener('click', closeModal);
  elements.closeModalBtn.addEventListener('click', closeModal);
  
  elements.deleteFeatureBtn.addEventListener('click', () => {
    const id = elements.featureId.value;
    if (id) {
      handleDeleteFeature(id);
    }
  });

  // Close drawer when clicking backdrop overlay
  elements.modalBackdrop.addEventListener('click', (e) => {
    if (e.target === elements.modalBackdrop) closeModal();
  });

  // Target project dropdown toggle
  elements.inputProject.addEventListener('change', (e) => {
    if (e.target.value === 'new') {
      elements.newProjectFields.style.display = 'flex';
      elements.inputNewProjectName.setAttribute('required', 'true');
      elements.inputNewProjectName.focus();
    } else {
      elements.newProjectFields.style.display = 'none';
      elements.inputNewProjectName.removeAttribute('required');
    }
  });

  // Form Submit (Add or Edit)
  elements.modalForm.addEventListener('submit', handleFormSubmit);
}

// Initial Project Loading & Tab Generation
async function initProjects() {
  try {
    const res = await fetch('/api/projects');
    if (!res.ok) throw new Error('Failed to load projects list.');
    
    state.projects = await res.json();
    
    if (state.projects.length === 0) {
      showToast('No projects available. Please configure projects on the server.', 'error');
      return;
    }

    // Default to first project
    state.selectedProjectId = state.projects[0].id;
    
    rebuildProjectTabs();
    fetchProjectData();
  } catch (error) {
    showToast(error.message, 'error');
  }
}

// Rebuild Project Tab buttons dynamically
function rebuildProjectTabs() {
  elements.projectTabsList.innerHTML = '';
  
  // Sort projects alphabetically by name for a clean layout
  const sorted = [...state.projects].sort((a, b) => a.name.localeCompare(b.name));
  
  sorted.forEach(p => {
    const tabBtn = document.createElement('button');
    tabBtn.className = 'project-tab-btn';
    tabBtn.textContent = p.name;
    tabBtn.setAttribute('data-id', p.id);
    
    if (p.id === state.selectedProjectId) {
      tabBtn.classList.add('active');
    }

    // Add click handler to toggle projects
    tabBtn.addEventListener('click', () => {
      document.querySelectorAll('.project-tab-btn').forEach(btn => btn.classList.remove('active'));
      tabBtn.classList.add('active');

      state.selectedProjectId = p.id;
      fetchProjectData();
    });

    elements.projectTabsList.appendChild(tabBtn);
  });
}



// Fetch Stats and Features for current project
async function fetchProjectData() {
  if (!state.selectedProjectId) return;
  
  try {
    const [featuresRes, statsRes] = await Promise.all([
      fetch(`/api/projects/${state.selectedProjectId}/features`),
      fetch(`/api/projects/${state.selectedProjectId}/stats`)
    ]);

    if (!featuresRes.ok || !statsRes.ok) {
      throw new Error('Failed to fetch project roadmap data.');
    }

    state.features = await featuresRes.json();
    state.stats = await statsRes.json();

    renderStats();
    renderBoard();
  } catch (error) {
    showToast(error.message, 'error');
  }
}

// Render Dashboard Statistics
function renderStats() {
  elements.statTotal.textContent = state.stats.total;
  elements.statBacklog.textContent = state.stats.backlog;
  elements.statPlanned.textContent = state.stats.planned;
  elements.statInProgress.textContent = state.stats.in_progress;
  elements.statReleased.textContent = state.stats.released;
}

// Render Kanban Board
function renderBoard() {
  // Clear columns
  elements.cardsBacklog.innerHTML = '';
  elements.cardsPlanned.innerHTML = '';
  elements.cardsInProgress.innerHTML = '';
  elements.cardsReleased.innerHTML = '';

  // Filter Features
  const filteredFeatures = state.features.filter(f => {
    // 1. Search filter (title or description)
    const matchesSearch = !state.filters.search || 
      f.title.toLowerCase().includes(state.filters.search) || 
      (f.description && f.description.toLowerCase().includes(state.filters.search));

    // 2. Status filter
    const matchesStatus = state.filters.status === 'all' || 
      f.status.toLowerCase() === state.filters.status.toLowerCase();

    // 3. Priority filter
    const matchesPriority = state.filters.priority === 'all' || 
      f.priority.toLowerCase() === state.filters.priority.toLowerCase();

    return matchesSearch && matchesStatus && matchesPriority;
  });

  // Group and Render
  const counts = { backlog: 0, planned: 0, in_progress: 0, released: 0 };

  filteredFeatures.forEach(feature => {
    const card = createFeatureCard(feature);
    const statusKey = feature.status.toLowerCase();

    if (statusKey === 'backlog') {
      elements.cardsBacklog.appendChild(card);
      counts.backlog++;
    } else if (statusKey === 'planned') {
      elements.cardsPlanned.appendChild(card);
      counts.planned++;
    } else if (statusKey === 'in_progress') {
      elements.cardsInProgress.appendChild(card);
      counts.in_progress++;
    } else if (statusKey === 'released') {
      elements.cardsReleased.appendChild(card);
      counts.released++;
    }
  });

  // Update Column Counts
  elements.countBacklog.textContent = counts.backlog;
  elements.countPlanned.textContent = counts.planned;
  elements.countInProgress.textContent = counts.in_progress;
  elements.countReleased.textContent = counts.released;

  // Render empty state placeholder if column has no cards
  Object.keys(counts).forEach(status => {
    const container = status === 'in_progress' ? elements.cardsInProgress : elements[`cards${capitalize(status)}`];
    if (counts[status] === 0) {
      const placeholder = document.createElement('div');
      placeholder.className = 'column-placeholder';
      placeholder.style.cssText = 'width: 290px; height: 110px; display: flex; align-items: center; justify-content: center; border: 2px dashed #cbd5e1; border-radius: 8px; color: #94a3b8; font-size: 0.8rem; font-weight: 500; flex-shrink: 0;';
      placeholder.textContent = 'No features found';
      container.appendChild(placeholder);
    }
  });

  // Show/Hide column lanes based on active filters and card counts
  const activeStatusFilter = state.filters.status.toLowerCase();
  const totalProjectFeatures = state.features.length;
  const lanes = {
    backlog: elements.colBacklog,
    planned: elements.colPlanned,
    in_progress: elements.colInProgress,
    released: elements.colReleased
  };

  Object.keys(lanes).forEach(statusKey => {
    const laneEl = lanes[statusKey];
    if (!laneEl) return;
    
    // If the project is completely empty of features, show all lanes for context
    if (totalProjectFeatures === 0) {
      laneEl.style.display = 'flex';
      return;
    }

    if (activeStatusFilter !== 'all') {
      if (activeStatusFilter === statusKey) {
        laneEl.style.display = 'flex';
      } else {
        laneEl.style.display = 'none';
      }
    } else {
      // If status filter is 'all', show the lane only if it has matching features
      if (counts[statusKey] > 0) {
        laneEl.style.display = 'flex';
      } else {
        laneEl.style.display = 'none';
      }
    }
  });
}

// Create Feature Card Element
function createFeatureCard(feature) {
  const card = document.createElement('div');
  card.className = 'feature-card';
  card.setAttribute('data-id', feature.id);

  // Capitalize Status/Priority for badges
  const displayPriority = capitalize(feature.priority);
  const displayStatus = feature.status === 'in_progress' ? 'In Progress' : capitalize(feature.status);
  
  // Format date
  const dateStr = new Date(feature.created_at).toLocaleDateString(undefined, {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });

  card.innerHTML = `
    <h4 class="card-title">${escapeHTML(feature.title)}</h4>
    <p class="card-desc">${feature.description ? escapeHTML(feature.description) : '<i>No description provided.</i>'}</p>
    <div class="card-badges">
      <span class="badge badge-priority-${feature.priority.toLowerCase()}">${displayPriority}</span>
      <span class="badge badge-status">${displayStatus}</span>
    </div>
    <div class="card-date">Created on ${dateStr}</div>
    <div class="card-actions">
      <button class="btn-primary-link edit-card-btn" data-id="${feature.id}">Edit</button>
      <button class="btn-danger-link delete-card-btn" data-id="${feature.id}">Delete</button>
    </div>
  `;

  // Attach card action handlers
  card.querySelector('.edit-card-btn').addEventListener('click', (e) => {
    e.stopPropagation(); // prevent card container click triggers
    openModal(feature);
  });
  card.querySelector('.delete-card-btn').addEventListener('click', (e) => {
    e.stopPropagation();
    handleDeleteFeature(feature.id);
  });

  // Click card container to open edit drawer
  card.addEventListener('click', () => openModal(feature));

  return card;
}

// Open Add/Edit Inspector Drawer Modal
function openModal(feature = null) {
  elements.modalForm.reset();
  elements.newProjectFields.style.display = 'none';
  elements.inputNewProjectName.removeAttribute('required');

  // Populate Target Project options
  elements.inputProject.innerHTML = '';
  state.projects.forEach(p => {
    const opt = document.createElement('option');
    opt.value = p.id;
    opt.textContent = p.name;
    elements.inputProject.appendChild(opt);
  });

  if (feature) {
    // Edit Mode (Hide "+ Create New Project..." choice, Show Delete Feature button)
    elements.modalTitle.textContent = 'Edit Feature Idea';
    elements.featureId.value = feature.id;
    elements.inputProject.value = feature.project_id;
    elements.inputTitle.value = feature.title;
    elements.inputDescription.value = feature.description || '';
    elements.inputPriority.value = feature.priority.toLowerCase();
    elements.inputStatus.value = feature.status.toLowerCase();
    elements.deleteFeatureBtn.style.display = 'inline-flex';
  } else {
    // Add Mode (Include "+ Create New Project..." choice, Hide Delete Feature button)
    elements.modalTitle.textContent = 'Add Feature Idea';
    elements.featureId.value = '';
    
    const newOpt = document.createElement('option');
    newOpt.value = 'new';
    newOpt.textContent = '+ Create New Project...';
    elements.inputProject.appendChild(newOpt);

    // Default to currently active project
    elements.inputProject.value = state.selectedProjectId || state.projects[0]?.id;
    
    // Defaults
    elements.inputPriority.value = 'medium';
    elements.inputStatus.value = 'backlog';
    elements.deleteFeatureBtn.style.display = 'none';
  }
  
  elements.modalBackdrop.style.display = 'flex';
  elements.inputTitle.focus();
}

// Close Modal
function closeModal() {
  elements.modalBackdrop.style.display = 'none';
  elements.modalForm.reset();
}

// Form Submit Handler (POST/PUT)
async function handleFormSubmit(e) {
  e.preventDefault();

  const id = elements.featureId.value;
  const title = elements.inputTitle.value.trim();
  const description = elements.inputDescription.value.trim();
  const priority = elements.inputPriority.value;
  const status = elements.inputStatus.value;
  const projectSelectVal = elements.inputProject.value;

  if (!title) {
    showToast('Title is required.', 'error');
    return;
  }

  try {
    let targetProjectId;

    // 1. Check if creating a new project first
    if (projectSelectVal === 'new') {
      const newProjName = elements.inputNewProjectName.value.trim();
      const newProjCategory = elements.inputNewProjectCategory.value.trim();
      const newProjDescription = elements.inputNewProjectDescription.value.trim();

      if (!newProjName) {
        showToast('New project name is required.', 'error');
        return;
      }

      // Create project
      const projRes = await fetch('/api/projects', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: newProjName,
          category: newProjCategory,
          description: newProjDescription
        })
      });

      const newProj = await projRes.json();
      if (!projRes.ok) {
        throw new Error(newProj.error || 'Failed to create new project.');
      }

      // Insert project to local state
      state.projects.push(newProj);
      targetProjectId = newProj.id;
      
      // Rebuild the project tabs
      rebuildProjectTabs();
    } else {
      targetProjectId = parseInt(projectSelectVal);
    }

    // 2. Create or Update the Feature
    const payload = { 
      project_id: targetProjectId, 
      title, 
      description, 
      priority, 
      status 
    };
    const url = id ? `/api/features/${id}` : '/api/features';
    const method = id ? 'PUT' : 'POST';

    const res = await fetch(url, {
      method: method,
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    const data = await res.json();
    if (!res.ok) {
      throw new Error(data.error || 'Failed to save feature.');
    }

    showToast(
      id ? 'Feature updated successfully.' : 'Feature created successfully.', 
      'success'
    );
    
    closeModal();

    // Switch view to the target project of this feature
    state.selectedProjectId = targetProjectId;
    
    // Highlight the active tab
    document.querySelectorAll('.project-tab-btn').forEach(btn => {
      const tabId = parseInt(btn.getAttribute('data-id'));
      if (tabId === targetProjectId) {
        btn.classList.add('active');
      } else {
        btn.classList.remove('active');
      }
    });

    fetchProjectData(); // Reload stats and features
  } catch (error) {
    showToast(error.message, 'error');
  }
}

// Delete Feature Handler
async function handleDeleteFeature(id) {
  const confirmed = confirm('Are you sure you want to delete this feature?');
  if (!confirmed) return;

  try {
    const res = await fetch(`/api/features/${id}`, {
      method: 'DELETE'
    });

    const data = await res.json();

    if (!res.ok) {
      throw new Error(data.error || 'Failed to delete feature.');
    }

    showToast('Feature deleted successfully.', 'success');
    closeModal();
    fetchProjectData(); // Reload stats and features
  } catch (error) {
    showToast(error.message, 'error');
  }
}

// Toast Notifications System
function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  
  toast.innerHTML = `
    <span class="toast-message">${escapeHTML(message)}</span>
    <button class="toast-close">&times;</button>
  `;

  // Auto-remove toast after 4 seconds
  const autoRemoveTimer = setTimeout(() => {
    removeToast(toast);
  }, 4000);

  // Close button click handler
  toast.querySelector('.toast-close').addEventListener('click', () => {
    clearTimeout(autoRemoveTimer);
    removeToast(toast);
  });

  elements.toastContainer.appendChild(toast);
}

function removeToast(toast) {
  toast.style.animation = 'fadeOut 0.3s ease-out forwards';
  toast.addEventListener('animationend', () => {
    toast.remove();
  });
}

// Helper: capitalize string
function capitalize(str) {
  if (!str) return '';
  return str.split('_')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
}

// Helper: escape HTML to prevent XSS
function escapeHTML(str) {
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
