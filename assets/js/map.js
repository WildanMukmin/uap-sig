// Global variables
let map;
let markersLayer;
let allData = [];
let filteredData = [];
let tempMarker = null;
let isEditMode = false;

// API Base URL
const API_URL = 'api/';

// Initialize map
function initMap() {
    // Create map centered on Bandar Lampung
    map = L.map('map').setView([-5.4292, 105.2619], 12);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Create markers layer
    markersLayer = L.layerGroup().addTo(map);

    // Add click event to map
    map.on('click', onMapClick);

    // Load data
    loadData();
}

// Load data from API
async function loadData() {
    try {
        const response = await fetch(API_URL + 'get_data.php');
        const geojson = await response.json();
        
        if (geojson && geojson.features) {
            allData = geojson.features;
            filteredData = [...allData];
            displayMarkers(filteredData);
            displayList(filteredData);
            populateFilters();
        }
    } catch (error) {
        console.error('Error loading data:', error);
        alert('Gagal memuat data');
    }
}

// Display markers on map
function displayMarkers(data) {
    markersLayer.clearLayers();
    
    data.forEach(feature => {
        const props = feature.properties;
        const coords = feature.geometry.coordinates;
        
        // Get icon based on jenis
        const icon = getIconByJenis(props.jenis);
        
        // Create marker
        const marker = L.marker([coords[1], coords[0]], { icon: icon })
            .bindPopup(createPopupContent(props))
            .addTo(markersLayer);
        
        // Store feature data in marker
        marker.feature = feature;
    });
}

// Get icon by jenis
function getIconByJenis(jenis) {
    const iconColors = {
        'Masjid': '#10b981',
        'Gereja': '#3b82f6',
        'Pura': '#f59e0b',
        'Vihara': '#ec4899',
        'Klenteng': '#8b5cf6'
    };
    
    const color = iconColors[jenis] || '#6b7280';
    
    return L.divIcon({
        className: 'custom-marker',
        html: `<div style="background-color: ${color}; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-mosque" style="color: white; font-size: 14px;"></i>
               </div>`,
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });
}

// Create popup content
function createPopupContent(props) {
    return `
        <div class="popup-content">
            <h4>${props.nama}</h4>
            <p><strong>Jenis:</strong> ${props.jenis}</p>
            <p><strong>Alamat:</strong> ${props.alamat || '-'}</p>
            <p><strong>Kecamatan:</strong> ${props.kecamatan || '-'}</p>
            <p><strong>Kapasitas:</strong> ${props.kapasitas || '-'} orang</p>
            <p><strong>Tahun Berdiri:</strong> ${props.tahun_berdiri || '-'}</p>
            ${props.keterangan ? `<p><strong>Keterangan:</strong> ${props.keterangan}</p>` : ''}
            <div class="popup-actions">
                <button class="btn btn-primary btn-sm" onclick="editData(${props.id})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-danger btn-sm" onclick="deleteData(${props.id})">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </div>
        </div>
    `;
}

// Display list in sidebar
function displayList(data) {
    const container = document.getElementById('list-container');
    
    if (data.length === 0) {
        container.innerHTML = '<p class="loading">Tidak ada data</p>';
        return;
    }
    
    container.innerHTML = data.map(feature => {
        const props = feature.properties;
        return `
            <div class="list-item" onclick="focusMarker(${props.id})" data-id="${props.id}">
                <span class="list-item-badge badge-${props.jenis.toLowerCase()}">${props.jenis}</span>
                <h4>${props.nama}</h4>
                <p><i class="fas fa-map-marker-alt"></i> ${props.kecamatan || 'N/A'}</p>
                <p><i class="fas fa-users"></i> Kapasitas: ${props.kapasitas || 'N/A'} orang</p>
                <div class="list-item-actions">
                    <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); editData(${props.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="event.stopPropagation(); deleteData(${props.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

// Focus on marker
function focusMarker(id) {
    const feature = allData.find(f => f.properties.id === id);
    if (feature) {
        const coords = feature.geometry.coordinates;
        map.setView([coords[1], coords[0]], 16);
        
        // Highlight marker
        markersLayer.eachLayer(layer => {
            if (layer.feature && layer.feature.properties.id === id) {
                layer.openPopup();
            }
        });
        
        // Highlight list item
        document.querySelectorAll('.list-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelector(`.list-item[data-id="${id}"]`).classList.add('active');
    }
}

// Populate filter dropdowns
function populateFilters() {
    const kecamatans = [...new Set(allData.map(f => f.properties.kecamatan).filter(k => k))];
    const kecamatanSelect = document.getElementById('filter-kecamatan');
    
    kecamatanSelect.innerHTML = '<option value="">Semua</option>' + 
        kecamatans.map(k => `<option value="${k}">${k}</option>`).join('');
}

// Apply filters
function applyFilters() {
    const jenisFilter = document.getElementById('filter-jenis').value;
    const kecamatanFilter = document.getElementById('filter-kecamatan').value;
    const searchText = document.getElementById('search-box').value.toLowerCase();
    
    filteredData = allData.filter(feature => {
        const props = feature.properties;
        
        const matchJenis = !jenisFilter || props.jenis === jenisFilter;
        const matchKecamatan = !kecamatanFilter || props.kecamatan === kecamatanFilter;
        const matchSearch = !searchText || props.nama.toLowerCase().includes(searchText);
        
        return matchJenis && matchKecamatan && matchSearch;
    });
    
    displayMarkers(filteredData);
    displayList(filteredData);
}

// Map click event
function onMapClick(e) {
    const { lat, lng } = e.latlng;
    
    // Remove temporary marker if exists
    if (tempMarker) {
        map.removeLayer(tempMarker);
    }
    
    // Create temporary marker
    tempMarker = L.marker([lat, lng], {
        icon: L.divIcon({
            className: 'temp-marker',
            html: '<div style="background-color: #ef4444; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3); animation: pulse 1.5s infinite;"></div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        })
    }).addTo(map);
    
    // Open form modal
    openFormModal('create', lat, lng);
}

// Open form modal
function openFormModal(mode = 'create', lat = null, lng = null, data = null) {
    const modal = document.getElementById('modal-form');
    const form = document.getElementById('form-sarana');
    const title = document.getElementById('modal-title');
    
    isEditMode = mode === 'edit';
    
    // Reset form
    form.reset();
    
    if (mode === 'create') {
        title.textContent = 'Tambah Data Sarana Ibadah';
        document.getElementById('form-latitude').value = lat.toFixed(6);
        document.getElementById('form-longitude').value = lng.toFixed(6);
    } else if (mode === 'edit' && data) {
        title.textContent = 'Edit Data Sarana Ibadah';
        document.getElementById('form-id').value = data.id;
        document.getElementById('form-nama').value = data.nama;
        document.getElementById('form-jenis').value = data.jenis;
        document.getElementById('form-latitude').value = data.latitude;
        document.getElementById('form-longitude').value = data.longitude;
        document.getElementById('form-alamat').value = data.alamat || '';
        document.getElementById('form-kecamatan').value = data.kecamatan || '';
        document.getElementById('form-kapasitas').value = data.kapasitas || '';
        document.getElementById('form-tahun').value = data.tahun_berdiri || '';
        document.getElementById('form-keterangan').value = data.keterangan || '';
    }
    
    modal.classList.add('show');
}

// Close modal
function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
    if (tempMarker) {
        map.removeLayer(tempMarker);
        tempMarker = null;
    }
}

// Create/Update data
async function saveData(formData) {
    const url = isEditMode ? API_URL + 'update.php' : API_URL + 'create.php';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            closeModal('modal-form');
            loadData();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
    }
}

// Edit data
function editData(id) {
    const feature = allData.find(f => f.properties.id === id);
    if (feature) {
        openFormModal('edit', null, null, feature.properties);
    }
}

// Delete data
async function deleteData(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        return;
    }
    
    try {
        const response = await fetch(API_URL + 'delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            loadData();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus data');
    }
}

// Show statistics
function showStatistics() {
    const modal = document.getElementById('modal-stats');
    const container = document.getElementById('stats-container');
    
    // Count by jenis
    const stats = {};
    allData.forEach(feature => {
        const jenis = feature.properties.jenis;
        stats[jenis] = (stats[jenis] || 0) + 1;
    });
    
    // Count by kecamatan
    const kecamatanStats = {};
    allData.forEach(feature => {
        const kec = feature.properties.kecamatan || 'Tidak Diketahui';
        kecamatanStats[kec] = (kecamatanStats[kec] || 0) + 1;
    });
    
    container.innerHTML = `
        <div class="stats-grid">
            <div class="stat-card">
                <h3>${allData.length}</h3>
                <p>Total Sarana Ibadah</p>
            </div>
            <div class="stat-card">
                <h3>${stats['Masjid'] || 0}</h3>
                <p>Masjid</p>
            </div>
            <div class="stat-card">
                <h3>${stats['Gereja'] || 0}</h3>
                <p>Gereja</p>
            </div>
            <div class="stat-card">
                <h3>${stats['Pura'] || 0}</h3>
                <p>Pura</p>
            </div>
            <div class="stat-card">
                <h3>${stats['Vihara'] || 0}</h3>
                <p>Vihara</p>
            </div>
            <div class="stat-card">
                <h3>${stats['Klenteng'] || 0}</h3>
                <p>Klenteng</p>
            </div>
        </div>
        
        <h3 style="margin: 2rem 0 1rem;">Distribusi per Kecamatan</h3>
        <table class="stats-table">
            <thead>
                <tr>
                    <th>Kecamatan</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                ${Object.entries(kecamatanStats)
                    .sort((a, b) => b[1] - a[1])
                    .map(([kec, count]) => `
                        <tr>
                            <td>${kec}</td>
                            <td><strong>${count}</strong></td>
                        </tr>
                    `).join('')}
            </tbody>
        </table>
    `;
    
    modal.classList.add('show');
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    initMap();
    
    // Filter events
    document.getElementById('filter-jenis').addEventListener('change', applyFilters);
    document.getElementById('filter-kecamatan').addEventListener('change', applyFilters);
    document.getElementById('search-box').addEventListener('input', applyFilters);
    
    // Button events
    document.getElementById('btn-add').addEventListener('click', function() {
        alert('Silakan klik pada peta untuk menambah titik baru');
    });
    
    document.getElementById('btn-stats').addEventListener('click', showStatistics);
    
    // Form submit
    document.getElementById('form-sarana').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            nama: document.getElementById('form-nama').value,
            jenis: document.getElementById('form-jenis').value,
            latitude: parseFloat(document.getElementById('form-latitude').value),
            longitude: parseFloat(document.getElementById('form-longitude').value),
            alamat: document.getElementById('form-alamat').value,
            kecamatan: document.getElementById('form-kecamatan').value,
            kapasitas: parseInt(document.getElementById('form-kapasitas').value) || 0,
            tahun_berdiri: parseInt(document.getElementById('form-tahun').value) || null,
            keterangan: document.getElementById('form-keterangan').value
        };
        
        if (isEditMode) {
            formData.id = parseInt(document.getElementById('form-id').value);
        }
        
        saveData(formData);
    });
    
    // Cancel button
    document.getElementById('btn-cancel').addEventListener('click', function() {
        closeModal('modal-form');
    });
    
    // Close buttons
    document.querySelectorAll('.close').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            closeModal(modal.id);
        });
    });
    
    // Close modal on outside click
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target.id);
        }
    });
});

// Add pulse animation
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }
`;
document.head.appendChild(style);