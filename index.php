<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web GIS - Sarana Ibadah Bandar Lampung</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header>
            <div class="header-content">
                <h1><i class="fas fa-map-marked-alt"></i> Web GIS Sarana Ibadah</h1>
                <p>Pemetaan Sarana Ibadah di Bandar Lampung</p>
            </div>
            <div class="header-actions">
                <button id="btn-add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Data
                </button>
                <button id="btn-stats" class="btn btn-info">
                    <i class="fas fa-chart-bar"></i> Statistik
                </button>
            </div>
        </header>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="sidebar-section">
                    <h3><i class="fas fa-filter"></i> Filter Data</h3>
                    <div class="filter-group">
                        <label>Jenis Sarana Ibadah:</label>
                        <select id="filter-jenis" class="form-control">
                            <option value="">Semua</option>
                            <option value="Masjid">Masjid</option>
                            <option value="Gereja">Gereja</option>
                            <option value="Pura">Pura</option>
                            <option value="Vihara">Vihara</option>
                            <option value="Klenteng">Klenteng</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Kecamatan:</label>
                        <select id="filter-kecamatan" class="form-control">
                            <option value="">Semua</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Pencarian:</label>
                        <input type="text" id="search-box" class="form-control" placeholder="Cari nama...">
                    </div>
                </div>

                <div class="sidebar-section">
                    <h3><i class="fas fa-list"></i> Daftar Sarana Ibadah</h3>
                    <div id="list-container" class="list-container">
                        <p class="loading">Memuat data...</p>
                    </div>
                </div>
            </aside>

            <!-- Map Container -->
            <main class="map-container">
                <div id="map"></div>
                <div class="map-info">
                    <p><i class="fas fa-mouse-pointer"></i> Klik pada peta untuk menambah titik baru</p>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Form -->
    <div id="modal-form" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Tambah Data Sarana Ibadah</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="form-sarana">
                    <input type="hidden" id="form-id">
                    
                    <div class="form-group">
                        <label>Nama Sarana Ibadah <span class="required">*</span></label>
                        <input type="text" id="form-nama" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Jenis <span class="required">*</span></label>
                        <select id="form-jenis" class="form-control" required>
                            <option value="">Pilih Jenis</option>
                            <option value="Masjid">Masjid</option>
                            <option value="Gereja">Gereja</option>
                            <option value="Pura">Pura</option>
                            <option value="Vihara">Vihara</option>
                            <option value="Klenteng">Klenteng</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Latitude <span class="required">*</span></label>
                            <input type="number" id="form-latitude" class="form-control" step="0.000001" required readonly>
                        </div>
                        <div class="form-group">
                            <label>Longitude <span class="required">*</span></label>
                            <input type="number" id="form-longitude" class="form-control" step="0.000001" required readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea id="form-alamat" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Kecamatan</label>
                            <input type="text" id="form-kecamatan" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Kapasitas</label>
                            <input type="number" id="form-kapasitas" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Tahun Berdiri</label>
                        <input type="number" id="form-tahun" class="form-control" min="1900" max="2025">
                    </div>

                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea id="form-keterangan" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <button type="button" class="btn btn-secondary" id="btn-cancel">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Statistik -->
    <div id="modal-stats" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Statistik Sarana Ibadah</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div id="stats-container">
                    <p class="loading">Memuat statistik...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/map.js"></script>
</body>
</html>