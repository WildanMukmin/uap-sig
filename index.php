<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web GIS - Sarana Ibadah Bandar Lampung</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    
    <style>
        #map { 
            height: 600px;
            z-index: 100;
        }
        .leaflet-popup-content { min-width: 250px; }
        
        /* Custom popup styling */
        .custom-popup .leaflet-popup-content-wrapper {
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .custom-popup .leaflet-popup-tip {
            box-shadow: 0 3px 14px rgba(0,0,0,0.2);
        }
        
        /* Ensure popup appears above everything */
        .leaflet-popup {
            z-index: 1000 !important;
        }
        
        /* Kecamatan tooltip styling */
        .kecamatan-tooltip {
            background: white;
            border: 2px solid #2563eb;
            border-radius: 8px;
            padding: 8px 12px;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        /* Fix for popup being cut off */
        .leaflet-container {
            overflow: visible !important;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-map-marked-alt text-2xl"></i>
                    <div>
                        <h1 class="text-xl font-bold">Web GIS Sarana Ibadah</h1>
                        <p class="text-sm text-blue-200">Kota Bandar Lampung</p>
                    </div>
                </div>
                <div id="nav-actions" class="flex items-center space-x-4">
                    <a href="#statistik" class="hover:text-blue-200 transition">
                        <i class="fas fa-chart-bar"></i> Statistik
                    </a>
                    <a href="admin-login.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition font-semibold">
                        <i class="fas fa-sign-in-alt"></i> Login Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Sidebar Filter -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-filter mr-2 text-blue-600"></i>
                        Filter Data
                    </h2>
                    
                    <!-- Filter Jenis -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis Sarana Ibadah
                        </label>
                        <select id="filter-jenis" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua</option>
                            <option value="Masjid">Masjid</option>
                            <option value="Gereja">Gereja</option>
                            <option value="Pura">Pura</option>
                            <option value="Vihara">Vihara</option>
                            <option value="Klenteng">Klenteng</option>
                        </select>
                    </div>

                    <!-- Filter Kecamatan -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Kecamatan
                        </label>
                        <select id="filter-kecamatan" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua</option>
                        </select>
                    </div>

                    <!-- Search Box -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pencarian
                        </label>
                        <div class="relative">
                            <input type="text" id="search-box" placeholder="Cari nama..." class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </div>

                    <!-- Data Count -->
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-gray-600">Total Data Ditampilkan</p>
                        <p class="text-2xl font-bold text-blue-600" id="data-count">0</p>
                    </div>
                </div>
            </div>

            <!-- Map & List -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Map Container -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
                        <h2 class="text-lg font-bold flex items-center">
                            <i class="fas fa-map mr-2"></i>
                            Peta Sebaran Sarana Ibadah
                        </h2>
                    </div>
                    <div id="map"></div>
                </div>

                <!-- List Container -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="p-4 border-b">
                        <h2 class="text-lg font-bold text-gray-800 flex items-center">
                            <i class="fas fa-list mr-2 text-blue-600"></i>
                            Daftar Sarana Ibadah
                        </h2>
                    </div>
                    <div id="list-container" class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[600px] overflow-y-auto">
                        <div class="col-span-full text-center py-8 text-gray-500">
                            <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
                            <p>Memuat data...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div id="statistik" class="mt-12">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-chart-bar mr-3 text-blue-600"></i>
                    Statistik Sarana Ibadah
                </h2>
                <div id="stats-container" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <!-- Stats will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12 py-6">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2024 Web GIS Sarana Ibadah Bandar Lampung</p>
            <p class="text-sm text-gray-400 mt-2">Sistem Informasi Geografis Berbasis Web</p>
        </div>
    </footer>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Main JS -->
    <script src="assets/js/guest-map.js"></script>
</body>
</html>