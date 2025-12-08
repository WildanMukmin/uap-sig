<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Web GIS Sarana Ibadah</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    
    <style>
        #map { height: 500px; }
        .leaflet-popup-content { min-width: 250px; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-map-marked-alt text-2xl"></i>
                    <div>
                        <h1 class="text-xl font-bold">Admin Dashboard</h1>
                        <p class="text-sm text-blue-200">Web GIS Sarana Ibadah</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm font-semibold" id="user-name">Loading...</p>
                        <p class="text-xs text-blue-200" id="user-role">Admin</p>
                    </div>
                    <button onclick="handleLogout()" 
                        class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Sarana</p>
                        <p class="text-3xl font-bold text-gray-800" id="stat-total">0</p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <i class="fas fa-database text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Masjid</p>
                        <p class="text-3xl font-bold text-green-600" id="stat-masjid">0</p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-full">
                        <i class="fas fa-mosque text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Gereja</p>
                        <p class="text-3xl font-bold text-blue-600" id="stat-gereja">0</p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <i class="fas fa-church text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Lainnya</p>
                        <p class="text-3xl font-bold text-purple-600" id="stat-lainnya">0</p>
                    </div>
                    <div class="bg-purple-100 p-4 rounded-full">
                        <i class="fas fa-place-of-worship text-2xl text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="flex flex-wrap gap-3">
                <button onclick="showAddModal()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i>Tambah Data
                </button>
                <button onclick="refreshData()" 
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
                <button onclick="exportData()" 
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-download mr-2"></i>Export Data
                </button>
                <a href="index.php" target="_blank"
                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-eye mr-2"></i>Lihat Peta Publik
                </a>
            </div>
        </div>

        <!-- Map and Table -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Map -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-4 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
                    <h2 class="text-lg font-bold flex items-center">
                        <i class="fas fa-map mr-2"></i>Peta Interaktif
                    </h2>
                    <p class="text-sm text-blue-200">Klik pada peta untuk menambah titik baru</p>
                </div>
                <div id="map"></div>
            </div>

            <!-- Filter Sidebar -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-filter mr-2 text-blue-600"></i>Filter Data
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis</label>
                        <select id="filter-jenis" onchange="applyFilters()" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua</option>
                            <option value="Masjid">Masjid</option>
                            <option value="Gereja">Gereja</option>
                            <option value="Pura">Pura</option>
                            <option value="Vihara">Vihara</option>
                            <option value="Klenteng">Klenteng</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kecamatan</label>
                        <select id="filter-kecamatan" onchange="applyFilters()" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                        <input type="text" id="search-box" oninput="applyFilters()" 
                            placeholder="Cari nama..." 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-gray-600">Data Ditampilkan</p>
                    <p class="text-2xl font-bold text-blue-600" id="data-count">0</p>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="mt-6 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 border-b">
                <h2 class="text-lg font-bold text-gray-800 flex items-center">
                    <i class="fas fa-table mr-2 text-blue-600"></i>Daftar Sarana Ibadah
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kecamatan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kapasitas</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="table-body" class="divide-y divide-gray-200">
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <p>Memuat data...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form (akan dilanjutkan di JavaScript) -->
    <div id="modal-form" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <!-- Modal content akan dibuat di JavaScript -->
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Admin JS -->
    <script src="assets/js/admin-dashboard.js"></script>
</body>
</html>