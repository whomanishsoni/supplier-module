$(document).ready(function() {
  // Initialize variables
  let currentPage = 1;
  let itemsPerPage = 10;
  let globalSearch = '';
  let nameSearch = '';
  let emailSearch = '';
  let statusFilter = '';
  let selectedRows = []; // Store IDs as strings
  let sortConfig = { key: 'id', direction: 'asc' };

  // DOM elements
  const globalSearchInput = $('#global-search');
  const nameSearchInput = $('#name-search');
  const emailSearchInput = $('#email-search');
  const statusFilterSelect = $('#status-filter');
  const addSupplierBtn = $('#add-supplier-btn');
  const deleteSelectedBtn = $('#delete-selected-btn');
  const selectAllCheckbox = $('#select-all');
  const tableBody = $('#table-body');
  const itemsPerPageSelect = $('#items-per-page');
  const paginationInfo = $('#pagination-info');
  const pagination = $('#pagination');
  const supplierModal = $('#supplier-modal');
  const supplierForm = $('#supplier-form');
  const viewSupplierModal = $('#view-supplier-modal');
  const modalTitle = $('#modal-title');
  const modalClose = $('#modal-close');
  const modalSubmit = $('#modal-submit');
  const viewModalClose = $('#view-modal-close');
  const toggleGlobal = $('#toggle-global');
  const toggleFilters = $('#toggle-filters');
  const filtersContainer = $('#filters-container');

  // Debug: Verify jQuery loaded
  console.log('jQuery loaded:', typeof $ !== 'undefined');

  // Show notification
  function showNotification(message, type) {
    const notification = $('#notification');
    const notificationText = $('#notification-text');
    if (notification.length === 0 || notificationText.length === 0) {
      console.warn('Notification element or text container not found');
      return;
    }
    notificationText.text(message || 'Unknown message');
    notification
      .removeClass('hidden success error')
      .addClass(type === 'success' || type === 'error' ? type : 'error')
      .fadeIn();
    setTimeout(() => notification.fadeOut(), 3000);
  }

  // Cleanup old dropdowns
  function cleanupDropdowns() {
    $('[id^="dropdown-"]').remove();
  }

  // Fetch and render table data
  function renderTable() {
    console.log('Rendering table, page:', currentPage, 'selectedRows:', selectedRows);
    $.ajax({
      url: 'api/read.php',
      type: 'GET',
      data: {
        draw: currentPage,
        start: (currentPage - 1) * itemsPerPage,
        length: itemsPerPage,
        search: { value: globalSearch },
        status_filter: statusFilter,
        name_filter: nameSearch,
        email_filter: emailSearch,
        order: [{ column: ['id', 'name', 'email', 'phone', 'status', 'created_at', 'updated_at'].indexOf(sortConfig.key) + 1, dir: sortConfig.direction }]
      },
      dataType: 'json',
      success: function(response) {
        console.log('API response:', response);
        if (!response || !response.recordsFiltered || !Array.isArray(response.data)) {
          console.log('Invalid API response:', response);
          tableBody.html(`<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Error loading data</td></tr>`);
          pagination.empty();
          paginationInfo.text('');
          showNotification('Invalid data from server', 'error');
          return;
        }

        tableBody.empty();
        cleanupDropdowns(); // Remove old dropdowns before rendering new ones
        if (response.data.length === 0) {
          tableBody.html(`<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No results found</td></tr>`);
        } else {
          response.data.forEach((supplier, index) => {
            const supplierId = String(supplier.id);
            console.log('Rendering row for supplier ID:', supplierId);
            if (!supplierId) {
              console.warn('Invalid supplier ID:', supplier);
            }
            const row = `
              <tr class="bg-white border-b hover:bg-gray-50">
                <td class="px-2 sm:px-4 py-3 sm:py-4">
                  <input type="checkbox" class="row-checkbox w-4 h-4 rounded focus:ring-2 focus:ring-blue-500" data-id="${supplierId}" ${selectedRows.includes(supplierId) ? 'checked' : ''} aria-label="Select supplier ${supplierId}">
                </td>
                <td class="px-2 sm:px-4 py-3 sm:py-4">${supplierId || 'N/A'}</td>
                <td class="px-2 sm:px-4 py-3 sm:py-4 font-medium text-gray-900">${supplier.name || 'N/A'}</td>
                <td class="px-2 sm:px-4 py-3 sm:py-4 hidden sm:table-cell">${supplier.email || 'N/A'}</td>
                <td class="px-2 sm:px-4 py-3 sm:py-4 hidden sm:table-cell">${supplier.phone || 'N/A'}</td>
                <td class="px-2 sm:px-4 py-3 sm:py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs sm:text-sm font-medium shadow-sm
                    ${supplier.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${supplier.status || 'N/A'}
                  </span>
                </td>
                <td class="px-2 sm:px-4 py-3 sm:py-4">
                  <div class="relative flex items-center space-x-1 sm:space-x-2">
                    <button class="toggle-details sm:hidden p-1.5 sm:p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors" data-id="${supplierId}" title="More Info" aria-label="Toggle details for supplier ${supplierId}" aria-expanded="false">
                      <svg class="w-4 h-4 sm:w-5 sm:h-5 detail-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                    </button>
                    <button id="dropdown-trigger-${supplierId}" class="p-1.5 sm:p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors" title="Actions" aria-label="Actions for supplier ${supplierId}">
                      <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr class="details-row hidden sm:hidden bg-gray-50" data-id="${supplierId}" aria-hidden="true">
                <td colspan="7" class="px-3 sm:px-4 py-3">
                  <div class="flex flex-col space-y-2 border-l-4 border-gray-200 pl-4">
                    <div><span class="font-medium text-gray-700">Email:</span> ${supplier.email || 'N/A'}</div>
                    <div><span class="font-medium text-gray-700">Phone:</span> ${supplier.phone || 'N/A'}</div>
                    <div><span class="font-medium text-gray-700">Address:</span> ${supplier.address || 'N/A'}</div>
                  </div>
                </td>
              </tr>
            `;
            tableBody.append(row);

            // Initialize dropdown after appending row
            const triggerEl = document.getElementById(`dropdown-trigger-${supplierId}`);
            const dropdownContent = `
              <div id="dropdown-${supplierId}" class="z-50 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44">
                <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdown-${supplierId}">
                  <li>
                    <button data-action="show" data-id="${supplierId}" class="flex items-center w-full px-4 py-2 hover:bg-gray-100">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      Show
                    </button>
                  </li>
                  <li>
                    <button data-action="edit" data-id="${supplierId}" class="flex items-center w-full px-4 py-2 hover:bg-gray-100">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      Edit
                    </button>
                  </li>
                  <li>
                    <button data-action="delete" data-id="${supplierId}" class="flex items-center w-full px-4 py-2 hover:bg-gray-100 text-red-600">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      Delete
                    </button>
                  </li>
                </ul>
              </div>
            `;
            $('body').append(dropdownContent);
            new Dropdown(document.getElementById(`dropdown-${supplierId}`), triggerEl, {
              placement: 'bottom-end',
              appendTo: document.body
            });
          });
        }

        // Update pagination with truncation
        const totalPages = Math.ceil(response.recordsFiltered / itemsPerPage);
        paginationInfo.text(`Showing ${(currentPage - 1) * itemsPerPage + 1} to ${Math.min(currentPage * itemsPerPage, response.recordsFiltered)} of ${response.recordsFiltered} entries`);
        pagination.empty();
        pagination.append(`
          <li>
            <button id="prev-page" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 ${currentPage === 1 ? 'opacity-50 pointer-events-none' : ''}">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
            </button>
          </li>
        `);

        const maxVisiblePages = 3;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        if (endPage - startPage < maxVisiblePages - 1) {
          startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        if (startPage > 1) {
          pagination.append(`
            <li>
              <button class="page-btn px-3 py-2 leading-tight border border-gray-300 text-gray-500 bg-white hover:bg-gray-100" data-page="1">
                1
              </button>
            </li>
          `);
          if (startPage > 2) {
            pagination.append(`
              <li>
                <span class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300">...</span>
              </li>
            `);
          }
        }

        for (let i = startPage; i <= endPage; i++) {
          pagination.append(`
            <li>
              <button class="page-btn px-3 py-2 leading-tight border border-gray-300 ${currentPage === i ? 'text-blue-600 bg-blue-50' : 'text-gray-500 bg-white hover:bg-gray-100'}" data-page="${i}">
                ${i}
              </button>
            </li>
          `);
        }

        if (endPage < totalPages) {
          if (endPage < totalPages - 1) {
            pagination.append(`
              <li>
                <span class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300">...</span>
              </li>
            `);
          }
          pagination.append(`
            <li>
              <button class="page-btn px-3 py-2 leading-tight border border-gray-300 text-gray-500 bg-white hover:bg-gray-100" data-page="${totalPages}">
                ${totalPages}
              </button>
            </li>
          `);
        }

        pagination.append(`
          <li>
            <button id="next-page" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 ${currentPage === totalPages ? 'opacity-50 pointer-events-none' : ''}">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
          </li>
        `);

        // Update select all checkbox
        selectAllCheckbox.prop('checked', response.data.length > 0 && response.data.every(supplier => selectedRows.includes(String(supplier.id))));
      },
      error: function(xhr) {
        console.log('API error:', xhr.responseText);
        showNotification('Error fetching data: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
        tableBody.html(`<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Error loading data</td></tr>`);
        pagination.empty();
        paginationInfo.text('');
      }
    });
  }

  // Handle sorting
  function handleSort(key) {
    sortConfig = {
      key: key,
      direction: sortConfig.key === key && sortConfig.direction === 'asc' ? 'desc' : 'asc'
    };
    $('th[data-sort]').each(function() {
      const icon = $(this).find('.sort-icon');
      if ($(this).data('sort') === key) {
        icon.html(`<svg class="w-4 h-4 sort-icon ${sortConfig.direction === 'asc' ? 'sort-asc' : 'sort-desc'}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>`);
      } else {
        icon.empty();
      }
    });
    currentPage = 1;
    renderTable();
  }

  // Open modal
  function openModal(type, supplier = null) {
    modalTitle.text(type === 'show' ? 'View Supplier' : type === 'edit' ? 'Edit Supplier' : 'Add Supplier');
    modalSubmit.toggleClass('hidden', type === 'show');
    modalClose.text(type === 'show' ? 'Close' : 'Cancel');
    modalSubmit.text(type === 'edit' ? 'Update' : 'Create');

    if (type === 'show') {
      $('#view-name').text(supplier.name || 'N/A');
      $('#view-email').text(supplier.email || 'N/A');
      $('#view-phone').text(supplier.phone || 'N/A');
      $('#view-address').text(supplier.address || 'N/A');
      $('#view-status').text(supplier.status || 'N/A');
      $('#view-created-at').text(supplier.created_at || 'N/A');
      $('#view-updated-at').text(supplier.updated_at || 'N/A');
      viewSupplierModal.removeClass('hidden');
    } else {
      $('#supplier-id').val(supplier ? supplier.id : '');
      $('#modal-name').val(supplier ? supplier.name : '');
      $('#modal-email').val(supplier ? supplier.email : '');
      $('#modal-phone').val(supplier ? supplier.phone : '');
      $('#modal-address').val(supplier ? supplier.address : '');
      $('#modal-status').val(supplier ? supplier.status : 'active');
      supplierModal.removeClass('hidden');
    }
  }

  // Event listeners
  globalSearchInput.on('input', function() {
    globalSearch = $(this).val();
    currentPage = 1;
    renderTable();
  });

  nameSearchInput.on('input', function() {
    nameSearch = $(this).val();
    currentPage = 1;
    renderTable();
  });

  emailSearchInput.on('input', function() {
    emailSearch = $(this).val();
    currentPage = 1;
    renderTable();
  });

  statusFilterSelect.on('change', function() {
    statusFilter = $(this).val();
    currentPage = 1;
    renderTable();
  });

  itemsPerPageSelect.on('change', function() {
    itemsPerPage = Number($(this).val());
    currentPage = 1;
    selectedRows = [];
    renderTable();
  });

  selectAllCheckbox.on('change', function() {
    const isChecked = $(this).is(':checked');
    const checkboxes = $('#table-body .row-checkbox');
    console.log('Select All checked:', isChecked, 'Found checkboxes:', checkboxes.length);
    selectedRows = isChecked ? checkboxes.map(function() {
      const id = String($(this).data('id'));
      if (id) return id;
      console.warn('Invalid checkbox ID:', $(this).data('id'));
      return null;
    }).get().filter(id => id !== null) : [];
    checkboxes.prop('checked', isChecked); // Force checkbox state
    console.log('Selected Rows:', selectedRows);
    deleteSelectedBtn.prop('disabled', selectedRows.length === 0);
    deleteSelectedBtn.attr('title', `Delete Selected (${selectedRows.length})`);
  });

  deleteSelectedBtn.on('click', function() {
    if (selectedRows.length > 0 && confirm(`Are you sure you want to delete ${selectedRows.length} supplier(s)?`)) {
      $.ajax({
        url: 'api/bulk_delete.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ ids: selectedRows }),
        success: function(response) {
          if (response && response.message && response.status) {
            showNotification(response.message, response.status);
          } else {
            showNotification('Invalid response from server', 'error');
          }
          selectedRows = [];
          currentPage = 1;
          renderTable();
        },
        error: function(xhr) {
          showNotification('Error deleting suppliers: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
        }
      });
    }
  });

  addSupplierBtn.on('click', () => openModal('create'));

  // Close modal when clicking outside
  supplierModal.on('click', function(e) {
    if (e.target === this) {
      $(this).addClass('hidden');
    }
  });
  viewSupplierModal.on('click', function(e) {
    if (e.target === this) {
      $(this).addClass('hidden');
    }
  });

  modalClose.on('click', () => supplierModal.addClass('hidden'));
  viewModalClose.on('click', () => viewSupplierModal.addClass('hidden'));

  supplierForm.on('submit', function(e) {
    e.preventDefault();
    const id = $('#supplier-id').val();
    const data = {
      name: $('#modal-name').val(),
      email: $('#modal-email').val(),
      phone: $('#modal-phone').val(),
      address: $('#modal-address').val(),
      status: $('#modal-status').val()
    };
    const url = id ? 'api/update.php' : 'api/create.php';
    if (id) data.id = id;

    $.ajax({
      url: url,
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(data),
      success: function(response) {
        if (response && response.message && response.status) {
          showNotification(response.message, response.status);
          supplierModal.addClass('hidden'); // Close modal after successful submission
          currentPage = 1;
          renderTable();
        } else {
          showNotification('Invalid response from server', 'error');
        }
      },
      error: function(xhr) {
        showNotification('Error saving supplier: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
      }
    });
  });

  toggleGlobal.on('click', function() {
    globalSearchInput.toggleClass('hidden');
  });

  toggleFilters.on('click', function() {
    filtersContainer.toggleClass('hidden');
  });

  $('#app').on('click', '#notification-close', function() {
    $('#notification').fadeOut();
  });

  $('#app').on('click', '.page-btn', function() {
    currentPage = Number($(this).data('page'));
    selectedRows = [];
    renderTable();
  }).on('click', '#prev-page', function() {
    if (currentPage > 1) {
      currentPage--;
      selectedRows = [];
      renderTable();
    }
  }).on('click', '#next-page', function() {
    $.ajax({
      url: 'api/read.php',
      type: 'GET',
      data: { length: itemsPerPage, start: 0, search: { value: globalSearch }, status_filter: statusFilter, name_filter: nameSearch, email_filter: emailSearch },
      dataType: 'json',
      success: function(response) {
        const totalPages = Math.ceil(response.recordsFiltered / itemsPerPage);
        if (currentPage < totalPages) {
          currentPage++;
          selectedRows = [];
          renderTable();
        }
      },
      error: function(xhr) {
        console.log('Error fetching total pages:', xhr.responseText);
      }
    });
  }).on('click', 'th[data-sort]', function() {
    const key = $(this).data('sort');
    handleSort(key);
  }).on('click', '.toggle-details', function() {
    const id = String($(this).data('id'));
    const detailsRow = $(`.details-row[data-id="${id}"]`);
    const isVisible = detailsRow.is(':visible');
    detailsRow.toggleClass('hidden');
    $(this).find('.detail-icon').html(
      isVisible
        ? `<svg class="w-4 h-4 sm:w-5 sm:h-5 detail-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>`
        : `<svg class="w-4 h-4 sm:w-5 sm:h-5 detail-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>`
    );
    $(this).attr('aria-expanded', !isVisible);
    detailsRow.attr('aria-hidden', isVisible);
  });

  // Delegated event listener for dropdown action buttons
  $(document).on('click', 'button[data-action]', function(e) {
    const btn = $(this);
    const id = String(btn.data('id'));
    const action = btn.data('action');
    console.log('Action button clicked:', action, 'ID:', id);

    if (action === 'show' || action === 'edit') {
      $.ajax({
        url: 'api/single_read.php',
        type: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
          if (response && response.id) {
            openModal(action, response);
            $('#dropdown-' + id).addClass('hidden'); // Hide dropdown after action
          } else {
            showNotification(response.message || 'Error: Supplier not found', 'error');
          }
        },
        error: function(xhr) {
          showNotification('Error fetching supplier: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
        }
      });
    } else if (action === 'delete') {
      if (confirm('Are you sure you want to delete this supplier?')) {
        $.ajax({
          url: 'api/delete.php',
          type: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({ id: id }),
          success: function(response) {
            if (response && response.message && response.status) {
              showNotification(response.message, response.status);
            } else {
              showNotification('Invalid response from server', 'error');
            }
            selectedRows = selectedRows.filter(rowId => rowId !== id);
            currentPage = 1;
            renderTable();
            $('#dropdown-' + id).addClass('hidden'); // Hide dropdown after action
          },
          error: function(xhr) {
            showNotification('Error deleting supplier: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
          }
        });
      }
    }
  });

  // Rebind row checkbox event listeners
  tableBody.on('click', '.row-checkbox', function() {
    const id = String($(this).data('id'));
    console.log('Row checkbox clicked, ID:', id);
    if (id) {
      selectedRows = selectedRows.includes(id) ? selectedRows.filter(rowId => rowId !== id) : [...selectedRows, id];
      $(this).prop('checked', selectedRows.includes(id)); // Force checkbox state
      console.log('Selected Rows:', selectedRows);
      deleteSelectedBtn.prop('disabled', selectedRows.length === 0);
      deleteSelectedBtn.attr('title', `Delete Selected (${selectedRows.length})`);
      selectAllCheckbox.prop('checked', tableBody.find('.row-checkbox').length > 0 && tableBody.find('.row-checkbox').length === selectedRows.length);
    } else {
      console.warn('Invalid row checkbox ID:', $(this).data('id'));
    }
  });

  // Initial render
  renderTable();
});