<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Supplier Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="public/js/flowbite.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="public/css/styles.css">
</head>
<body class="bg-gray-100">
  <div id="app" class="max-w-7xl mx-auto">
    <!-- Notification -->
    <div id="notification" class="hidden fixed top-16 sm:top-20 right-4 z-[60] px-4 py-2 rounded-lg shadow-lg text-white text-sm sm:text-base flex items-center justify-between" role="alert" aria-live="assertive">
      <span id="notification-text"></span>
      <button id="notification-close" class="ml-2 text-white hover:text-gray-200" aria-label="Close notification">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    <!-- Sticky Header -->
    <div class="sticky top-0 z-50 bg-gray-100 p-2 sm:p-4 shadow-md">
      <div class="flex flex-row items-center justify-between space-x-2 sm:space-x-4">
        <div class="flex flex-row items-center space-x-2 sm:space-x-4">
          <button id="toggle-global" class="p-1 sm:p-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-100" title="Toggle Global Search">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </button>
          <input
            id="global-search"
            type="text"
            placeholder="Search..."
            class="hidden w-24 sm:w-48 px-2 sm:px-3 py-1 sm:py-1.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
          />
          <button id="toggle-filters" class="p-1 sm:p-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-100" title="Toggle Filters">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.32.776l-2 1.5A1 1 0 019 20.5V13.414L3.293 6.707A1 1 0 013 6V4z" />
            </svg>
          </button>
          <div id="filters-container" class="hidden flex flex-row items-center space-x-2 sm:space-x-4">
            <input
              id="name-search"
              type="text"
              placeholder="Search by name..."
              class="w-24 sm:w-48 px-2 sm:px-3 py-1 sm:py-1.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
            />
            <input
              id="email-search"
              type="text"
              placeholder="Search by email..."
              class="w-24 sm:w-48 px-2 sm:px-3 py-1 sm:py-1.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
            />
            <select
              id="status-filter"
              class="w-20 sm:w-36 px-2 sm:px-3 py-1 sm:py-1.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm"
            >
              <option value="">All Statuses</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="flex flex-row items-center space-x-2 sm:space-x-4">
          <button
            id="add-supplier-btn"
            class="p-1 sm:p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            title="Add Supplier"
          >
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </button>
          <button
            id="delete-selected-btn"
            class="p-1 sm:p-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
            title="Delete Selected (0)"
            disabled
          >
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Table Wrapper to Prevent Dropdown Clipping -->
    <div class="relative">
      <div class="overflow-x-hidden mt-4">
        <table id="data-table" class="w-full text-xs sm:text-sm text-left text-gray-500">
          <thead class="text-xs uppercase bg-gray-50">
            <tr>
              <th scope="col" class="px-2 sm:px-4 py-2 sm:py-3 w-12">
                <input id="select-all" type="checkbox" class="w-4 h-4" />
              </th>
              <th scope="col" class="px-2 sm:px-4 py-2 sm:py-3 cursor-pointer" data-sort="id">ID <span class="sort-icon"></span></th>
              <th scope="col" class="px-2 sm:px-4 py-2 sm:py-3 cursor-pointer" data-sort="name">Name <span class="sort-icon"></span></th>
              <th scope="col" class="px-2 sm:px-4 py-2 sm:py-3 cursor-pointer hidden sm:table-cell" data-sort="email">Email <span class="sort-icon"></span></th>
              <th scope="col" class="px-2 sm:px-4 py-2 sm:py-3 cursor-pointer hidden sm:table-cell" data-sort="phone">Phone <span class="sort-icon"></span></th>
              <th scope="col" class="px-2 sm:px-4 py-2 sm:py-3 cursor-pointer" data-sort="status">Status <span class="sort-icon"></span></th>
              <th scope="col" class="px-2 sm:px-4 py-2 sm:py-3">Actions</th>
            </tr>
          </thead>
          <tbody id="table-body"></tbody>
        </table>
      </div>
    </div>

    <!-- Pagination and Page Length -->
    <div class="mt-3 sm:mt-4 flex flex-col sm:flex-row justify-between items-center gap-2 sm:gap-4">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <select
          id="items-per-page"
          class="px-2 sm:px-3 py-1 sm:py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-700 text-xs sm:text-sm"
          title="Items per page"
        >
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
        <span class="text-xs sm:text-sm text-gray-700">per page</span>
      </div>
      <nav class="flex flex-col sm:flex-row justify-between items-center w-full sm:w-auto" aria-label="Table navigation">
        <span id="pagination-info" class="text-xs sm:text-sm text-gray-700 text-center sm:text-left"></span>
        <ul id="pagination" class="inline-flex items-center -space-x-px mt-2 sm:mt-0"></ul>
      </nav>
    </div>

    <!-- Add/Edit Modal -->
    <div id="supplier-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
      <div class="bg-white p-4 sm:p-6 rounded-lg shadow-lg w-full h-full sm:max-w-md sm:h-auto">
        <h2 id="modal-title" class="text-lg sm:text-xl font-bold mb-3 sm:mb-4"></h2>
        <form id="supplier-form" class="space-y-3 sm:space-y-4">
          <input type="hidden" id="supplier-id">
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700">Name</label>
            <input id="modal-name" type="text" class="w-full px-3 sm:px-4 py-1.5 sm:py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base" placeholder="Enter name">
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700">Email</label>
            <input id="modal-email" type="email" class="w-full px-3 sm:px-4 py-1.5 sm:py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base" placeholder="Enter email">
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700">Phone</label>
            <input id="modal-phone" type="text" class="w-full px-3 sm:px-4 py-1.5 sm:py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base" placeholder="Enter phone">
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700">Address</label>
            <input id="modal-address" type="text" class="w-full px-3 sm:px-4 py-1.5 sm:py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base" placeholder="Enter address">
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700">Status</label>
            <select id="modal-status" class="w-full px-3 sm:px-4 py-1.5 sm:py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div class="flex justify-end gap-2">
            <button type="button" id="modal-close" class="px-3 sm:px-4 py-1.5 sm:py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 text-sm sm:text-base">Cancel</button>
            <button type="submit" id="modal-submit" class="px-3 sm:px-4 py-1.5 sm:py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm sm:text-base">Submit</button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Modal -->
    <div id="view-supplier-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
      <div class="bg-white p-4 sm:p-6 rounded-lg shadow-lg w-full h-full sm:max-w-md sm:h-auto">
        <h2 id="view-modal-title" class="text-lg sm:text-xl font-bold mb-3 sm:mb-4">View Supplier</h2>
        <div class="space-y-3 sm:space-y-4">
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700">Name</label>
            <p id="view-name" class="w-full px-3 sm:px-4 py-1.5 sm:py-2 border rounded-lg bg-gray-100 text-sm sm:text-base"></p>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700">Email</label>
            <p id="view-email" class="w-full px-3 sm:px-4 py-1.5 sm:py-2 border rounded-lg bg-gray-100 text-sm sm:text-base"></p>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700">Phone</label>
            <p id="view-phone" class="w-full px-3 sm:px-4 py-1.5 sm:py-2 border rounded-lg bg-gray-100 text-sm sm:text-base"></p>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700">Address</label>
            <p id="view-address" class="w-full px-3 sm:px-4 py-1.5 sm:py-2 border rounded-lg bg-gray-100 text-sm sm:text-base"></p>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700">Status</label>
            <p id="view-status" class="w-full px-3 sm:px-4 py-1.5 sm:py-2 border rounded-lg bg-gray-100 text-sm sm:text-base"></p>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700">Created At</label>
            <p id="view-created-at" class="w-full px-3 sm:px-4 py-1.5 sm:py-2 border rounded-lg bg-gray-100 text-sm sm:text-base"></p>
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700">Updated At</label>
            <p id="view-updated-at" class="w-full px-3 sm:px-4 py-1.5 sm:py-2 border rounded-lg bg-gray-100 text-sm sm:text-base"></p>
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-3 sm:mt-4">
          <button id="view-modal-close" class="px-3 sm:px-4 py-1.5 sm:py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 text-sm sm:text-base">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script src="public/js/jquery.min.js"></script>
  <script src="public/js/flowbite.min.js"></script>
  <script src="public/js/scripts.js"></script>
</body>
</html>