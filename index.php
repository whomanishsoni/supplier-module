<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="public/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 sm:p-6">
        <h1 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-6">Supplier Management</h1>
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-start gap-2 sm:gap-4">
            <button id="addSupplierBtn" class="bg-blue-500 text-white px-3 py-2 rounded w-full sm:w-auto">Add Supplier</button>
            <button id="deleteAllBtn" class="bg-red-500 text-white px-3 py-2 rounded w-full sm:w-auto">Delete Selected (0)</button>
        </div>
        <div class="mb-4 flex flex-col sm:flex-row flex-wrap gap-2 sm:gap-4 items-start sm:items-center">
            <div class="flex items-center gap-2">
                <label for="statusFilter" class="text-sm font-medium">Status:</label>
                <select id="statusFilter" class="px-2 py-1 border rounded text-sm">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label for="nameFilter" class="text-sm font-medium">Name:</label>
                <input type="text" id="nameFilter" class="px-2 py-1 border rounded text-sm" placeholder="Enter name">
            </div>
            <div class="flex items-center gap-2">
                <label for="emailFilter" class="text-sm font-medium">Email:</label>
                <input type="text" id="emailFilter" class="px-2 py-1 border rounded text-sm" placeholder="Enter email">
            </div>
        </div>
        <div class="table-responsive">
            <table id="suppliersTable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllCheckbox"></th>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="supplierModal" tabindex="-1" aria-labelledby="supplierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="supplierModalLabel">Add Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="supplierForm">
                        <input type="hidden" id="supplierId">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewSupplierModal" tabindex="-1" aria-labelledby="viewSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewSupplierModalLabel">View Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-bold">Name:</label>
                        <p id="viewName" class="text-gray-700"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bold">Email:</label>
                        <p id="viewEmail" class="text-gray-700"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bold">Phone:</label>
                        <p id="viewPhone" class="text-gray-700"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bold">Address:</label>
                        <p id="viewAddress" class="text-gray-700"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bold">Status:</label>
                        <p id="viewStatus" class="text-gray-700"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bold">Created At:</label>
                        <p id="viewCreatedAt" class="text-gray-700"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div id="notification" class="fixed top-4 right-4 z-50 hidden p-4 rounded shadow-lg w-80"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
    <script src="public/js/scripts.js"></script>
</body>
</html>