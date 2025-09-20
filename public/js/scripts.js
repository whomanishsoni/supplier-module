$(document).ready(function() {
    // Initialize Bootstrap modals
    const supplierModal = new bootstrap.Modal(document.getElementById('supplierModal'), { keyboard: false });
    const viewSupplierModal = new bootstrap.Modal(document.getElementById('viewSupplierModal'), { keyboard: false });

    // Initialize DataTable
    let table = $('#suppliersTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: {
            details: {
                type: 'inline',
                renderer: function(api, rowIdx, columns) {
                    var data = api.row(rowIdx).data();
                    if (!data) return false;

                    var detailData = `
                        <div class="p-2 bg-gray-50">
                            <p class="mb-1"><strong>Name:</strong> ${data.name || 'N/A'}</p>
                            <p class="mb-1"><strong>Email:</strong> ${data.email || 'N/A'}</p>
                            <p class="mb-1"><strong>Phone:</strong> ${data.phone || 'N/A'}</p>
                            <p class="mb-1"><strong>Address:</strong> ${data.address || 'N/A'}</p>
                            <p class="mb-1"><strong>Status:</strong> ${data.status || 'N/A'}</p>
                            <p class="mb-1"><strong>Created At:</strong> ${data.created_at || 'N/A'}</p>
                        </div>
                    `;
                    return $(detailData);
                }
            }
        },
        ajax: {
            url: 'api/read.php',
            type: 'GET',
            data: function(d) {
                d.status_filter = $('#statusFilter').val();
                d.name_filter = $('#nameFilter').val();
                d.email_filter = $('#emailFilter').val();
            }
        },
        order: [[7, 'desc']], // Default sort by created_at
        columns: [
            {
                data: null,
                render: function(data, type, row) {
                    return `<input type="checkbox" class="select-row" value="${row.id || ''}">`;
                },
                orderable: false,
                responsivePriority: 1,
                className: 'dt-checkboxes'
            },
            {
                data: null,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                orderable: false,
                responsivePriority: 2
            },
            { data: 'name', responsivePriority: 1 },
            { data: 'email', responsivePriority: 3 },
            { data: 'phone', responsivePriority: 4 },
            { data: 'address', responsivePriority: 5 },
            { data: 'status', responsivePriority: 2 },
            { data: 'created_at', responsivePriority: 6 },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button class="btn-action view-btn" data-id="${row.id || ''}" title="View"><i class="fas fa-eye text-green-500 hover:text-green-700"></i></button>
                        <button class="btn-action edit-btn" data-id="${row.id || ''}" title="Edit"><i class="fas fa-edit text-blue-500 hover:text-blue-700"></i></button>
                        <button class="btn-action delete-btn" data-id="${row.id || ''}" title="Delete"><i class="fas fa-trash-alt text-red-500 hover:text-red-700"></i></button>
                    `;
                },
                orderable: false,
                responsivePriority: 1
            }
        ],
        columnDefs: [
            {
                targets: 0, // Checkbox column
                orderable: false,
                createdCell: function(td, cellData, rowData, row, col) {
                    $(td).on('click', '.select-row', function(e) {
                        e.stopPropagation(); // Prevent row expansion on checkbox click
                    });
                }
            }
        ],
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        dom: 'lfrtip' // Default layout with length, filter, table, info, pagination
    });

    // Function to update Delete Selected button text
    function updateDeleteButtonText() {
        const selectedCount = $('.select-row:checked').length;
        $('#deleteAllBtn').text(`Delete Selected (${selectedCount})`);
    }

    // Initialize Delete Selected button text
    updateDeleteButtonText();

    // Reload table when filters change
    $('#statusFilter, #nameFilter, #emailFilter').on('change input', function() {
        table.ajax.reload(function() {
            updateDeleteButtonText();
        });
    });

    // Select All Checkbox
    $('#selectAllCheckbox').on('change', function() {
        $('.select-row').prop('checked', $(this).is(':checked'));
        updateDeleteButtonText();
    });

    $(document).on('change', '.select-row', function() {
        if ($('.select-row:checked').length === $('.select-row').length) {
            $('#selectAllCheckbox').prop('checked', true);
        } else {
            $('#selectAllCheckbox').prop('checked', false);
        }
        updateDeleteButtonText();
    });

    // Show notification
    function showNotification(message, type) {
        const notification = $('#notification');
        notification.text(message).removeClass('hidden success error').addClass(type).fadeIn();
        setTimeout(() => notification.fadeOut(), 3000);
    }

    // Add Supplier
    $('#addSupplierBtn').click(function() {
        $('#supplierModalLabel').text('Add Supplier');
        $('#supplierForm')[0].reset();
        $('#supplierId').val('');
        supplierModal.show();
    });

    // View Supplier
    $(document).on('click', '.view-btn', function() {
        const id = $(this).data('id');
        $.ajax({
            url: 'api/single_read.php',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response && response.id) {
                    $('#viewName').text(response.name || '');
                    $('#viewEmail').text(response.email || '');
                    $('#viewPhone').text(response.phone || '');
                    $('#viewAddress').text(response.address || '');
                    $('#viewStatus').text(response.status || 'active');
                    $('#viewCreatedAt').text(response.created_at || '');
                    $('#viewSupplierModalLabel').text('View Supplier');
                    viewSupplierModal.show();
                } else {
                    showNotification(response.message || 'Error: Supplier not found', 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotification('Error fetching supplier: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
            }
        });
    });

    // Edit Supplier
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        $.ajax({
            url: 'api/single_read.php',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response && response.id) {
                    $('#supplierId').val(response.id);
                    $('#name').val(response.name || '');
                    $('#email').val(response.email || '');
                    $('#phone').val(response.phone || '');
                    $('#address').val(response.address || '');
                    $('#status').val(response.status || 'active');
                    $('#supplierModalLabel').text('Edit Supplier');
                    supplierModal.show();
                } else {
                    showNotification(response.message || 'Error: Supplier not found', 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotification('Error fetching supplier: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
            }
        });
    });

    // Save Supplier
    $('#supplierForm').submit(function(e) {
        e.preventDefault();
        const id = $('#supplierId').val();
        const data = {
            name: $('#name').val(),
            email: $('#email').val(),
            phone: $('#phone').val(),
            address: $('#address').val(),
            status: $('#status').val()
        };
        const url = id ? 'api/update.php' : 'api/create.php';
        if (id) data.id = id;

        $.ajax({
            url: url,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                showNotification(response.message, response.status);
                supplierModal.hide();
                table.ajax.reload(function() {
                    updateDeleteButtonText();
                });
            },
            error: function(xhr) {
                const response = xhr.responseJSON || { message: 'An error occurred', status: 'error' };
                showNotification(response.message, 'error');
            }
        });
    });

    // Delete Supplier
    $(document).on('click', '.delete-btn', function() {
        if (confirm('Are you sure you want to delete this supplier?')) {
            const id = $(this).data('id');
            $.ajax({
                url: 'api/delete.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ id: id }),
                success: function(response) {
                    showNotification(response.message, response.status);
                    table.ajax.reload(function() {
                        updateDeleteButtonText();
                    });
                },
                error: function(xhr) {
                    const response = xhr.responseJSON || { message: 'An error occurred', status: 'error' };
                    showNotification(response.message, 'error');
                }
            });
        }
    });

    // Delete Selected (Bulk)
    $('#deleteAllBtn').click(function() {
        const selectedIds = $('.select-row:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            showNotification('No suppliers selected', 'error');
            return;
        }

        if (confirm('Are you sure you want to delete selected suppliers?')) {
            $.ajax({
                url: 'api/bulk_delete.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ ids: selectedIds }),
                success: function(response) {
                    showNotification(response.message, response.status);
                    $('#selectAllCheckbox').prop('checked', false);
                    $('.select-row').prop('checked', false);
                    table.ajax.reload(function() {
                        updateDeleteButtonText();
                    });
                },
                error: function(xhr) {
                    const response = xhr.responseJSON || { message: 'An error occurred', status: 'error' };
                    showNotification(response.message, 'error');
                }
            });
        }
    });
});